<?php

/**
 * @author      Paul Siedler <paul.siedler@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Netresearch_OPS_Model_Backend_Operation_Parameter_Abstract
    implements Netresearch_OPS_Model_Backend_Operation_Parameter_Interface
{
    protected $requestParams = array();

    protected $opsConfig = null;
    protected $dataHelper = null;

    protected $additionalParamsModel = null;

    /**
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     * @param                                        $payment
     * @param                                        $amount
     *
     * @return array
     */
    public function getRequestParams(
        Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod,
        Varien_Object $payment,
        $amount
    ) 
    {
        $this->getBaseParams($opsPaymentMethod, $payment, $amount);
        $this->addPmSpecificParams($opsPaymentMethod, $payment, $amount);

        return $this->requestParams;
    }

    /**
     * retrieves the basic parameters for a capture call
     *
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     * @param Varien_Object                          $payment
     * @param                                        $amount
     *
     * @return $this
     */
    protected function getBaseParams(
        Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod,
        Varien_Object $payment,
        $amount
    ) 
    {
        $this->requestParams['AMOUNT']    = $this->getDataHelper()->getAmount($amount);
        $this->requestParams['PAYID']     = $payment->getAdditionalInformation('paymentId');
        $this->requestParams['OPERATION'] = $this->getOrderHelper()->determineOperationCode($payment, $amount);
        $this->requestParams['CURRENCY']  = Mage::app()->getStore($payment->getOrder()->getStoreId())
                                               ->getBaseCurrencyCode();

        return $this;
    }

    /**
     * retrieves ops config model
     *
     * @return Netresearch_OPS_Model_Config
     */
    protected function getOpsConfig()
    {
        if (null === $this->opsConfig) {
            $this->opsConfig = Mage::getModel('ops/config');
        }

        return $this->opsConfig;
    }

    /**
     * if we have to add payment specific paramters to our request, we'll set them here
     *
     * @param $opsPaymentMethod
     * @param $payment
     * @param $amount
     *
     * @return $this
     */
    protected function addPmSpecificParams(Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod,
        Varien_Object $payment, $amount
    ) 
    {
        if ($this->isPmRequiringAdditionalParams($opsPaymentMethod)) {
            $this->setAdditionalParamsModelFor($opsPaymentMethod);
            if ($this->additionalParamsModel instanceof
                Netresearch_OPS_Model_Backend_Operation_Parameter_Additional_Interface
            ) {
                $params = $this->additionalParamsModel->extractAdditionalParams($payment);
                $this->requestParams = array_merge($this->requestParams, $params);
            }
        }

        return $this;
    }

    protected function isPmRequiringAdditionalParams(Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod)
    {
        return false;
    }

    protected function setAdditionalParamsModelFor(Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod)
    {
        $this->additionalParamsModel = null;
    }

    /**
     * retrieves the data helper
     *
     * @return Netresearch_OPS_Helper_Data|null
     */
    protected function getDataHelper()
    {
        if (null == $this->dataHelper) {
            $this->dataHelper = Mage::helper('ops/data');
        }

        return $this->dataHelper;
    }

    /**
     * Returns the order helper for the corresponding transaction type
     *
     * @return Netresearch_OPS_Helper_Order_Abstract
     */
    public abstract function getOrderHelper();

}