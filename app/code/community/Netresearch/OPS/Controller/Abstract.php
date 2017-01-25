<?php
/**
 * Netresearch_OPS_Controller_Abstract
 * 
 * @package   
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de> 
 * @author    André Herrn <andre.herrn@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
    protected function getQuote()
    {
        return $this->_getCheckout()->getQuote();
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function getConfig()
    {
        return Mage::getModel('ops/config');
    }

    /**
     * Return order instance loaded by increment id'
     *
     * @return Mage_Sales_Model_Order
     */

    /**
     * Return order instance loaded by increment id'
     *
     * @param  $opsOrderId
     *
     * @return Mage_Sales_Model_Order
     */

    protected function _getOrder($opsOrderId = null)
    {
        if (empty($this->_order)) {
            if (null === $opsOrderId) {
                $opsOrderId = $this->getRequest()->getParam('orderID');
            }
            $this->_order = Mage::helper('ops/order')->getOrder($opsOrderId);
        }

        return $this->_order;
    }

    /**
     * Get singleton with Checkout by OPS Api
     *
     * @return Netresearch_OPS_Model_Payment_Abstract
     */
    protected function _getApi()
    {
        $api = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance();
        if (null != $this->getRequest()->getParam('orderID')) {
            $api = $this->_getOrder()->getPayment()->getMethodInstance();
        }

        return $api;
    }

    /**
     * get payment helper
     *
     * @return Netresearch_OPS_Helper_Payment
     */
    protected function getPaymentHelper()
    {
        return Mage::helper('ops/payment');
    }
    
    /**
     * get direct link helper
     *
     * @return Netresearch_OPS_Helper_Directlink
     */
    protected function getDirectlinkHelper()
    {
        return Mage::helper('ops/directlink');
    }

    protected function getSubscriptionHelper()
    {
        return Mage::helper('ops/subscription');
    }

    /**
     * Validation of incoming OPS data
     *
     * @param mixed[]|bool $paramOverwrite array of parameters with SHASIGN to validate instead of standard request
     *                                     parameters
     *
     * @return bool
     */
    protected function _validateOPSData($paramOverwrite = false)
    {
        $helper = Mage::helper('ops');

        $params = $paramOverwrite ? : $this->getRequest()->getParams();

        if ($this->getSubscriptionHelper()->isSubscriptionFeedback($params)) {
            $profile = $this->getSubscriptionHelper()->getProfileForSubscription($params['orderID']);
            if (!$profile->getId()) {
                $this->_getCheckout()->addError($this->__('Subscription is not valid'));
                $helper->log(
                    $helper->__(
                        "Incoming Ingenico ePayments Feedback\n\nRequest Path: %s\nParams: %s\n\nSubscription not valid\n",
                        $this->getRequest()->getPathInfo(),
                        serialize($this->getRequest()->getParams())
                    )
                );
                return false;
            }
            $storeId = $profile->getStoreId();
        } else {
            $order = $this->_getOrder();
            if (!$order->getId()) {
                $helper->log(
                    $helper->__(
                        "Incoming Ingenico ePayments Feedback\n\nRequest Path: %s\nParams: %s\n\nOrder not valid\n",
                        $this->getRequest()->getPathInfo(),
                        serialize($this->getRequest()->getParams())
                    )
                );
                $this->_getCheckout()->addError($this->__('Order is not valid'));
                return false;
            }
            $storeId = $order->getStoreId();
        }

        //remove custom responseparams, because they are not hashed by Ingenico ePayments
        if ($this->getConfig()->getConfigData('template') == Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_IFRAME
            && array_key_exists('IFRAME', $params)
        ) {
            unset($params['IFRAME']);
        }

        $secureKey = $this->getConfig()->getShaInCode($storeId);
        $secureSet = $this->getPaymentHelper()->getSHAInSet($params, $secureKey);

        $helper->log(
            $helper->__(
                "Incoming Ingenico ePayments Feedback\n\nRequest Path: %s\nParams: %s\n",
                $this->getRequest()->getPathInfo(),
                serialize($this->getRequest()->getParams())
            )
        );
        
        if (Mage::helper('ops/payment')->shaCryptValidation($secureSet, $params['SHASIGN']) !== true) {
            $this->_getCheckout()->addError($this->__('Hash is not valid'));
            return false;
        }

        return true;
    }

    public function isJsonRequested($params)
    {
        if (array_key_exists('RESPONSEFORMAT', $params) && $params['RESPONSEFORMAT'] == 'JSON') {
            return true;
        }
        return false;
    }

    public function getSubscriptionManager()
    {
        return Mage::getModel('ops/subscription_manager');
    }
}
