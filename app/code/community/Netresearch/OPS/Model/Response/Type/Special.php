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
 * Special.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Model_Response_Type_Special extends Netresearch_OPS_Model_Response_Type_Abstract
{
    /**
     * Handles the specific actions for the concrete payment statuses
     */
    protected function _handleResponse()
    {
        if (!Netresearch_OPS_Model_Status::isSpecialStatus($this->getStatus())) {
            Mage::throwException(Mage::helper('ops')->__('%s is not a special status!', $this->getStatus()));
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getMethodInstance()->getInfoInstance();
        $order = $payment->getOrder();
        $helper = Mage::helper('ops');

        if ($this->getStatus() == Netresearch_OPS_Model_Status::WAITING_FOR_IDENTIFICATION) {
            $payment->setIsTransactionPending(true);
            $payment->setAdditionalInformation('HTML_ANSWER', $this->getHtmlAnswer());
            $order->addStatusHistoryComment(
                $this->getIntermediateStatusComment($helper->__('Customer redirected for 3DS authorization.'))
            );
        }

        if ($this->getStatus() == Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT) {
            $order->addStatusHistoryComment(
                $this->getIntermediateStatusComment(
                    $helper->__(
                        'Customer received your payment instructions, waiting for actual payment.'
                    )
                )
            );

            // gateway payments do not send confirmation emails by default
            Mage::helper('ops/data')->sendTransactionalEmail($order);
        }

        if ($this->getStatus() == Netresearch_OPS_Model_Status::INVALID_INCOMPLETE) {
            //save status information to order before exception
            if($this->getShouldRegisterFeedback()){
                $this->updateAdditionalInformation();
                $payment->save();
            }


            $message = Mage::helper('ops')->__('Ingenico ePayments status 0, the action failed.');
            if ($helper->isAdminSession()) {
                $message .= ' ' . $this->getNcerror() . ' ' . $this->getNcerrorplus();
            }
            Mage::throwException($message);
        }

        $payment->save();
        $order->save();
    }
}
