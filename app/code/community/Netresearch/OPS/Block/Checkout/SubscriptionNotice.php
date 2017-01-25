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
 * SubscriptionNotice.php
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 *
 *
 * @method Netresearch_OPS_Block_Checkout_SubscriptionNotice setProfile(Mage_Catalog_Model_Product $product)
 *
 */
class Netresearch_OPS_Block_Checkout_SubscriptionNotice extends Mage_Core_Block_Template
{

    /**
     * Returns the quotes nominal items product
     *
     * @return Mage_Sales_Model_Recurring_Profile
     */
    public function getProfile()
    {
        if (!$this->hasData('profile')) {
            /** @var Mage_Sales_Model_Quote_Item $item */
            foreach ($this->getQuote()->getAllItems() as $item) {
                $product = $item->getProduct();
                if (is_object($product) && $product->isRecurring()
                    && $profile = Mage::getModel('sales/recurring_profile')->importProduct($product)
                ) {
                    $profile->importQuote($this->getQuote());
                    $profile->importQuoteItem($item);
                    $this->setProfile($profile);
                    break;
                }
            }
        }

        return $this->getData('profile');
    }

    /**
     * Get checkout session quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Checks wether the recurring profile has a trial period specified
     *
     * @return bool
     */
    public function hasTrial()
    {
        return (bool)$this->getProfile()->getTrialPeriodUnit();
    }

    /**
     * Checks wether the recurring profile has an initial fee specified
     *
     * @return bool
     */
    public function hasInitialFee()
    {
        return $this->getProfile()->getInitAmount() > 0;
    }

    /**
     * @return string
     */
    public function getInitialFeeText()
    {
        return $this->__(
            'You will be charged an initial amount of %s.',
            $this->helper('checkout')->formatPrice($this->getProfile()->getInitAmount())
        );
    }

    /**
     * @return string
     */
    public function getTrialSubscriptionText()
    {
        $profile = $this->getProfile();

        return $this->__(
            'A trial subscription will be created. This will charge you %s every %s %s until %s.',
            $this->helper('checkout')->formatPrice(
                $profile->getTrialBillingAmount() + $profile->getShippingAmount()
            ),
            $profile->getTrialPeriodFrequency(),
            $profile->getPeriodUnitLabel($profile->getTrialPeriodUnit()),
            $this->localizeDate($this->getTrialSubscriptionEndDate())
        );
    }

    /**
     * @return string
     */
    public function getRegularSubscriptionText()
    {
        $profile = $this->getProfile();

        $message = $this->__(
            'A subscription will be created. This will charge you %s every %s %s.',
            $this->helper('checkout')->formatPrice(
                $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount()
            ),
            $profile->getPeriodFrequency(),
            $profile->getPeriodUnitLabel($profile->getPeriodUnit())
        );

        if ($this->getRegularSubscriptionEndDate()) {
            $message .= $this->__(
                ' The subscription will end on %s.',
                $this->localizeDate($this->getRegularSubscriptionEndDate())
            );
        }
        return $message;
    }

    public function getCancelInformationText()
    {
        return $this->__(
            'To cancel the subscription, please send an email to the shop owner' .
            ' or request this by clicking the suspend button on the subscriptions detail view in your customer account.'
            . ' A link to that page will be displayed on the checkout success page.'
        );
    }

    public function displayNotice()
    {
        $result = true;
        if (!$this->getProfile()
            || $this->getQuote()->getPayment()->getMethod() != Netresearch_OPS_Model_Payment_Recurring_Cc::CODE
        ) {
            $result = false;
        }

        return $result;
    }

    protected function getTrialSubscriptionEndDate()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag $parameterModel */
        $parameterModel = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        $enddate = null;
        $startDate = new DateTime($this->getProfile()->getStartDatetime());
        $enddate = $parameterModel->calculateEndDate(
            $startDate, $this->getProfile()->getTrialPeriodUnit(),
            $this->getProfile()->getTrialPeriodFrequency(), $this->getProfile()->getTrialPeriodMaxCycles()
        );

        return $enddate;
    }

    protected function getRegularSubscriptionEndDate()
    {
        $parameterModel = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        $enddate = null;
        if ($this->hasTrial()) {
            $startDate = $this->getTrialSubscriptionEndDate();
        } else {
            $startDate = new DateTime($this->getProfile()->getStartDatetime());
        }
        $enddate = $parameterModel->calculateEndDate(
            $startDate, $this->getProfile()->getPeriodUnit(),
            $this->getProfile()->getPeriodFrequency(), $this->getProfile()->getPeriodMaxCycles()
        );

        return $enddate;
    }

    protected function localizeDate(DateTime $date)
    {
        $date = new Zend_Date($date->getTimestamp());
        return Mage::helper('core')->formatDate($date);
    }
}