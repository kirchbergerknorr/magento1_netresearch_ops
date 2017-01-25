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
 * Capture.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Model_Response_Type_Capture extends Netresearch_OPS_Model_Response_Type_Abstract
{
    /**
     * Handles the specific actions for the concrete payment status
     */
    protected function _handleResponse()
    {
        if (!Netresearch_OPS_Model_Status::isCapture($this->getStatus())) {
            Mage::throwException(Mage::helper('ops')->__('%s is not a capture status!', $this->getStatus()));
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getMethodInstance()->getInfoInstance();
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();


        /**
         * Basically we have to check the following things here:
         *
         * Order state      - payment_review suggests an already existing intermediate status
         *                  - pending_payment or new suggests no feedback yet
         *
         * payment status   - intermediate and not failed -> move to payment review or add another comment
         *                  - intermediate and failed -> if recoverable let the order open and place comment
         *                  - finished - finish invoice dependent on order state
         */

        if (Netresearch_OPS_Model_Status::isIntermediate($this->getStatus())) {
            $this->processIntermediateState($payment, $order);
        } else {
            // final means state 9 or 95
            $this->processFinalState($order, $payment);
        }

        if ($this->getShouldRegisterFeedback()) {
            $this->registerFeedBack($payment, $order);
        }
    }

    /**
     * process intermediate state
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param Mage_Sales_Model_Order $order
     */
    protected function processIntermediateState($payment, $order)
    {
        $message = $this->getIntermediateStatusComment();
        $payment->setIsTransactionPending(true);
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
            || $order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING
        ) {
            // transaction was placed on PSP, initial feedback to shop or partial capture case
            $payment->setPreparedMessage($message);
            if ($this->getShouldRegisterFeedback()) {
                $payment->registerCaptureNotification($this->getAmount());
            }

        } elseif ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            // payment was pending and is still pending
            $payment->setIsTransactionApproved(false);
            $payment->setIsTransactionDenied(false);
            $payment->setPreparedMessage($message);

            if ($this->getShouldRegisterFeedback()) {
                $payment->setNotificationResult(true);
                $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, false);
            }
        }
    }

    /**
     * process final state
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param Mage_Sales_Model_Order $order
     */
    protected function processFinalState($order, $payment)
    {
        $message = $this->getFinalStatusComment();
        if ($order->getState() == Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            $payment->setNotificationResult(true);
            $payment->setPreparedMessage($message);
            if ($this->getShouldRegisterFeedback()) {
                $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_ACCEPT, false);
                $transaction = $payment->getTransaction($payment->getLastTransId());
                if ($transaction) {
                    $transaction->close(true);
                }
            }
        } else {
            $payment->setPreparedMessage($message);
            if ($this->getShouldRegisterFeedback()) {
                $payment->registerCaptureNotification($this->getAmount());
            }
        }
    }

    /**
     * save payment and order object and send transaction email
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param Mage_Sales_Model_Order $order
     */
    protected function registerFeedBack($payment, $order)
    {
        $payment->save();
        $order->save();

        // gateway payments do not send confirmation emails by default
        Mage::helper('ops/data')->sendTransactionalEmail($order);

        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::getModel('sales/order_invoice')->load($this->getTransactionId(), 'transaction_id');
        if ($invoice->getId()) {
            Mage::helper('ops')->sendTransactionalEmail($invoice);
        }
    }
}