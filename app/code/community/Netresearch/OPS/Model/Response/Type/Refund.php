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
 * Refund.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Model_Response_Type_Refund extends Netresearch_OPS_Model_Response_Type_Abstract
{
    /**
     * Handles the specific actions for the concrete payment status
     */
    protected function _handleResponse()
    {
        if (!Netresearch_OPS_Model_Status::isRefund($this->getStatus())) {
            Mage::throwException(Mage::helper('ops')->__('%s is not a refund status!', $this->getStatus()));
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getMethodInstance()->getInfoInstance();

        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        if (!$this->hasPayidsub()) {
            $creditMemo = $this->determineCreditMemo();
            $payment->setRefundTransactionId($creditMemo->getTransactionId());
        } else {
            $creditMemo = Mage::getModel('sales/order_creditmemo')->load(
                $this->getTransactionId(), 'transaction_id'
            );
            $payment->setRefundTransactionId($this->getTransactionId());
        }

        if ($creditMemo->getId()) {
            if (Netresearch_OPS_Model_Status::isFinal($this->getStatus())
                && $creditMemo->getState() == Mage_Sales_Model_Order_Creditmemo::STATE_OPEN
            ) {
                $creditMemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED);
                $this->closeRefundTransaction($creditMemo);
                $this->addFinalStatusComment();
            } elseif ($this->getStatus() == Netresearch_OPS_Model_Status::REFUND_REFUSED) {
                $order = $this->processRefundRefused($creditMemo);
            } else {
                $this->addIntermediateStatusComment();
            }

        } else {
            if ($this->getShouldRegisterFeedback()) {
                $payment->setParentTransactionId($this->getPayid());
                $payment->setTransactionId($this->getTransactionId());
                $payment->setIsTransactionClosed(Netresearch_OPS_Model_Status::isFinal($this->getStatus()));
                $payment->registerRefundNotification($this->getAmount());
            }
            if (Netresearch_OPS_Model_Status::isFinal($this->getStatus())) {
                $this->addFinalStatusComment();
            } else {
                $this->addIntermediateStatusComment();
                $creditMemo = $payment->getCreatedCreditMemo() ?: $payment->getCreditmemo();
                if ($creditMemo) {
                    $creditMemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN);
                }
            }
        }

        if ($this->getShouldRegisterFeedback()) {
            $this->registerFeedBack($order, $payment, $creditMemo);
        }

    }

    /**
     * Will load the creditmemo by identifying open refund transactions
     *
     * @return Mage_Sales_Model_Order_Creditmemo|null
     */
    protected function determineCreditMemo()
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getMethodInstance()->getInfoInstance();
        $refundTransaction = Mage::helper('ops/order_refund')->getOpenRefundTransaction($payment);
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = Mage::getModel('sales/order_creditmemo')->load(
            $refundTransaction->getTxnId(), 'transaction_id'
        );

        return $creditmemo;
    }

    /**
     * Closes the refund transaction for the given creditmemo
     *
     * @param $creditMemo
     */
    protected function closeRefundTransaction($creditMemo)
    {
        $refundTransaction = $this->getMethodInstance()->getInfoInstance()->lookupTransaction(
            $creditMemo->getTransactionId(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND
        );
        if ($refundTransaction) {
            $refundTransaction->setIsClosed(true)
                ->save();
        }
    }

    /**
     * process refund refused response
     *
     * @param $creditMemo
     * @return mixed
     */
    protected function processRefundRefused($creditMemo)
    {
        $order = $creditMemo->getOrder();
        $creditMemo->cancel()->save();
        $this->closeRefundTransaction($creditMemo);
        $invoice = Mage::getModel('sales/order_invoice')->load($creditMemo->getInvoiceId());
        $invoice->setIsUsedForRefund(0)
            ->setBaseTotalRefunded(
                $invoice->getBaseTotalRefunded() - $creditMemo->getBaseGrandTotal()
            );
        $creditMemo->setInvoice($invoice);
        /** @var Mage_Sales_Model_Order_Creditmemo_Item $item */
        foreach ($creditMemo->getAllItems() as $item) {
            $item->getOrderItem()->setAmountRefunded(
                $item->getOrderItem()->getAmountRefunded() - $item->getRowTotal()
            );
            $item->getOrderItem()->setBaseAmountRefunded(
                $item->getOrderItem()->getBaseAmountRefunded() - $item->getBaseRowTotal()
            );
        }
        $order->setTotalRefunded($order->getTotalRefunded() - $creditMemo->getBaseGrandTotal());
        $order->setBaseTotalRefunded($order->getBaseTotalRefunded() - $creditMemo->getBaseGrandTotal());

        $this->addRefusedStatusComment();
        $state = Mage_Sales_Model_Order::STATE_COMPLETE;
        if ($order->canShip() || $order->canInvoice()) {
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
        }
        $order->setState(
            $state,
            true,
            $this->getRefusedStatusComment(Mage::helper('ops')->__('Refund refused by Ingenico ePayments.'))
        );

        return $order;
    }

    /**
     * register feedback
     *
     * @param $order
     * @param $payment
     * @param $creditMemo
     */
    protected function registerFeedBack($order, $payment, $creditMemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($order)
            ->addObject($payment)
            ->addObject($creditMemo);

        if ($creditMemo->getInvoice()) {
            $transactionSave->addObject($creditMemo->getInvoice());
        }
        $transactionSave->save();
    }

}