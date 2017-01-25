<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Model_Backend_Operation_Capture_Parameter
    extends Netresearch_OPS_Model_Backend_Operation_Parameter_Abstract
{
    /**
     * checks whether we need to retrieve additional parameter for the capture request or not
     *
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     *
     * @return bool - true if we need to retrieve any additional parameters, false otherwise
     */
    protected function isPmRequiringAdditionalParams(Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod)
    {
        $opsPaymentMethodClass = get_class($opsPaymentMethod);
        $opsPmsRequiringSpecialParams = $this->getOpsConfig()
                                             ->getMethodsRequiringAdditionalParametersFor(
                                                 Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_TRANSACTION_TYPE
                                             );

        return (in_array($opsPaymentMethodClass, array_values($opsPmsRequiringSpecialParams)));
    }

    /**
     * sets the model which retrieves the additional params
     *
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     */
    protected function setAdditionalParamsModelFor(Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod)
    {
        if ($opsPaymentMethod instanceof Netresearch_OPS_Model_Payment_OpenInvoice_Abstract) {
            $this->additionalParamsModel = Mage::getModel('ops/backend_operation_capture_additional_openInvoiceNl');
        }
    }

    /**
     * Returns the order helper for the corresponding transaction type
     *
     * @return Netresearch_OPS_Helper_Order_Abstract
     */
    public function getOrderHelper()
    {
        return Mage::helper('ops/order_capture');
    }
} 