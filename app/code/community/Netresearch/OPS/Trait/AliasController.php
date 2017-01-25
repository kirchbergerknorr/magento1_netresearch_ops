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
 * @copyright Copyright (c) 2016 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * AliasController.php
 *
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

trait Netresearch_OPS_Trait_AliasController
{
    use Netresearch_OPS_Trait_PaymentHelper;
    
    /**
     * @return Netresearch_OPS_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('ops/config');
    }
    
    /**
     * accept-action for Alias-generating iframe-response
     *
     */
    public function acceptAction()
    {
        $params = $this->getRequest()->getParams();
        $requiredParams = array_fill_keys(array('Alias_OrderId', 'Alias_AliasId'), '');
        $missingParams = count(array_diff_key($requiredParams, $params));
        if ($missingParams) {

            return $this->parseFrontendException();
        } else {
            $helper = Mage::helper('ops');
            $helper->log(
                $helper->__(
                    "Incoming accepted Ingenico ePayments Alias Feedback\n\nRequest Path: %s\nParams: %s\n",
                    $this->getRequest()->getPathInfo(),
                    serialize($params)
                )
            );
            Mage::helper('ops/alias')->saveAlias($params);


            if (array_key_exists('Alias_OrderId', $params)) {
                $quote = Mage::getModel('sales/quote')->load($params['Alias_OrderId']);
                $this->updateAdditionalInformation($quote, $params);
            } else {
                $quote = Mage::getSingleton('checkout/session')->getQuote();
            }

            // OGNH-7 special handling for admin orders
            Mage::helper('ops/alias')->setAliasToPayment(
                $quote->getPayment(),
                array_change_key_case($params, CASE_LOWER),
                false
            );

            return $this->parseFrontendSuccess($params['Alias_AliasId']);
        }
    }
    
    /**
     * exception-action for Alias-generating iframe-response
     *
     */
    public function exceptionAction()
    {
        $params = $this->getRequest()->getParams();
        $errors = array();
        
        foreach ($params as $key => $value) {
            if (stristr($key, 'error') && 0 != $value) {
                $errors[] = $value;
            }
        }
        
        $helper = Mage::helper('ops');
        $helper->log(
            $helper->__(
                "Incoming exception Ingenico ePayments Alias Feedback\n\nRequest Path: %s\nParams: %s\n",
                $this->getRequest()->getPathInfo(),
                serialize($params)
            )
        );
        
        return $this->parseFrontendException();
    }
    
    /**
     * Generates the hash for the hosted tokenization page request
     *
     * @throws Exception
     */
    public function generateHashAction()
    {
        $storeId = $this->getStoreId();
        
        $result = $this->generateHash($storeId);
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    /**
     * Get store id from quote or request
     *
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = null;
        
        if (Mage::app()->getStore()->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/session_quote');
            $storeId = $quote->getStoreId();
        } else {
            $quoteId = $this->getRequest()->getParam('orderid');
            
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            
            if ($quote->getId() === null) {
                $quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);
            }
            
            if ($quote->getId() !== null) {
                $storeId = $quote->getStoreId();
            }
            if ($this->getRequest()->getParam('storeId') !== null) {
                $storeId = $this->getRequest()->getParam('storeId');
            }
        }
        
        return $storeId;
    }
    
    /**
     * updates the additional information from payment, thats needed for backend reOrders
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param string[]               $params
     */
    public function updateAdditionalInformation(Mage_Sales_Model_Quote $quote, $params)
    {
        if ($quote->getId() !== null && $quote->getPayment() && $quote->getPayment()->getId() !== null) {
            $payment = $quote->getPayment();
            if (array_key_exists('Alias_AliasId', $params)) {
                $payment->setAdditionalInformation('alias', $params['Alias_AliasId']);
            }
            if (array_key_exists('Card_Brand', $params)) {
                $payment->setAdditionalInformation('CC_BRAND', $params['Card_Brand']);
            }
            if (array_key_exists('Card_CardHolderName', $params)) {
                $payment->setAdditionalInformation('CC_CN', $params['Card_CardHolderName']);
            }
            if ($this->userIsRegistering()) {
                $payment->setAdditionalInformation('userIsRegistering', true);
            }
            $quote->setPayment($payment)->setDataChanges(true)->save();
            $quote->setDataChanges(true)->save();
        }
    }
    
    /**
     * Checks if checkout method is registering
     *
     * @return bool
     */
    protected function userIsRegistering()
    {
        return Mage::getSingleton('checkout/type_onepage')->getCheckoutMethod()
        === Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER;
    }
    
    /**
     * Generate hash from request parameters
     *
     * @param $storeId
     *
     * @return array
     */
    protected function generateHash($storeId)
    {
        $data = $this->cleanParamKeys();
        
        $secret = $this->getConfig()->getShaOutCode($storeId);
        $raw = $this->getPaymentHelper()->getSHAInSet($data, $secret);
        $result = array('hash' => Mage::helper('ops/payment')->shaCrypt($raw));
        
        return $result;
    }
    
    /**
     * Cleans param array from magentos admin params, fixes underscored keys
     *
     * @return array
     */
    protected function cleanParamKeys()
    {
        $data = array();
        foreach ($this->getRequest()->getParams() as $key => $value) {
            if ($key == 'form_key' || $key == 'isAjax' || $key == 'key') {
                continue;
            }
            $data[str_replace('_', '.', $key)] = $value;
        }
        
        return $data;
    }
    
    /**
     * @return Mage_Core_Controller_Response_Http
     */
    protected function parseFrontendException()
    {
        $result = "<script type='application/javascript'>" .
            "window.onload =  function() {  top.document.fire('alias:failure'); };" .
            "</script>";
        
        return $this->getResponse()->setBody($result);
    }
    
    /**
     * @param string $alias alias to be parsed in frontend javascript event
     *
     * @return Mage_Core_Controller_Response_Http
     */
    protected function parseFrontendSuccess($alias)
    {
        $result = sprintf(
            "<script type='application/javascript'>".
            "window.onload =  function() {  top.document.fire('alias:success', '%s'); };</script>",
            $alias
        );
        
        return $this->getResponse()->setBody($result);
    }
}