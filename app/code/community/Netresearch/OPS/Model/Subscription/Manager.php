<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Management endpoint for order creation from subscription feedback
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Model_Subscription_Manager
{
    const CREATION_FAILED = 'ERROR';
    const CREATION_SUCCEEDED = 'OK';


    protected $subscriptionHelper = null;
    protected $paymentHelper = null;
    protected $dataHelper = null;

    /**
     * @return Netresearch_OPS_Helper_Data
     */
    public function getDataHelper()
    {
        if (null === $this->dataHelper) {
            $this->dataHelper = Mage::helper('ops');
        }

        return $this->dataHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Data $dataHelper
     *
     * @returns $this
     */
    public function setDataHelper($dataHelper)
    {
        $this->dataHelper = $dataHelper;

        return $this;
    }

    /**
     * @return Netresearch_OPS_Helper_Payment
     */
    public function getPaymentHelper()
    {
        if (null === $this->paymentHelper) {
            $this->paymentHelper = Mage::helper('ops/payment');
        }

        return $this->paymentHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Payment $paymentHelper
     *
     * @returns $this
     */
    public function setPaymentHelper($paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;

        return $this;
    }


    /**
     * @return Netresearch_OPS_Helper_Subscription
     */
    public function getSubscriptionHelper()
    {
        if (null === $this->subscriptionHelper) {
            $this->subscriptionHelper = Mage::helper('ops/subscription');
        }

        return $this->subscriptionHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Subscription $subscriptionHelper
     *
     * @returns $this
     */
    public function setSubscriptionHelper($subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;

        return $this;
    }


    /**
     * Process request from Ingenico ePayments automatic subscription payments and initial creations
     *
     *
     * @param mixed[]                            $responseParams
     * @param Mage_Sales_Model_Recurring_Profile $profile   - only has to be provided during the initial setup of
     *                                                      the subscription
     * @param Mage_Sales_Model_Order             $order
     *
     *
     * @return Mage_Sales_Model_Order | false
     */
    public function processSubscriptionFeedback($responseParams, $profile = null, $order = null)
    {
        $createOrder = true;
        $feedbackType = false;
        $orderId = $responseParams['orderID'];
        if (!is_array($responseParams)) {
            Mage::throwException($this->getDataHelper()->__('No response array provided'));
        }

        if (null === $profile) {
            $profile = $this->getSubscriptionHelper()->getProfileForSubscription($orderId);
        }

        if (array_key_exists('creation_status', $responseParams)) {
            switch ($responseParams['creation_status']) {
                case self::CREATION_SUCCEEDED:
                    $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
                    break;
                default:
                    $profile->setState(Mage_Sales_Model_Recurring_Profile::STATE_UNKNOWN);
                    break;
            }
            $profile->setReferenceId($responseParams['subscription_id']);
            $profile->setProfileVendorInfo(serialize($responseParams));
            $profile->setAdditionalInfo(serialize($responseParams));
            $feedbackType = Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_INITIAL;
            // do not create an order since it already got created
            $createOrder = false;
        }

        if (!$feedbackType) {
            if ($this->getSubscriptionHelper()->isTrialFeedback($orderId)) {
                $feedbackType = Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL;
            } else {
                $feedbackType = Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_REGULAR;
            }
        }

        if ($createOrder && null === $order) {
            return $this->createOrderFromFeedback($profile, $feedbackType, $responseParams);
        } elseif (null != $order) {
            return $this->processPaymentFeedback($responseParams, $profile, $order);
        }

        return $createOrder;
    }

    /**
     * Creates an order for the given item type using the recurring profile model
     *
     * @see Mage_Sales_Model_Recurring_Profile::createOrder()
     *
     * @param Mage_Sales_Model_Recurring_Profile $profile
     * @param string                             $feedbackType - @see Mage_Sales_Model_Recurring_Profile payment types
     * @param string[]                           $responseParams
     *
     * @return Mage_Sales_Model_Order the created order
     */
    protected function createOrderFromFeedback($profile, $feedbackType, $responseParams)
    {
        // Just set the payment type, the recurring profile sets the correct amounts automatically
        $orderItem = new Varien_Object();
        $orderItem->setPaymentType($feedbackType);
        if ($feedbackType == Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_TRIAL) {
            $orderItemInfo = new Varien_Object($profile->getOrderItemInfo());
            $calculator = Mage::getModel('tax/calculation');
            $tax = $calculator->calcTaxAmount(
                $profile->getTrialBillingAmount(),
                $orderItemInfo->getTaxPercent(), true, true
            );
            $price = $profile->getTrialBillingAmount() - $tax;
            $orderItem->setPrice($price)
                      ->setTaxAmount($tax);
        }

        $order = $profile->createOrder($orderItem);
        $billingAddressInfo = $profile->getBillingAddressInfo();
        $order->setQuoteId($billingAddressInfo['quote_id']);

        $order = $this->processPaymentFeedback($responseParams, $profile, $order);

        return $order;
    }

    /**
     * Creates an order for the initial fee. It also fixes the missing data for the order item, so a capture can be
     * properly processed and an invoice created.
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     *
     * @return Mage_Sales_Model_Order
     */
    public function createInitialOrder(Mage_Payment_Model_Recurring_Profile $profile)
    {
        /** @var $profile Mage_Sales_Model_Recurring_Profile */
        $item = new Varien_Object();
        $orderItemInfo = new Varien_Object($profile->getOrderItemInfo());
        $calculator = Mage::getModel('tax/calculation');
        $taxAmount = $calculator->calcTaxAmount(
            $profile->getInitAmount(), $orderItemInfo->getTaxPercent(), true,
            true
        );
        $amountWithoutTax = $profile->getInitAmount() - $taxAmount;
        $item->setPaymentType(Mage_Sales_Model_Recurring_Profile::PAYMENT_TYPE_INITIAL)
             ->setPrice($amountWithoutTax)
             ->setTaxAmount($taxAmount);

        $order = $profile->createOrder($item);
        $order->setQuoteId($profile->getQuote()->getId());

        /** @var $item Mage_Sales_Model_Order_Item */
        list($item) = $order->getAllItems();
        $item->setData('base_discount_amount', 0)
             ->setData('base_discount_calculation_price', $profile->getInitAmount())
             ->setData('base_hidden_tax_amount', 0)
             ->setData('base_original_price', $profile->getInitAmount())
             ->setData('base_price_incl_tax', $profile->getInitAmount())
             ->setData('base_row_tax', $taxAmount)
             ->setData('base_row_total_incl_tax', $profile->getInitAmount())
             ->setData('base_tax_amount', $taxAmount)
             ->setData('base_taxable_amount', $profile->getInitAmount())
             ->setData('calculation_price', $amountWithoutTax)
             ->setData('converted_price', $amountWithoutTax)
             ->setData('discount_calculation_price', $profile->getInitAmount())
             ->setData('price_incl_tax', $profile->getInitAmount())
             ->setData('row_tax', $taxAmount)
             ->setData('row_total_incl_tax', $profile->getInitAmount())
             ->setData('taxable_amount', $profile->getInitAmount());

        $itemData = $item->getData();
        $item->addData($profile->getOrderItemInfo());
        $item->addData($itemData);
        $item->setId(null);
        $order->getItemsCollection()->removeItemByKey(0);
        $order->addItem($item);
        $order->save();

        return $order;
    }

    /**
     * Takes the responseparameters and applies the corresponding action (capture/authorize) on the order payment.
     * Also updates the Ingenico ePayments information on the payment.
     *
     * @param string[]                           $responseParams
     * @param Mage_Sales_Model_Recurring_Profile $profile
     * @param Mage_Sales_Model_Order             $order
     *
     * @return Mage_Sales_Model_Order $order - updated order
     */
    protected function processPaymentFeedback($responseParams, $profile, $order)
    {
        $payment = $order->getPayment();
        foreach ($responseParams as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $payment->setTransactionId($responseParams['PAYID'])
                ->setCurrencyCode($responseParams['currency'])
                ->setIsTransactionClosed(0);
        $order->save();
        $profile->addOrderRelation($order->getId());
        if ($this->getPaymentHelper()->isPaymentAuthorizeType($responseParams['STATUS'])) {
            $payment->registerAuthorizationNotification($responseParams['amount']);
        } elseif ($this->getPaymentHelper()->isPaymentCaptureType($responseParams['STATUS'])) {
            $payment->registerCaptureNotification($responseParams['amount']);
        } elseif ($responseParams['STATUS']
            == Netresearch_OPS_Model_Payment_Abstract::OPS_WAITING_FOR_IDENTIFICATION
        ) {
            // handle 3ds payment - only relevant for initial order creation
            $payment->setIsTransactionPending(1);
            $payment->registerCaptureNotification($responseParams['amount'], true);
        }
        $order->save();
        $this->getPaymentHelper()->applyStateForOrder($order, $responseParams);

        return $order;
    }

}