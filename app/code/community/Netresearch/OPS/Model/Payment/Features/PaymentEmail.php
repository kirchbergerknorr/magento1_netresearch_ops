<?php
/**
 * Netresearch OPS
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
 * Implements functionality to send Ingenico ePayments specific mails
 *
 * @category Payment method
 * @package  Netresearch OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Model_Payment_Features_PaymentEmail
{

    protected function getConfig()
    {
        return Mage::getModel('ops/config');
    }

    /**
     * Check if payment email is available for order
     *
     * @param $order
     *
     * @return bool
     */
    public function isAvailableForOrder($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $status = $order->getPayment()->getAdditionalInformation('status');

            return Netresearch_OPS_Model_Status::canResendPaymentInfo($status);
        }

        return false;
    }


    /**
     * Resends the payment information and returns true/false, depending if succeeded or not
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return boolean success state
     */
    public function resendPaymentInfo(Mage_Sales_Model_Order $order)
    {

        // reset payment method so the customer can choose freely from all available methods
        $this->setPaymentMethodToGeneric($order);

        $identity = $this->getIdentity($this->getConfig()->getResendPaymentInfoIdentity($order->getStoreId()));

        if ($order->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_PayPerMail) {
            $template =  $this->getConfig()->getPayPerMailTemplate($order->getStoreId());
        } else {
            $template = $this->getConfig()->getResendPaymentInfoTemplate($order->getStoreId());
        }

        $emailTemplate = $this->prepareTemplate(
            $template,
            $identity->getEmail(),
            $identity->getName()
        );

        $parameters = array(
            "order"       => $order,
            "paymentLink" => $this->generatePaymentLink($order),
            "store"       => Mage::app()->getStore($order->getStoreId())
        );

        return $emailTemplate->send($order->getCustomerEmail(), $order->getCustomerName(), $parameters);

    }

    /**
     * Generates the payment url
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    protected function generatePaymentLink(Mage_Sales_Model_Order $order)
    {
        $opsOrderId = Mage::helper('ops/order')->getOpsOrderId($order);

        $url = Mage::getModel('ops/config')->getPaymentRetryUrl(
            Mage::helper('ops/payment')->validateOrderForReuse($opsOrderId, $order->getStoreId()),
            $order->getStoreId()
        );

        return $url;
    }

    /**
     * Set payment method to Netresearch_OPS_Model_Payment_Flex
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @throws Exception
     */
    protected function setPaymentMethodToGeneric(Mage_Sales_Model_Order $order)
    {
        if (!$order->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_PayPerMail) {
            $order->getPayment()->setMethod(Netresearch_OPS_Model_Payment_Flex::CODE)->save();
        }
    }

    /**
     * Sends suspend subscription mail to configured store contact via configured mail template
     *
     * @param Mage_Sales_Model_Recurring_Profile $profile
     * @param Mage_Customer_Model_Customer       $customer
     *
     * @return bool
     */
    public function sendSuspendSubscriptionMail($profile, $customer)
    {
        if (null === $profile  || null === $customer) {
            Mage::throwException('Could not send mail due to internal error!');
        }

        $emailTemplate = $this->prepareTemplate(
            $this->getConfig()->getSuspendSubscriptionTemplate($profile->getStoreId()),
            $customer->getEmail(),
            $customer->getName()
        );

        $emailTemplate->addBcc($customer->getEmail());

        $parameters = array(
            "profile"  => $profile,
            "customer" => $customer,
            "store"    => Mage::app()->getStore($profile->getStoreId())
        );

        $identity = $this->getIdentity($this->getConfig()->getSuspendSubscriptionIdentity($profile->getStoreId()));

        return $emailTemplate->send($identity->getEmail(), $identity->getName(), $parameters);

    }


    /**
     * Loads email and name of the given store identity
     *
     * @param string $key - identity to load, defaults to sales
     *
     * @return Varien_Object with data name and email
     */
    protected function getIdentity($key = 'sales')
    {
        $identity = new Varien_Object();
        $identity->setName(Mage::getStoreConfig('trans_email/ident_' . $key . '/name'))
                 ->setEmail(Mage::getStoreConfig('trans_email/ident_' . $key . '/email'));

        return $identity;
    }

    /**
     * Loads the given template by identifier, sets sender mail and name
     *
     * @param string $template
     * @param string $senderMail
     * @param string $senderName
     *
     * @return Mage_Core_Model_Email_Template
     */
    protected function prepareTemplate($template, $senderMail, $senderName)
    {
        $emailTemplate = Mage::getModel('core/email_template')->load($template);
        if (null === $emailTemplate->getTemplateSubject()) {
            $emailTemplate = $emailTemplate->loadDefault($template);
        }
        $emailTemplate->setSenderName($senderName)
                      ->setSenderEmail($senderMail);

        return $emailTemplate;
    }

}
