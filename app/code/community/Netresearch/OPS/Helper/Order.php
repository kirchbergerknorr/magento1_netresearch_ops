<?php

/**
 * Netresearch_OPS_Helper_Order
 *
 * @package
 * @copyright 2013 Netresearch
 * @author    Michael Lühr <michael.luehr@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Order extends Mage_Core_Helper_Abstract
{

    const DELIMITER = '#';

    /** @var $config Netresearch_OPS_Model_Config */
    protected $config = null;

    protected $statusMappingModel = null;

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

    protected $dataHelper = null;


    /**
     * return the config model
     *
     * @return Netresearch_OPS_Model_Config
     */
    protected function getConfig()
    {
        if (null === $this->config) {
            $this->config = Mage::getModel('ops/config');
        }

        return $this->config;
    }

    /**
     * generates the OPS order id in dependency to the config
     *
     * @param mixed $salesObject
     * @param bool  $useOrderIdIfPossible if false forces the usage of quoteid (for Kwixo pm etc.)
     *
     * @return string
     */
    public function getOpsOrderId($salesObject, $useOrderIdIfPossible = true)
    {
        $config = $this->getConfig();
        $devPrefix = $config->getConfigData('devprefix');
        if ($salesObject instanceof Mage_Sales_Model_Order) {
            /** @var $salesObject Mage_Sales_Model_Order */
            $orderRef = $salesObject->getQuoteId();
        } elseif ($salesObject instanceof Mage_Sales_Model_Quote) {
            /** @var $salesObject Mage_Sales_Model_Quote */
            $orderRef = $salesObject->getId();
        }

        if ($config->getOrderReference($salesObject->getStoreId())
            == Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID
            && $useOrderIdIfPossible === true
        ) {
            if ($salesObject instanceof Mage_Sales_Model_Quote) {
                $salesObject->reserveOrderId();
                $orderRef = self::DELIMITER . $salesObject->getReservedOrderId();
            } elseif ($salesObject instanceof Mage_Sales_Model_Order) {
                $orderRef = self::DELIMITER . $salesObject->getIncrementId();
            }

        }

        return $devPrefix . $orderRef;
    }

    /**
     * getting the order from opsOrderId which can either the quote id or the order increment id
     * in both cases the dev prefix is stripped, if neccessary
     *
     * @param $opsOrderId
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($opsOrderId)
    {
        $order = null;
        $fieldToFilter = 'quote_id';
        $devPrefix = $this->getConfig()->getConfigData('devprefix');
        if ($devPrefix == substr($opsOrderId, 0, strlen($devPrefix))) {
            $opsOrderId = substr($opsOrderId, strlen($devPrefix));
        }
        // opsOrderId was created from order increment id, use increment id for filtering
        if (0 === strpos($opsOrderId, self::DELIMITER)) {
            $opsOrderId = substr($opsOrderId, strlen(self::DELIMITER));
            $fieldToFilter = 'increment_id';
        }

        /* @var $order Mage_Sales_Model_Resource_Order_Collection */
        $order = Mage::getModel('sales/order')->getCollection()
                     ->addFieldToFilter($fieldToFilter, $opsOrderId)
            // filter for OPS payment methods
                     ->join(array('payment' => 'sales/order_payment'), 'main_table.entity_id=parent_id', 'method')
                     ->addFieldToFilter('method', array(array('like' => 'ops_%')))
            // sort by increment_id of order to get only the latest (relevant for quote id search)
                     ->addOrder('main_table.increment_id');

        return $order->getFirstItem();
    }

    /**
     * load and return the quote via the quoteId
     *
     * @param string $quoteId
     *
     * @return Mage_Model_Sales_Quote
     */
    public function getQuote($quoteId)
    {
        return Mage::getModel('sales/quote')->load($quoteId);
    }

    /**
     * check if billing is same as shipping address
     *
     * @param Mage_Model_Sales_Order $order
     *
     * @return int
     */
    public function checkIfAddressesAreSame(Mage_Sales_Model_Order $order)
    {
        $addMatch = 0;
        $billingAddressHash = null;
        $shippingAddressHash = null;
        if ($order->getBillingAddress() instanceof Mage_Customer_Model_Address_Abstract) {
            $billingAddressHash = Mage::helper('ops/alias')->generateAddressHash(
                $order->getBillingAddress()
            );
        }
        if ($order->getShippingAddress() instanceof Mage_Customer_Model_Address_Abstract) {
            $shippingAddressHash = Mage::helper('ops/alias')->generateAddressHash(
                $order->getShippingAddress()
            );
        }

        if ($billingAddressHash === $shippingAddressHash) {
            $addMatch = 1;
        }

        return $addMatch;
    }

}
