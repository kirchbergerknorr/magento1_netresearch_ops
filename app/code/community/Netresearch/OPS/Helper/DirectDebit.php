<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG
 *          (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Helper_DirectDebit extends Netresearch_OPS_Helper_Payment_DirectLink_Request
{

    protected $dataHelper = null;

    protected $quoteHelper = null;

    protected $orderHelper = null;

    protected $customerHelper = null;


    /**
     * sets the data helper
     *
     * @param Netresearch_OPS_Helper_Data $dataHelper
     */
    public function setDataHelper(Netresearch_OPS_Helper_Data $dataHelper)
    {
        $this->dataHelper = Mage::helper('ops/data');
    }

    /**
     * gets the data helper
     *
     * @return Netresearch_OPS_Helper_Data
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
     * @return Mage_Core_Helper_Abstract
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
     * @param Mage_Core_Helper_Abstract $customerHelper
     */
    public function setCustomerHelper(Mage_Core_Helper_Abstract $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * gets the customer helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getCustomerHelper()
    {
        if (null === $this->customerHelper) {
            $this->customerHelper = Mage::helper('customer/data');
        }

        return $this->customerHelper;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param array $requestParams
     * @return Netresearch_OPS_Helper_DirectDebit
     */
    public function handleAdminPayment(Mage_Sales_Model_Quote $quote, $requestParams)
    {
        return $this;
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    protected function getPaymentSpecificParams(Mage_Sales_Model_Quote $quote)
    {
        $alias = $quote->getPayment()->getAdditionalInformation('alias');
        $saveAlias  = Mage::getModel('ops/alias')->load($alias, 'alias')->getId();

        $paymentMethod = 'Direct Debits ' . $quote->getPayment()->getAdditionalInformation('country_id');
        $params = array (
            'ALIAS' => $alias,
            'ALIASPERSISTEDAFTERUSE' => $saveAlias ? 'Y' : 'N',
            'PM' => $paymentMethod,
            'BRAND' => $paymentMethod
        );

        return $params;
    }
}
    