<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Netresearch_OPS_Block_RetryPayment
 *
 * @category payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Block_RetryPayment extends Netresearch_OPS_Block_Placeform
{

    protected $_order = null;

    protected function _getApi()
    {
        return $this->_getOrder()->getPayment()->getMethodInstance();
    }

    protected function _getOrder()
    {
        if (null === $this->_order) {
            $opsOrderId = $this->getOrderId();
            $this->_order = Mage::helper('ops/order')->getOrder($opsOrderId);
        }

        return $this->_order;
    }

    /**
     * Getting placeform url
     *
     * @return string
     */
    public function getFormAction()
    {
        $formAction = Mage::getUrl(
            '*/*/updatePaymentAndPlaceForm',
            array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure())
        );

        return $formAction;
    }

    /**
     * Getting cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        $formAction = Mage::getUrl(
            '*/*/cancel',
            array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure())
        );

        return $formAction;
    }

    public function getOrderId()
    {
        return $this->getRequest()->getParam('orderID');
    }

    /**
     * Returns the orders billing address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getBillingAddress()
    {
        return $this->_getOrder()->getBillingAddress();
    }

    /**
     * Returns the orders shipping address or false in case of a virtual order
     *
     * @return Mage_Sales_Model_Order_Address|false
     */
    public function getShippingAddress()
    {
        return $this->_getOrder()->getShippingAddress();
    }
}
