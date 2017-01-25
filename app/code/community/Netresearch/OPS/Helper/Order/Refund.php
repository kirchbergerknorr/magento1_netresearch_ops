<?php

/**
 * Netresearch_OPS_Helper_Order_Refund
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Order_Refund extends Netresearch_OPS_Helper_Order_Abstract
{
    protected $payment;
    protected $amount;
    protected $params;

    protected function getFullOperationCode()
    {
        return Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL;
    }

    protected function getPartialOperationCode()
    {
        return Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL;
    }

    protected function getPreviouslyProcessedAmount($payment)
    {
        return $payment->getBaseAmountRefundedOnline();
    }


    /**
     * @param Varien_Object $payment
     * @return $this
     */
    public function setPayment(Varien_Object $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param  array $params
     * @return $this
     */
    public function setCreditMemoRequestParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return array params
     */
    public function getCreditMemoRequestParams()
    {
        if (!is_array($this->params)) {
            $this->setCreditMemoRequestParams(Mage::app()->getRequest()->getParams());
        }

        return $this->params;
    }

    public function getInvoiceFromCreditMemoRequest()
    {
        $params = $this->getCreditMemoRequestParams();
        if (array_key_exists('invoice_id', $params)) {
            return Mage::getModel('sales/order_invoice')->load($params['invoice_id']);
        }

        return null;
    }

    public function getCreditMemoFromRequest()
    {
        $params = $this->getCreditMemoRequestParams();
        if (array_key_exists('creditmemo', $params)) {
            return $params['creditmemo'];
        }

        return array();
    }

    /**
     * @param $payment
     * @param $amount
     * @return mixed
     */
    public function prepareOperation($payment, $amount)
    {
        $params = $this->getCreditMemoRequestParams();

        if (array_key_exists('creditmemo', $params)) {
            $arrInfo           = $params['creditmemo'];
            $arrInfo['amount'] = $amount;
        }
        $arrInfo['type']      = $this->determineType($payment, $amount);
        $arrInfo['operation'] = $this->determineOperationCode($payment, $amount);

        if($arrInfo['type'] == 'full'){
            // hard overwrite operation code for last transaction
            $arrInfo['operation'] = $this->getFullOperationCode();
        }


        return $arrInfo;
    }

    /**
     * Checks for open refund transaction
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return Mage_Sales_Model_Order_Payment_Transaction|null
     */
    public function getOpenRefundTransaction($payment)
    {
        /** @var Mage_Sales_Model_Resource_Order_Payment_Transaction_Collection $refundTransactions */
        $refundTransactions = Mage::getModel('sales/order_payment_transaction')->getCollection();
        $transaction = $refundTransactions->addPaymentIdFilter($payment->getId())
            ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
            ->setOrderFilter($payment->getOrder())
            ->addFieldToFilter('is_closed', 0)
            ->getFirstItem();

        return $transaction;
    }
}
