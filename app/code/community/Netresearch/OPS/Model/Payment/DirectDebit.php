<?php
/**
 * Netresearch_OPS_Model_Payment_DirectDebit
 * 
 * @package   
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_DirectDebit
    extends Netresearch_OPS_Model_Payment_DirectLink
{
    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /* define a specific form block */
    protected $_formBlockType = 'ops/form_directDebit';

    /** payment code */
    protected $_code = 'ops_directDebit';

    public function getOrderPlaceRedirectUrl()
    {
        // Prevent redirect on direct debit payment
        return false; 
    }


    /**
     * @return Netresearch_OPS_Helper_DirectDebit
     */
    public function getRequestParamsHelper()
    {
        if (null === $this->requestParamsHelper) {
            $this->requestParamsHelper = Mage::helper('ops/directDebit');
        }

        return $this->requestParamsHelper;
    }

    protected function performPreDirectLinkCallActions(
        Mage_Sales_Model_Quote $quote,
        Varien_Object $payment, $requestParams = array()
    )
    {
        return $this;
    }

    protected function performPostDirectLinkCallAction(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order)
    {
        Mage::helper('ops/alias')->setAliasActive($quote, $order);

        return $this;
    }

    protected function handleAdminPayment(Mage_Sales_Model_Quote $quote)
    {
        return $this;
    }
}

