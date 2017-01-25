<?php

/**
 * Created by PhpStorm.
 * User: paul.siedler
 * Date: 18.09.2014
 * Time: 13:48
 */
abstract class Netresearch_OPS_Helper_Order_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * Return partial operation code for transaction type
     *
     * @return string Operation code defined in Netresearch_OPS_Model_Payment_Abstract
     */
    protected abstract function getPartialOperationCode();

    /**
     * Return full operation code for transaction type
     *
     * @return string Operation code defined in Netresearch_OPS_Model_Payment_Abstract
     */
    protected abstract function getFullOperationCode();


    /**
     * Checks if partial capture and returns 'full' or 'partial'
     *
     * @param Mage_Sales_Order_Payment $payment
     * @param float $amount
     * @return string 'partial' if type is partial, else 'full'
     */
    public function determineType($payment, $amount)
    {
        $orderTotalAmount = round(
            (Mage::helper('ops/payment')->getBaseGrandTotalFromSalesObject($payment->getOrder())) * 100,
            0
        );
        $amount           = round(($amount * 100), 0);

        if (abs($orderTotalAmount - $amount) <= 1) {
            return 'full';
        } else {
            return 'partial';
        }
    }


    /**
     * checks if the amount captured/refunded is equal to the amount of the full order
     * and returns the operation code accordingly
     *
     * @param Mage_Sales_Order_Payment $payment
     * @param float $amount
     * @return string operation code for the requested amount
     * @see getPartialOperationCode() and getFullOperationCode()     *
     */
    public function determineOperationCode($payment, $amount)
    {
        $orderTotalAmount = round(
            (Mage::helper('ops/payment')->getBaseGrandTotalFromSalesObject($payment->getOrder())) * 100,
            0
        );
        $totalProcessedAmount      = round((($this->getPreviouslyProcessedAmount($payment) + $amount) * 100), 0);

        if (abs($orderTotalAmount - $totalProcessedAmount) <= 1 ) {
            return $this->getFullOperationCode();
        } else {
            return $this->getPartialOperationCode();
        }

    }

    /**
     * Returns the Amount already processed for this kind of operation
     * eg. getBaseAmountPaidOnline and getRefundedAmount
     *
     * @param Mage_Sales_Order_Payment $payment
     * @return float amount already processed for this kind of operation
     */
    protected abstract function getPreviouslyProcessedAmount($payment);


}