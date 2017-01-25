<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Netresearch_OPS_Model_Payment_DirectLink extends Netresearch_OPS_Model_Payment_Abstract
{
    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** Check if we can capture directly from the backend */
    protected $_canUseInternal = true;

    protected $config = null;

    protected $directLinkHelper = null;

    protected $paymentHelper = null;

    protected $quoteHelper = null;

    protected $requestParamsHelper = null;

    protected $validationFactory = null;

    protected $dataHelper = null;

    protected $_isInitializeNeeded = false;

    /**
     * @param Netresearch_OPS_Helper_Payment_DirectLink_RequestInterface $requestParamsHelper
     */
    public function setRequestParamsHelper($requestParamsHelper)
    {
        $this->requestParamsHelper = $requestParamsHelper;
    }


    /**
     * sets the quote helper
     *
     * @param Netresearch_OPS_Helper_Quote $quoteHelper
     */
    public function setQuoteHelper(Netresearch_OPS_Helper_Quote $quoteHelper)
    {
        $this->quoteHelper = $quoteHelper;
    }

    /**
     * gets the quote helper
     *
     * @return Netresearch_OPS_Helper_Quote
     */
    public function getQuoteHelper()
    {
        if (null === $this->quoteHelper) {
            $this->quoteHelper = Mage::helper('ops/quote');
        }

        return $this->quoteHelper;
    }


    /**
     * @param Netresearch_OPS_Helper_Directlink $directLinkHelper
     */
    public function setDirectLinkHelper($directLinkHelper)
    {
        $this->directLinkHelper = $directLinkHelper;
    }

    /**
     * @return Netresearch_OPS_Helper_Directlink
     */
    public function getDirectLinkHelper()
    {
        if (null === $this->directLinkHelper) {
            $this->directLinkHelper = MAge::helper('ops/directlink');
        }

        return $this->directLinkHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Payment $paymentHelper
     */
    public function setPaymentHelper($paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
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
     * @param null $config
     */
    public function setConfig(Netresearch_OPS_Model_Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Netresearch_OPS_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->config = Mage::getModel('ops/config');
        }

        return $this->config;
    }

    /**
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @return Mage_Payment_Model_Abstract|void
     */

    public function authorize(Varien_Object $payment, $amount)
    {
        if ($this->isInlinePayment($payment)
            && Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE == $this->getConfigPaymentAction()
        ) {
            $order = $payment->getOrder();
            $quote = $this->getQuoteHelper()->getQuote();
            $this->confirmPayment($order, $quote, $payment);
        }
    }

    /**
     * Saves the payment model and runs the request to Ingenico ePaymentss webservice
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Quote $quote
     * @param Varien_Object          $payment
     *
     * @throws Mage_Core_Exception
     */

    protected function confirmPayment(Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote $quote,
        Varien_Object $payment
    ) 
    {
        $this->handleAdminPayment($quote);
        $requestParams = $this->getRequestParamsHelper()->getDirectLinkRequestParams($quote, $order, $payment);
        $this->invokeRequestParamValidation($requestParams);
        $this->performPreDirectLinkCallActions($quote, $order);
        $response = $this->getDirectLinkHelper()->performDirectLinkRequest(
            $quote, $requestParams, $quote->getStoreId()
        );
        if ($response) {
            $handler = Mage::getModel('ops/response_handler');
            $handler->processResponse($response, $this, false);
            $this->performPostDirectLinkCallAction($quote, $order);

        } else {
            $this->getPaymentHelper()->handleUnknownStatus($order);
        }
    }

    /**
     * Handles backend payments on Magento side
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return Netresearch_OPS_Model_Payment_DirectLink
     */
    abstract protected function handleAdminPayment(Mage_Sales_Model_Quote $quote);

    /**
     * @return Netresearch_OPS_Helper_Payment_DirectLink_RequestInterface
     */
    abstract protected function getRequestParamsHelper();


    /**
     * Perform necessary preparation before request to Ingenico ePayments is sent
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Varien_Object          $payment
     * @param array                  $requestParams
     *
     * @return Netresearch_OPS_Model_Payment_DirectLink
     */
    abstract protected function performPreDirectLinkCallActions(Mage_Sales_Model_Quote $quote, Varien_Object $payment,
        $requestParams = array()
    );

    /**
     * Perform necessary work after the Directlink Request was sent and an response was received and processed
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     *
     * @return Netresearch_OPS_Model_Payment_DirectLink
     */
    abstract protected function performPostDirectLinkCallAction(Mage_Sales_Model_Quote $quote,
        Mage_Sales_Model_Order $order
    );


    /**
     * performs direct link request either for inline payments and
     * direct sale mode or the normal maintenance call (invoice)
     *
     * @override
     *
     * @param Varien_Object $payment
     * @param float         $amount
     *
     * @return Mage_Payment_Model_Abstract|void
     */
    public function capture(Varien_Object $payment, $amount)
    {
        /**
         * process direct sale inline payments (initial request)
         */
        if (Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE == $this->getConfigPaymentAction()
            && $this->getPaymentHelper()->isInlinePayment($payment)
        ) {
            $order = $payment->getOrder();
            $quote = $this->getQuoteHelper()->getQuote();
            $this->confirmPayment($order, $quote, $payment);
        } /**
         * invoice request authorize mode if the payment was placed on Ingenico ePayments side
         */
        elseif (0 < strlen(trim($payment->getAdditionalInformation('paymentId')))) {
            parent::capture($payment, $amount);
        }
    }


    /**
     * checks if the selected payment supports inline mode
     *
     * @param $payment - the payment to check
     *
     * @return bool - true if it's support inline mode, false otherwise
     */
    protected function isInlinePayment($payment)
    {
        $result = false;

        $methodInstance = $payment->getMethodInstance();
        if ((
                $methodInstance instanceof Netresearch_OPS_Model_Payment_Cc
                && $methodInstance->hasBrandAliasInterfaceSupport($payment)
                || $this->getDataHelper()->isAdminSession()
            )
            || $methodInstance instanceof Netresearch_OPS_Model_Payment_DirectDebit
        ) {
            $result = true;
        }

        return $result;
    }


    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Validate checkout request parameters
     *
     * @param $requestParams
     *
     * @throws Mage_Core_Exception
     * @return Netresearch_OPS_Model_Payment_DirectLink
     */
    protected function invokeRequestParamValidation($requestParams)
    {
        $validator = $this->getValidationFactory()->getValidatorFor(
            Netresearch_OPS_Model_Validator_Parameter_Factory::TYPE_REQUEST_PARAMS_VALIDATION
        );
        if (false == $validator->isValid($requestParams)) {
            $this->getOnepage()->getCheckout()->setGotoSection('payment');
            Mage::throwException(
                $this->getHelper()->__('The data you have provided can not be processed by Ingenico ePayments')
            );
        }

        return $this;
    }

    /**
     * @return Netresearch_OPS_Model_Validator_Parameter_Factory
     */
    public function getValidationFactory()
    {
        if (null === $this->validationFactory) {
            $this->validationFactory = Mage::getModel('ops/validator_parameter_factory');
        }

        return $this->validationFactory;
    }

    /**
     * sets the used validation factory
     *
     * @param Netresearch_OPS_Model_Validator_Parameter_Factory $validationFactory
     */
    public function setValidationFactory(Netresearch_OPS_Model_Validator_Parameter_Factory $validationFactory)
    {
        $this->validationFactory = $validationFactory;
    }

    /**
     * @param Netresearch_OPS_Helper_Data $dataHelper
     */
    public function setDataHelper(Netresearch_OPS_Helper_Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return Netresearch_OPS_Helper_Data
     */
    public function getDataHelper()
    {
        if (null === $this->dataHelper) {
            $this->dataHelper = Mage::helper('ops/data');
        }

        return $this->dataHelper;
    }

}