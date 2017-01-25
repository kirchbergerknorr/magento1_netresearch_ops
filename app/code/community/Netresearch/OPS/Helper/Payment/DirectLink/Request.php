<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */



abstract class Netresearch_OPS_Helper_Payment_DirectLink_Request
    implements Netresearch_OPS_Helper_Payment_DirectLink_RequestInterface
{

    protected $dataHelper = null;

    protected $quoteHelper = null;

    protected $orderHelper = null;

    protected $customerHelper = null;

    protected $validator = null;

    protected $requestHelper = null;

    protected $config = null;

    /**
     * @param null $config
     */
    public function setConfig($config)
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

    public function setRequestHelper(Netresearch_OPS_Helper_Payment_Request $requestHelper)
    {
        $this->requestHelper = $requestHelper;
    }

    /**
     * @return Netresearch_OPS_Helper_Payment_Request
     */
    public function getRequestHelper()
    {
        if (null === $this->requestHelper) {
            $this->requestHelper = Mage::helper('ops/payment_request');
            $this->requestHelper->setConfig($this->getConfig());
        }

        return $this->requestHelper;
    }

    /**
     * sets the data helper
     *
     * @param Netresearch_OPS_Helper_Data $dataHelper
     */
    public function setDataHelper(Netresearch_OPS_Helper_Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * gets the data helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getDataHelper()
    {
        if (null === $this->dataHelper) {
            $this->dataHelper = Mage::helper('ops/data');
        }

        return $this->dataHelper;
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
     * @return Mage_Core_Helper_Abstract
     */
    public function getQuoteHelper()
    {
        if (null === $this->quoteHelper) {
            $this->quoteHelper = Mage::helper('ops/quote');
        }

        return $this->quoteHelper;
    }

    /**
     * sets the order helper
     *
     * @param Netresearch_OPS_Helper_Order $orderHelper
     */
    public function setOrderHelper(Netresearch_OPS_Helper_Order $orderHelper)
    {
        $this->orderHelper = $orderHelper;
    }

    /**
     * gets the order helper
     *
     * @return Netresearch_OPS_Helper_Order
     */
    public function getOrderHelper()
    {
        if (null === $this->orderHelper) {
            $this->orderHelper = Mage::helper('ops/order');
        }

        return $this->orderHelper;
    }

    /**
     * sets the customer helper
     *
     * @param Mage_Customer_Helper_Data $customerHelper
     */
    public function setCustomerHelper(Mage_Core_Helper_Abstract $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * gets the customer helper
     *
     * @return Mage_Customer_Helper_Data
     */
    public function getCustomerHelper()
    {
        if (null === $this->customerHelper) {
            $this->customerHelper = Mage::helper('customer/data');
        }

        return $this->customerHelper;
    }


    /**
     * extracts the parameter for the direct link request from the quote,
     * order and, optionally from existing request params
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @param array $requestParams
     *
     * @return array - the parameters for the direct link request
     */
    public function getDirectLinkRequestParams(
        Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order, $requestParams = array())
    {
        $billingAddress  = $order->getBillingAddress();
        $shippingAddress = $this->getShippingAddress($order, $billingAddress);
        $requestParams = $this->getBaseRequestParams($quote, $order, $billingAddress);
        $requestParams = array_merge($requestParams, $this->getPaymentSpecificParams($quote));

        if ($this->getConfig()->canSubmitExtraParameter($quote->getStoreId())) {
            $shipToParams = $this->getRequestHelper()->extractShipToParameters($shippingAddress, $quote);
            $shipToParams = $this->decodeParamsForDirectLinkCall($shipToParams);
            $requestParams = array_merge($requestParams, $shipToParams);
        }

        $requestParams = $this->addCustomerSpecificParams($requestParams);
        $requestParams = $this->addParamsForAdminPayments($requestParams);

        return $requestParams;
    }

    /**
     * specail handling like validation and so on for admin payments
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param array                       $requestParams
     *
     * @return mixed
     */
    abstract public function handleAdminPayment(Mage_Sales_Model_Quote $quote, $requestParams);

    /**
     * extracts payment specific payment parameters
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return array
     */
    abstract protected function getPaymentSpecificParams(Mage_Sales_Model_Quote $quote);

    /**
     * gets the shipping address if there is one, otherwise the billing address is used as shipping address
     *
     * @param $order
     * @param $billingAddress
     *
     * @return mixed
     */
    protected function getShippingAddress(Mage_Sales_Model_Order $order, $billingAddress)
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress || false === $shippingAddress) {
            $shippingAddress = $billingAddress;
        }
        return $shippingAddress;
    }

    /**
     * utf8 decode for direct link calls
     *
     * @param array $requestParams
     *
     * @return array - the decoded array
     */
    protected function decodeParamsForDirectLinkCall(array $requestParams)
    {
        foreach ($requestParams as $key => $value) {
            $requestParams[$key] = utf8_decode($value);
        }
        return $requestParams;
    }

    /**
     * @param $requestParams
     *
     * @return mixed
     */
    protected function addCustomerSpecificParams($requestParams)
    {
        if ($this->getCustomerHelper()->isLoggedIn()) {
            $requestParams['CUID'] = $this->getCustomerHelper()->getCustomer()->getId();
        }
        return $requestParams;
    }

    /**
     * @param $requestParams
     *
     * @return mixed
     */
    protected function addParamsForAdminPayments($requestParams)
    {
        if ($this->getDataHelper()->isAdminSession()) {
            $requestParams['ECI'] = Netresearch_OPS_Model_Eci_Values::MANUALLY_KEYED_FROM_MOTO;
            $requestParams['REMOTE_ADDR'] = 'NONE';
        }

        return $requestParams;
    }

    /**
     * @param $quote
     * @param $order
     * @param $billingAddress
     *
     * @return array
     */
    protected function getBaseRequestParams($quote, $order, $billingAddress)
    {
        $merchantRef = $this->getOrderHelper()->getOpsOrderId($order, $this->canUseOrderId($quote->getPayment()));
        $requestParams = array(
            'AMOUNT'                        => $this->getDataHelper()->getAmount($quote->getBaseGrandTotal()),
            'CURRENCY'                      => $this->getQuoteHelper()->getQuoteCurrency($quote),
            'OPERATION'                     => $this->getQuoteHelper()->getPaymentAction($quote),
            'ORDERID'                       => $merchantRef,
            'ORIG'                          => $this->getDataHelper()->getModuleVersionString(),
            'EMAIL'                         => $order->getCustomerEmail(),
            'REMOTE_ADDR'                   => $quote->getRemoteIp(),
            'RTIMEOUT'                      => $this->getConfig()->getTransActionTimeout()
        );

        $ownerParams = $this->getOwnerParams($quote, $billingAddress, $requestParams);
        $requestParams = array_merge($requestParams, $ownerParams);
        $requestParams['ADDMATCH']       = $this->getOrderHelper()->checkIfAddressesAreSame($order);

        return $requestParams;
    }

    /**
     * @param $quote
     * @param $billingAddress
     * @param $requestParams
     *
     * @return array
     */
    protected function getOwnerParams($quote, $billingAddress, $requestParams)
    {
        $ownerParams = $this->getRequestHelper()->getOwnerParams($billingAddress, $quote);
        if (array_key_exists('OWNERADDRESS', $ownerParams) && array_key_exists('OWNERTOWN', $ownerParams)) {
            $ownerAddrParams = $this->decodeParamsForDirectLinkCall(
                array('OWNERADDRESS' => $ownerParams['OWNERADDRESS'], 'OWNERTOWN' => $ownerParams['OWNERTOWN'])
            );
            $ownerParams = array_merge($ownerParams, $ownerAddrParams);
        }

        return $ownerParams;
    }

    /**
     * @return bool
     */
    public function canUseOrderId(Varien_Object $payment)
    {
        $methodInstance = $payment->getMethodInstance();
        return
            $this->getConfig()->getInlineOrderReference() == Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID
            && $methodInstance instanceof Netresearch_OPS_Model_Payment_DirectLink;
    }


} 