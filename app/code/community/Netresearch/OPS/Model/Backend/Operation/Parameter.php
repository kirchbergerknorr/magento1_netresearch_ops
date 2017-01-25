<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Model_Backend_Operation_Parameter
{
    protected $parameterModel = null;

    protected $dataHelper = null;

    /**
     * retrieves the neccessary parameter for the given operation
     *
     * @param                                        $operation
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     * @param Varien_Object                          $payment
     * @param                                        $amount
     *
     * @return array
     */
    public function getParameterFor(
        $operation,
        Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod,
        Varien_Object $payment,
        $amount
    ) 
    {
        return $this->getParameterModel($operation)->getRequestParams($opsPaymentMethod, $payment, $amount);
    }

    /**
     * retrieves the parameter model for the given operation
     *
     * @param $operation - the operation we need the parameters for
     *
     * @throws Mage_Core_Exception - in case the operation is not supported
     * @return Netresearch_OPS_Model_Backend_Operation_Parameter_Interface - the model for the parameters of the
     *      operation
     */
    protected function getParameterModel($operation)
    {
        if ($operation === Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_TRANSACTION_TYPE) {
            return Mage::getModel('ops/backend_operation_capture_parameter');
        }
        if ($operation === Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_TRANSACTION_TYPE) {
            return Mage::getModel('ops/backend_operation_refund_parameter');
        }

        Mage::throwException($this->getDataHelper()->__('operation %s not supported', $operation));
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
} 