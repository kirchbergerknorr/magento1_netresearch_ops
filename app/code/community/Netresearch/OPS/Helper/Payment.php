<?php

/**
 * Netresearch_OPS_Helper_Payment
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Payment extends Mage_Core_Helper_Abstract
{
    protected $shaAlgorithm = null;

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Netresearch_OPS_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('ops/config');
    }

    /**
     * Get encrypt / decrypt algorithm
     *
     * @return string
     */
    public function getCryptMethod()
    {
        if (null === $this->shaAlgorithm) {
            $this->shaAlgorithm = $this->getConfig()->getConfigData('secret_key_type');
        }

        return $this->shaAlgorithm;
    }

    /**
     * Crypt Data by SHA1 ctypting algorithm by secret key
     *
     * @param array  $data
     * @param string $key
     *
     * @return string hash
     */
    public function shaCrypt($data, $key = '')
    {
        if (is_array($data)) {
            return hash($this->getCryptMethod(), implode("", $data));
        }
        if (is_string($data)) {
            return hash($this->getCryptMethod(), $data);
        } else {
            return "";
        }
    }

    /**
     * Check hash crypted by SHA1 with existing data
     *
     * @param array  $data
     * @param string $hashFromOPS
     * @param string $key
     *
     * @return bool
     */
    public function shaCryptValidation($data, $hashFromOPS, $key = '')
    {
        if (is_array($data)) {
            $data = implode("", $data);
        }

        $hashUtf8 = strtoupper(hash($this->getCryptMethod(), $data));
        $hashNonUtf8 = strtoupper(hash($this->getCryptMethod(), utf8_encode($data)));

        $helper = Mage::helper('ops');
        $helper->log($helper->__("Module Secureset: %s", $data));

        if ($this->compareHashes($hashFromOPS, $hashUtf8)) {
            return true;
        } else {
            $helper->log($helper->__("Trying again with non-utf8 secureset"));

            return $this->compareHashes($hashFromOPS, $hashNonUtf8);
        }
    }

    protected function compareHashes($hashFromOPS, $actual)
    {
        $helper = Mage::helper('ops');
        $helper->log(
            $helper->__(
                "Checking hashes\nHashed String by Magento: %s\nHashed String by Ingenico ePayments: %s",
                $actual,
                $hashFromOPS
            )
        );

        if ($hashFromOPS == $actual) {
            Mage::helper('ops')->log("Successful validation");

            return true;
        }

        return false;
    }

    /**
     * Return set of data which is ready for SHA crypt
     *
     * @param array  $params
     * @param string $SHAkey
     *
     * @return string
     */
    public function getSHAInSet($params, $SHAkey)
    {
        $params = $this->prepareParamsAndSort($params);
        $plainHashString = "";
        foreach ($params as $paramSet):
            if ($paramSet['value'] == '' || $paramSet['key'] == 'SHASIGN' || is_array($paramSet['value'])) {
                continue;
            }
            $plainHashString .= strtoupper($paramSet['key']) . "=" . $paramSet['value'] . $SHAkey;
        endforeach;

        return $plainHashString;
    }

    /**
     * Return prepared and sorted array for SHA Signature Validation
     *
     * @param array $params
     *
     * @return string
     */
    public function prepareParamsAndSort($params)
    {
        unset($params['CardNo']);
        unset($params['Brand']);
        unset($params['SHASign']);

        $params = array_change_key_case($params, CASE_UPPER);

        //PHP ksort takes care about "_", OPS not
        $sortedParams = array();
        foreach ($params as $key => $value):
            $sortedParams[str_replace("_", "", $key)] = array('key' => $key, 'value' => $value);
        endforeach;
        ksort($sortedParams);

        return $sortedParams;
    }

    /*
     * Get SHA-1-IN hash for ops-authentification
     *
     * All Parameters have to be alphabetically, UPPERCASE
     * Empty Parameters shouldn't appear in the secure string
     *
     * @param array  $formFields
     * @param string $shaCode
     *
     * @return string
     */
    public function getSHASign($formFields, $shaCode = null, $storeId = null)
    {
        if (null === $shaCode) {
            $shaCode = Mage::getModel('ops/config')->getShaOutCode($storeId);
        }
        $formFields = array_change_key_case($formFields, CASE_UPPER);
        uksort($formFields, 'strnatcasecmp');
        $plainHashString = '';
        foreach ($formFields as $formKey => $formVal) {
            if (null === $formVal || '' === $formVal || $formKey == 'SHASIGN') {
                continue;
            }
            $plainHashString .= strtoupper($formKey) . '=' . $formVal . $shaCode;
        }

        return $plainHashString;
    }

    /**
     * @param int $opsOrderId
     * @param int $storeId
     *
     * @return array
     */
    public function validateOrderForReuse($opsOrderId, $storeId)
    {

        return array(
            'orderID' => $opsOrderId,
            'SHASIGN' => strtoupper(
                $this->shaCrypt(
                    $this->getSHAInSet(
                        array('orderId' => $opsOrderId),
                        $this->getConfig()->getShaOutCode($storeId)
                    )
                )
            ),
        );
    }

    /**
     * We get some CC info from ops, so we must save it
     *
     * @param Mage_Sales_Model_Order $order
     * @param array                  $ccInfo
     *
     * @return $this
     */
    public function _prepareCCInfo($order, $ccInfo)
    {
        if (isset($ccInfo['CN'])) {
            $order->getPayment()->setCcOwner($ccInfo['CN']);
        }

        if (isset($ccInfo['CARDNO'])) {
            $order->getPayment()->setCcNumberEnc($ccInfo['CARDNO']);
            $order->getPayment()->setCcLast4(substr($ccInfo['CARDNO'], -4));
        }

        if (isset($ccInfo['ED'])) {
            $order->getPayment()->setCcExpMonth(substr($ccInfo['ED'], 0, 2));
            $order->getPayment()->setCcExpYear(substr($ccInfo['ED'], 2, 2));
        }

        return $this;
    }

    public function isPaymentAccepted($status)
    {
        return in_array(
            $status, array(
                Netresearch_OPS_Model_Status::AUTHORIZED,
                Netresearch_OPS_Model_Status::AUTHORIZATION_WAITING,
                Netresearch_OPS_Model_Status::AUTHORIZED_UNKNOWN,
                Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT,
                Netresearch_OPS_Model_Status::PAYMENT_REQUESTED,
                Netresearch_OPS_Model_Status::PAYMENT_PROCESSING,
                Netresearch_OPS_Model_Status::PAYMENT_UNCERTAIN,
                Netresearch_OPS_Model_Status::WAITING_FOR_IDENTIFICATION
            )
        );
    }

    public function isPaymentAuthorizeType($status)
    {
        return in_array(
            $status, array(
                Netresearch_OPS_Model_Status::AUTHORIZED,
                Netresearch_OPS_Model_Status::AUTHORIZATION_WAITING,
                Netresearch_OPS_Model_Status::AUTHORIZED_UNKNOWN,
                Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT
            )
        );
    }

    public function isPaymentCaptureType($status)
    {
        return in_array(
            $status, array(
                Netresearch_OPS_Model_Status::PAYMENT_REQUESTED,
                Netresearch_OPS_Model_Status::PAYMENT_PROCESSING,
                Netresearch_OPS_Model_Status::PAYMENT_UNCERTAIN
            )
        );
    }

    public function isPaymentFailed($status)
    {
        return false == $this->isPaymentAccepted($status);
    }

    /**
     * apply ops state for order
     *
     * @param Mage_Sales_Model_Order $order  Order
     * @param array                  $params Request params
     *
     * @return string
     */
    public function applyStateForOrder($order, $params)
    {

        Mage::getModel('ops/response_handler')->processResponse($params, $order->getPayment()->getMethodInstance());
        $order->getPayment()->save();

        $feedbackStatus = '';

        switch ($params['STATUS']) {
            case Netresearch_OPS_Model_Status::WAITING_FOR_IDENTIFICATION : //3D-Secure
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT;
                break;
            case Netresearch_OPS_Model_Status::AUTHORIZED:
            case Netresearch_OPS_Model_Status::AUTHORIZED_WAITING_EXTERNAL_RESULT:
            case Netresearch_OPS_Model_Status::AUTHORIZATION_WAITING:
            case Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT:
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT;
                break;
            case Netresearch_OPS_Model_Status::PAYMENT_REQUESTED:
            case Netresearch_OPS_Model_Status::PAYMENT_PROCESSING:
            case Netresearch_OPS_Model_Status::PAYMENT_PROCESSED_BY_MERCHANT:
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT;
                break;
            case Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED:
            case Netresearch_OPS_Model_Status::PAYMENT_REFUSED:
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_DECLINE;
                break;
            case Netresearch_OPS_Model_Status::CANCELED_BY_CUSTOMER:
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_CANCEL;
                break;
            default:
                //all unknown transaction will accept as exceptional
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_EXCEPTION;
        }

        return $feedbackStatus;
    }

    /**
     * Process success action by accept url
     *
     *
     * @param $order
     * @param $params
     * @param int $instantCapture
     * @throws Exception
     */
    public function acceptOrder($order, $params, $instantCapture = 0)
    {
        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
        $this->_prepareCCInfo($order, $params);
        $this->setPaymentTransactionInformation($order->getPayment(), $params, 'accept');
        $this->setFraudDetectionParameters($order->getPayment(), $params);

        if ($transaction = Mage::helper('ops/payment')->getTransactionByTransactionId($order->getQuoteId())) {
            $transaction->setTxnId($params['PAYID'])->save();
        }

        try {
            if (false === $this->forceAuthorize($order)
                && ($this->getConfig()->getConfigData('payment_action')
                    == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
                    || $instantCapture)
                && $params['STATUS'] != Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT
            ) {
                $this->_processDirectSale($order, $params, $instantCapture);
            } else {
                $this->_processAuthorize($order, $params);
            }
        } catch (Exception $e) {
            $this->_getCheckout()->addError(Mage::helper('ops')->__('Order can not be saved.'));
            throw $e;
        }
    }

    /**
     * Set Payment Transaction Information
     *
     * @param Mage_Sales_Model_Order_Payment $payment Sales Payment Model
     * @param array                          $params  Request params
     * @param string                         $action  Action (accept|cancel|decline|wait|exception)
     */
    protected function setPaymentTransactionInformation(Mage_Sales_Model_Order_Payment $payment, $params, $action)
    {
        $payment->setTransactionId($params['PAYID']);
        $code = $payment->getMethodInstance()->getCode();

        $isInline = false;

        /* In authorize mode we still have no authorization transaction for CC and DirectDebit payments,
         * so capture or cancel won't work. So we need to create a new authorization transaction for them
         * when a payment was accepted by Ingenico ePayments
         *
         * In exception-case we create the authorization-transaction too
         * because some exception-cases can turn into accepted
         */
        if (('accept' === $action || 'exception' === $action)
            && in_array($code, array('ops_cc', 'ops_directDebit'))
        ) {
            $payment->setIsTransactionClosed(false);
            $isInline = $this->isInlinePayment($payment);
            /* create authorization transaction for non-inline pms */
            if (false === $isInline
                || (array_key_exists('HTML_ANSWER', $params)
                    || 0 < strlen(
                        $payment->getAdditionalInformation('HTML_ANSWER')
                    ))
            ) {
                $payment->addTransaction("authorization", null, true, $this->__("Process outgoing transaction"));
            }
            $payment->setLastTransId($params['PAYID']);
        }

        /* Ingenico ePayments sends parameter HTML_ANSWER to trigger 3D secure redirection */
        if (isset($params['HTML_ANSWER']) && ('ops_cc' == $code)) {
            $payment->setAdditionalInformation('HTML_ANSWER', $params['HTML_ANSWER']);
            $payment->setIsTransactionPending(true);
        }

        $payment->setAdditionalInformation('paymentId', $params['PAYID']);
        $payment->setAdditionalInformation('status', $params['STATUS']);
        if (array_key_exists('ACCEPTANCE', $params) && 0 < strlen(trim($params['ACCEPTANCE']))) {
            $payment->setAdditionalInformation('acceptance', $params['ACCEPTANCE']);
        }
        if (array_key_exists('BRAND', $params) && ('ops_cc' == $code) && 0 < strlen(trim($params['BRAND']))) {
            $payment->setAdditionalInformation('CC_BRAND', $params['BRAND']);
        }
        if (false === $isInline || array_key_exists('HTML_ANSWER', $params)) {
            $payment->setIsTransactionClosed(true);
        }
        $payment->setDataChanges(true);
        $payment->save();
    }

    /**
     * add fraud detection of Ingenico ePayments to additional payment data
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array                          $params
     */
    protected function setFraudDetectionParameters($payment, $params)
    {
        $params = array_change_key_case($params, CASE_UPPER);
        if (array_key_exists('SCORING', $params)) {
            $payment->setAdditionalInformation('scoring', $params['SCORING']);
        }
        if (array_key_exists('SCO_CATEGORY', $params)) {
            $payment->setAdditionalInformation('scoringCategory', $params['SCO_CATEGORY']);
        }
        $additionalScoringData = array();
        foreach ($this->getConfig()->getAdditionalScoringKeys() as $key) {
            if (array_key_exists($key, $params)) {
                if (false === mb_detect_encoding($params[$key], 'UTF-8', true)) {
                    $additionalScoringData[$key] = utf8_encode($params[$key]);
                } else {
                    $additionalScoringData[$key] = $params[$key];
                }
            }
        }
        $payment->setAdditionalInformation('additionalScoringData', $additionalScoringData);
    }





    /**
     * Get Payment Exception Message
     *
     * @param $ops_status
     * @return string
     */
    protected function getPaymentExceptionMessage($ops_status)
    {
        $exceptionMessage = '';
        switch ($ops_status) {
            case Netresearch_OPS_Model_Status::PAYMENT_UNCERTAIN :
                $exceptionMessage = Mage::helper('ops')->__(
                    'A technical problem arose during payment process, giving unpredictable result. Ingenico ePayments status: %s.',
                    Mage::helper('ops')->getStatusText($ops_status)
                );
                break;
            default:
                $exceptionMessage = Mage::helper('ops')->__(
                    'An unknown exception was thrown in the payment process. Ingenico ePayments status: %s.',
                    Mage::helper('ops')->getStatusText($ops_status)
                );
        }

        return $exceptionMessage;
    }


    /**
     * send invoice to customer if that was configured by the merchant
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice Invoice to be sent
     *
     * @return void
     */
    public function sendInvoiceToCustomer(Mage_Sales_Model_Order_Invoice $invoice)
    {
        if (false == $invoice->getEmailSent()
            && $this->getConfig()->getSendInvoice()
        ) {
            $invoice->sendEmail(true);
        }
    }

    /**
     * Process Configured Payment Actions: Authorized, Default operation
     * just place order
     *
     * @param Mage_Sales_Model_Order $order  Order
     * @param array                  $params Request params
     */
    protected function _processAuthorize($order, $params)
    {
        $status = $params['STATUS'];
        if ($status == Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT) {
            $order->setState(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage::helper('ops')->__(
                    'Waiting for payment. Ingenico ePayments status: %s.', Mage::helper('ops')->getStatusText($status)
                )
            );

            // send new order mail for bank transfer, since it is 'successfully' authorized at this point
            if ($order->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_BankTransfer
                && $order->getEmailSent() != 1
            ) {
                $order->sendNewOrderEmail();
            }
        } elseif ($status == Netresearch_OPS_Model_Status::AUTHORIZATION_WAITING) {
            $order->setState(
                Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage::helper('ops')->__(
                    'Authorization uncertain. Ingenico ePayments status: %s.', Mage::helper('ops')->getStatusText($status)
                )
            );
        } else {
            // for 3DS payments the order has to be retrieved from the payment review step
            if ($this->isInlinePayment($order->getPayment())
                && 0 < strlen(trim($order->getPayment()->getAdditionalInformation('HTML_ANSWER')))
                && $order->getPayment()->getAdditionalInformation('status') == Netresearch_OPS_Model_Status::AUTHORIZED
            ) {

                $order->getPayment()->setIsTransactionApproved(true)->registerPaymentReviewAction(
                    Mage_Sales_Model_Order_Payment::REVIEW_ACTION_UPDATE, true
                )->save();
            }
            if ($this->isRedirectPaymentMethod($order) === true
                && $order->getEmailSent() != 1
            ) {
                $order->sendNewOrderEmail();
            }

            if (!$this->isPaypalSpecialStatus($order->getPayment()->getMethodInstance(), $status)) {

                $payId = $params['PAYID'];
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage_Sales_Model_Order::STATE_PROCESSING,
                    Mage::helper('ops')->__(
                        'Processed by Ingenico ePayments. Payment ID: %s. Ingenico ePayments status: %s.', $payId,
                        Mage::helper('ops')->getStatusText($status)
                    )
                );
            }
        }
        $order->save();
    }

    /**
     * Special status handling for Paypal and status 91
     *
     * @param $pm
     * @param $status
     *
     * @return bool
     */
    protected function isPaypalSpecialStatus($pm, $status)
    {
        return $pm instanceof Netresearch_OPS_Model_Payment_Paypal
        && $status == Netresearch_OPS_Model_Status::PAYMENT_PROCESSING;
    }

    /**
     * Fetches transaction with given transaction id
     *
     * @param string $transactionId
     *
     * @return mixed Mage_Sales_Model_Order_Payment_Transaction | boolean
     */
    public function getTransactionByTransactionId($transactionId)
    {
        if (!$transactionId) {
            return false;
        }
        $transaction = Mage::getModel('sales/order_payment_transaction')
            ->getCollection()
            ->addAttributeToFilter('txn_id', $transactionId)
            ->getLastItem();
        if (null === $transaction->getId()) {
            return false;
        }
        $transaction->getOrderPaymentObject();

        return $transaction;
    }

    /**
     * refill cart
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return void
     */
    public function refillCart($order)
    {
        // add items
        $cart = Mage::getSingleton('checkout/cart');

        if (0 < $cart->getQuote()->getItemsCollection()->getSize()) {
            //cart is not empty, so refilling it is not required
            return;
        }
        foreach ($order->getItemsCollection() as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        $cart->save();

        // add coupon code
        $coupon = $order->getCouponCode();
        $session = Mage::getSingleton('checkout/session');
        if (null != $coupon) {
            $session->getQuote()->setCouponCode($coupon)->save();
        }
    }

    /**
     * Save OPS Status to Payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array                          $params OPS-Response
     *
     * @return void
     */
    public function saveOpsStatusToPayment(Mage_Sales_Model_Order_Payment $payment, $params)
    {
        $payment
            ->setAdditionalInformation('status', $params['STATUS'])
            ->save();
    }

    /**
     * Check is payment method is a redirect method
     *
     * @param $order
     * @return bool
     */
    protected function isRedirectPaymentMethod($order)
    {
        $result = false;
        $method = $order->getPayment()->getMethodInstance();
        if ($method
            && $method->getOrderPlaceRedirectUrl() != '' // Magento returns ''
            && $method->getOrderPlaceRedirectUrl() !== false // Ops returns false
        ) {
            $result = true;
        }

        return $result;
    }

    public function getQuote()
    {
        return $this->_getCheckout()->getQuote();
    }

    /**
     * sets the state to pending payment if neccessary (order is in state new)
     * and adds a comment to status history
     *
     * @param $order - the order
     */
    public function handleUnknownStatus($order)
    {
        if ($order instanceof Mage_Sales_Model_Order) {
            $message = Mage::helper('ops')->__(
                'Unknown Ingenico ePayments state for this order. Please check Ingenico ePayments backend for this order.'
            );
            if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    $message
                );
            } else {
                $order->addStatusHistoryComment($message);
            }
            $order->save();
        }
    }

    /**
     * returns the base grand total from either a quote or an order
     *
     * @param $salesObject
     *
     * @return double the base amount of the order
     * @throws Exception if $salesObject is not a quote or an order
     */
    public function getBaseGrandTotalFromSalesObject($salesObject)
    {
        if (!($salesObject instanceof Mage_Sales_Model_Order || $salesObject instanceof Mage_Sales_Model_Quote)) {
            Mage::throwException('$salesObject is not a quote or an order instance');
        }

        return $salesObject->getBaseGrandTotal();
    }


    /**
     * Save the last used refund operation code to payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string                         $operationCode
     *
     * @return void
     */
    public function saveOpsRefundOperationCodeToPayment(Mage_Sales_Model_Order_Payment $payment, $operationCode)
    {
        if (in_array(
            strtoupper(trim($operationCode)),
            array(
                Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL,
                Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL
            )
        )
        ) {
            Mage::helper('ops/data')->log(
                sprintf(
                    "set last refund operation '%s' code to payment for order '%s'",
                    $operationCode,
                    $payment->getOrder()->getIncrementId()
                )
            );
            $payment
                ->setAdditionalInformation('lastRefundOperationCode', $operationCode)
                ->save();
        }
    }

    /**
     * sets the canRefund information depending on the last refund operation code
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    public function setCanRefundToPayment(Mage_Sales_Model_Order_Payment $payment)
    {
        $refundOperationCode = $payment->getAdditionalInformation('lastRefundOperationCode');
        if (in_array(
            strtoupper(trim($refundOperationCode)),
            array(
                Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL,
                Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL
            )
        )
        ) {
            /*
             * a further refund is possible if the transaction remains open, that means either the merchant
             * did not close the transaction or the refunded amount is less than the orders amount
             */
            $canRefund = ($refundOperationCode == Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL);
            Mage::helper('ops/data')->log(
                sprintf(
                    "set canRefund to '%s' for payment of order '%s'",
                    var_export($canRefund, true),
                    $payment->getOrder()->getIncrementId()
                )
            );
            $payment
                ->setAdditionalInformation('canRefund', $canRefund)
                ->save();
        }
    }

    /**
     * determine whether the payment supports only authorize or not
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return true . if so, false otherwise
     */
    protected function forceAuthorize(Mage_Sales_Model_Order $order)
    {
        return ($order->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Kwixo_Abstract);
    }


    /**
     * add ops_cc payment to checkout methods if quote total is zero and zero amount checkout is activated
     *
     * @param Mage_Payment_Block_Form_Container $block
     *
     * @return $this
     */
    public function addCCForZeroAmountCheckout(Mage_Payment_Block_Form_Container $block)
    {
        $methods = $block->getMethods();
        if (false === $this->checkIfCCisInCheckoutMethods($methods)) {
            $ccPayment = Mage::getModel('ops/payment_cc');
            if ($ccPayment->getFeatureModel()->isCCAndZeroAmountAuthAllowed($ccPayment, $block->getQuote())) {
                $ccPayment->setInfoInstance($block->getQuote()->getPayment());
                $methods[] = $ccPayment;
                $block->setData('methods', $methods);
            }
        }

        return $this;
    }


    /**
     * check if ops_cc is in payment methods array
     *
     * @param $methods
     *
     * @return array
     */
    protected function checkIfCCisInCheckoutMethods($methods)
    {
        $result = false;
        foreach ($methods as $method) {
            if ($method->getCode() == 'ops_cc') {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * checks if the payment method can use order's increment id as merchant's reference
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    public function isInlinePaymentWithOrderId(Mage_Payment_Model_Info $payment)
    {
        return $this->isInlinePayment($payment) && $this->getConfig()->getInlineOrderReference() == Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID;
    }

    /**
     * checks if the payment method can pbe processed via direct link
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    public function isInlinePayment(Mage_Payment_Model_Info $payment)
    {
        $result = false;
        $methodInstance = $payment->getMethodInstance();
        if ($methodInstance instanceof Netresearch_OPS_Model_Payment_DirectDebit
            || ($methodInstance instanceof Netresearch_OPS_Model_Payment_Cc
                && ($methodInstance->hasBrandAliasInterfaceSupport($payment)
                    || Mage::helper('ops/data')->isAdminSession()))
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * checks if the inline payment can use quote id as merchant's reference
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    public function isInlinePaymentWithQuoteId(Mage_Payment_Model_Info $payment)
    {
        return $this->isInlinePayment($payment)
        && (0 === strlen(
            trim($payment->getMethodInstance()->getConfigPaymentAction())
        ));
    }

    /**
     * sets the invoices of an order to paid
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return Netresearch_OPS_Helper_Payment
     */
    public function setInvoicesToPaid($order)
    {
        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
        }
        $order->getInvoiceCollection()->save();

        return $this;
    }

    /**
     * cancel all invoices for a given order
     *
     * @param $order
     *
     * @return Netresearch_OPS_Helper_Payment
     * @throws Exception
     */
    public function cancelInvoices($order)
    {
        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        foreach ($order->getInvoiceCollection() as $invoice) {
            $invoice->cancel();
            $invoice->save();
        }

        return $this;
    }

    /**
     * Returns if the current payment status is an invalid one, namely if it is one of the following:
     * Netresearch_OPS_Model_Payment_Abstract::INVALID_INCOMPLETE,
     * Netresearch_OPS_Model_Payment_Abstract::CANCELED_BY_CUSTOMER,
     * Netresearch_OPS_Model_Payment_Abstract::AUTHORISATION_DECLINED,
     *
     * @param $status
     *
     * @return bool
     */
    public function isPaymentInvalid($status)
    {
        return in_array(
            $status,
            array(
                Netresearch_OPS_Model_Status::INVALID_INCOMPLETE,
                Netresearch_OPS_Model_Status::CANCELED_BY_CUSTOMER,
                Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED,
            )
        );
    }

    /**
     * @param $paymentCode
     *
     * @return string
     */
    public function getPaymentDefaultLogo($paymentCode)
    {
        return Mage::getSingleton('core/design_package')->getSkinUrl(
            'images/ops/logos/' . $paymentCode . '.png',
            array('_area' => 'frontend')
        );
    }

}
