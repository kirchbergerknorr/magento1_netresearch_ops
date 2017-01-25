<?php
/**
 * Netresearch_OPS_Helper_DirectLink
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Directlink extends Mage_Core_Helper_Abstract
{
    /**
     * Creates Transactions for directlink activities
     *
     * @param Mage_Sales_Model_Order $order
     * @param int $transactionID - persistent transaction id
     * @param int $subPayID - identifier for each transaction
     * @param array $arrInformation - add dynamic data
     * @param string $typename - name for the transaction exp.: refund
     * @param string $comment - order comment
     *
     * @return Netresearch_OPS_Helper_Directlink $this
     */
    public function directLinkTransact($order,$transactionID, $subPayID,
        $arrInformation = array(), $typename, $comment, $closed = 0)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionID."/".$subPayID);
        $payment->setParentTransactionId($transactionID);
        $payment->setIsTransactionClosed($closed);
        $payment->setTransactionAdditionalInfo($arrInformation, null);
        return $this;
    }

    /**
     * Checks if there is an active transaction for a special order for special
     * type
     *
     * @param string $type - refund, capture etc.
     * @param int $orderID
     * @return bol success
     */
    public function checkExistingTransact($type, $orderID)
    {
        $transaction = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->addAttributeToFilter('order_id', $orderID)
            ->addAttributeToFilter('txn_type', $type)
            ->addAttributeToFilter('is_closed', 0)
            ->getLastItem();

        return ($transaction->getTxnId()) ? true : false;
    }

    /**
     * get transaction type for given OPS status
     *
     * @param string $status
     *
     * @return string
     */
    public function getTypeForStatus($status)
    {
        switch ($status) {
            case Netresearch_OPS_Model_Status::REFUNDED :
            case Netresearch_OPS_Model_Status::REFUND_PENDING:
            case Netresearch_OPS_Model_Status::REFUND_UNCERTAIN :
            case Netresearch_OPS_Model_Status::REFUND_REFUSED :
            case Netresearch_OPS_Model_Status::REFUNDED_OK :
                return Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_TRANSACTION_TYPE;
            case Netresearch_OPS_Model_Status::PAYMENT_REQUESTED :
            case Netresearch_OPS_Model_Status::PAYMENT_PROCESSED_BY_MERCHANT :
            case Netresearch_OPS_Model_Status::PAYMENT_PROCESSING:
            case Netresearch_OPS_Model_Status::PAYMENT_UNCERTAIN:
            case Netresearch_OPS_Model_Status::PAYMENT_IN_PROGRESS:
            case Netresearch_OPS_Model_Status::PAYMENT_REFUSED:
            case Netresearch_OPS_Model_Status::PAYMENT_DECLINED_BY_ACQUIRER:
                return Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_TRANSACTION_TYPE;
            case Netresearch_OPS_Model_Status::AUTHORIZED_AND_CANCELLED: //Void finished
            case Netresearch_OPS_Model_Status::AUTHORIZED_AND_CANCELLED_OK:
            case Netresearch_OPS_Model_Status::DELETION_WAITING:
            case Netresearch_OPS_Model_Status::DELETION_UNCERTAIN:
            case Netresearch_OPS_Model_Status::DELETION_REFUSED:
                return Netresearch_OPS_Model_Payment_Abstract::OPS_VOID_TRANSACTION_TYPE;
            case Netresearch_OPS_Model_Status::PAYMENT_DELETED:
            case Netresearch_OPS_Model_Status::PAYMENT_DELETION_PENDING:
            case Netresearch_OPS_Model_Status::PAYMENT_DELETION_UNCERTAIN:
            case Netresearch_OPS_Model_Status::PAYMENT_DELETION_REFUSED:
            case Netresearch_OPS_Model_Status::PAYMENT_DELETION_OK:
            case Netresearch_OPS_Model_Status::DELETION_HANDLED_BY_MERCHANT:
                return Netresearch_OPS_Model_Payment_Abstract::OPS_DELETE_TRANSACTION_TYPE;
        }
    }

    /**
     * Process Direct Link Feedback to do: Capture, De-Capture and Refund
     *
     * @param Mage_Sales_Model_Order $order  Order
     * @param array                  $params Request params
     *
     * @return void
     */
    public function processFeedback($order, $params)
    {
        Mage::getModel('ops/response_handler')->processResponse($params, $order->getPayment()->getMethodInstance());
        $order->getPayment()->save();
    }

    /**
     * Get the payment transaction by PAYID and Operation
     *
     * @param Mage_Sales_Model_Order $order
     * @param int                    $payId
     * @param string                 $operation
     *
     * @return Mage_Sales_Model_Order_Payment_Transaction
     *
     * @throws Mage_Core_Exception
     */
    public function getPaymentTransaction($order, $payId, $operation)
    {
        $helper = Mage::helper('ops');
        $transactionCollection = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->addAttributeToFilter('txn_type', $operation)
            ->addAttributeToFilter('is_closed', 0)
            ->addAttributeToFilter('order_id', $order->getId());
        if ($payId != '') {
            $transactionCollection->addAttributeToFilter('parent_txn_id', $payId);
        }

        if ($transactionCollection->getSize()>1 || $transactionCollection->getSize() == 0) {
            $errorMsq = $helper->__(
                "Warning, transaction count is %s instead of 1 for the Payid '%s', order '%s' and Operation '%s'.",
                $transactionCollection->getSize(),
                $payId,
                $order->getId(),
                $operation
            );
            $helper->log($errorMsq);
            Mage::throwException($errorMsq);
        }

        if ($transactionCollection->getSize() == 1) {
            $transaction = $transactionCollection->getLastItem();
            $transaction->setOrderPaymentObject($order->getPayment());
            return $transaction;
        }
    }


    /**
     * Check if there are payment transactions for an order and an operation
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $authorization
     *
     * @return boolean
     */
    public function hasPaymentTransactions($order, $operation)
    {
        $transactionCollection = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->addAttributeToFilter('txn_type', $operation)
            ->addAttributeToFilter('is_closed', 0)
            ->addAttributeToFilter('order_id', $order->getId());

        return (0 < $transactionCollection->getSize());
    }

    /**
     * validate incoming and internal amount value format and convert it to float
     *
     * @param string
     * @return float
     */
    public function formatAmount($amount)
    {
        // Avoid quotes added somewhere unknown
        if (preg_match("/^[\"']([0-9-\..,-]+)[\"']$/i", $amount, $matches)) {
            Mage::helper('ops')->log(
                "Warning in formatAmount: Found quotes around amount in '" . var_export($amount, true) . "'"
            );
            $amount = $matches[1];
        }

        return number_format($amount, 2);
    }

    /**
     * determine if the current OPS request is valid
     *
     * @param array                  $transactions     Iteratable of Mage_Sales_Model_Order_Payment_Transaction
     * @param Mage_Sales_Model_Order $order
     * @param array                  $opsRequestParams
     *
     * @return boolean
     */
    public function isValidOpsRequest(
        $openTransaction,
        Mage_Sales_Model_Order $order,
        $opsRequestParams
    )
    {
        if ($this->getTypeForStatus($opsRequestParams['STATUS']) == Netresearch_OPS_Model_Payment_Abstract::OPS_DELETE_TRANSACTION_TYPE) {
            return false;
        }

        $requestedAmount = null;
        if (array_key_exists('amount', $opsRequestParams)) {
            $requestedAmount = $this->formatAmount($opsRequestParams['amount']);
        }

        /* find expected amount */
        $expectedAmount = null;
        if (null !== $openTransaction) {
            $transactionInfo = unserialize($openTransaction->getAdditionalInformation('arrInfo'));
            if (array_key_exists('amount', $transactionInfo)) {
                if (null === $expectedAmount || $transactionInfo['amount'] == $requestedAmount) {
                    $expectedAmount = $this->formatAmount($transactionInfo['amount']);
                }
            }
        }

        if ($this->getTypeForStatus($opsRequestParams['STATUS']) == Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_TRANSACTION_TYPE
            || $this->getTypeForStatus($opsRequestParams['STATUS']) == Netresearch_OPS_Model_Payment_Abstract::OPS_VOID_TRANSACTION_TYPE
        ) {
            if (null === $requestedAmount || 0 == count($openTransaction) || $requestedAmount != $expectedAmount) {
                return false;
            }
        }

        if ($this->getTypeForStatus($opsRequestParams['STATUS']) == Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_TRANSACTION_TYPE) {
            if (null === $requestedAmount) {
                Mage::helper('ops')->log('Please configure Ingenico ePayments to submit amount');
                return false;
            }
            $grandTotal = $this->formatAmount(Mage::helper('ops/payment')->getBaseGrandTotalFromSalesObject($order));
            if ($grandTotal != $requestedAmount) {
                if (null === $openTransaction || $expectedAmount != $requestedAmount) {
                    return false;
                }
            }
        }
        return true;
    }

    public function performDirectLinkRequest($quote, $params, $storeId = null)
    {
        $url = Mage::getModel('ops/config')->getDirectLinkGatewayOrderPath($storeId);
        $response = Mage::getSingleton('ops/api_directlink')->performRequest($params, $url, $storeId);
        /**
         * allow null as valid state for creating the order with status 'pending'
         */
        if (null != $response['STATUS'] && Mage::helper('ops/payment')->isPaymentFailed($response['STATUS'])) {
            Mage::getSingleton('checkout/type_onepage')->getCheckout()->setGotoSection('payment');
            Mage::throwException(Mage::helper('ops/data')->__('Ingenico ePayments Payment failed'));
        }

        return $response;
    }
}
