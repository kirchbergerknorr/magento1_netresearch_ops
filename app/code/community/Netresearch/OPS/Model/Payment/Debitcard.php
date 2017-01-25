<?php

/**
 * Netresearch_OPS_Model_Payment_Debitcard
 *
 * @package
 * @copyright 2016 Netresearch
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_Debitcard extends Netresearch_OPS_Model_Payment_Cc
{

    /** payment code */
    protected $_code = 'ops_dc';


    /**
     * @param null $payment
     * @return string
     */
    public function getOpsCode($payment = null)
    {
        return 'CreditCard';
    }

    /**
     * @inheritdoc
     */
    public function getMethodDependendFormFields($order, $requestParams = null)
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);
        if ($this->getConfig()->getCreditDebitSplit($order->getStoreId())) {
            $formFields['CREDITDEBIT'] = "D";
        }

        return $formFields;
    }

    /**
     * @return Netresearch_OPS_Helper_Debitcard
     */
    public function getRequestParamsHelper()
    {
        if (null === $this->requestParamsHelper) {
            $this->requestParamsHelper = Mage::helper('ops/debitcard');
        }

        return $this->requestParamsHelper;
    }


}

