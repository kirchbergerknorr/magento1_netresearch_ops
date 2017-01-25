<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Netresearch_OPS_Helper_Payment_DirectLink_RequestInterface
{
    /**
     * sets the data helper
     *
     * @param Netresearch_OPS_Helper_Data $dataHelper
     */
    public function setDataHelper(Netresearch_OPS_Helper_Data $dataHelper);

    /**
     * @return Netresearch_OPS_Model_Config
     */
    public function getConfig();

    /**
     * sets the quote helper
     *
     * @param Netresearch_OPS_Helper_Quote $quoteHelper
     */
    public function setQuoteHelper(Netresearch_OPS_Helper_Quote $quoteHelper);

    /**
     * extracts the parameter for the direct link request from the quote, order and, optionally from existing request params
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @param array                  $requestParams
     *
     * @return array - the parameters for the direct link request
     */
    public function getDirectLinkRequestParams(
        Mage_Sales_Model_Quote $quote,
        Mage_Sales_Model_Order $order,
        $requestParams = array()
    );

    /**
     * @param null $config
     */
    public function setConfig($config);

    /**
     * sets the order helper
     *
     * @param Netresearch_OPS_Helper_Order $orderHelper
     */
    public function setOrderHelper(Netresearch_OPS_Helper_Order $orderHelper);

    /**
     * sets the customer helper
     *
     * @param Mage_Core_Helper_Abstract $customerHelper
     */
    public function setCustomerHelper(Mage_Core_Helper_Abstract $customerHelper);

    public function setRequestHelper(Netresearch_OPS_Helper_Payment_Request $requestHelper);

    /**
     * gets the customer helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getCustomerHelper();

    /**
     * gets the order helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getOrderHelper();

    /**
     * gets the data helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getDataHelper();

    /**
     * @return Netresearch_OPS_Helper_Payment_Request
     */
    public function getRequestHelper();

    /**
     * special handling like validation and so on for admin payments
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param                        $requestParams
     *
     * @return mixed
     */
    public function handleAdminPayment(Mage_Sales_Model_Quote $quote, $requestParams);

    /**
     * gets the quote helper
     *
     * @return Mage_Core_Helper_Abstract
     */
    public function getQuoteHelper();
}