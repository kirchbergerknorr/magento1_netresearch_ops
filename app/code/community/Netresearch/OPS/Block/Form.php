<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netresearch_OPS_Block_Form extends Mage_Payment_Block_Form_Cc
{

    protected $pmLogo = null;

    protected $fieldMapping = array();

    protected $config = null;

    protected $_aliasDataForCustomer = array();

    /**
     * Frontend Payment Template
     */
    const FRONTEND_TEMPLATE = 'ops/form.phtml';

    /**
     * Init OPS payment form
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::FRONTEND_TEMPLATE);
    }

    /**
     * get OPS config
     *
     * @return Netresearch_Ops_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->config =  Mage::getSingleton('ops/config');
        }

        return $this->config;
    }

    /**
     * @param Netresearch_OPS_Model_Config $config
     * @return $this
     */
    public function setConfig(Netresearch_OPS_Model_Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (Mage::app()->getStore()->isAdmin()) {
            $quote = Mage::getSingleton('adminhtml/sales_order_create')->getQuote();
        }

        return $quote;
    }

    /**
     * @return array
     */
    public function getDirectDebitCountryIds()
    {
        return explode(',', $this->getConfig()->getDirectDebitCountryIds());
    }

    public function getBankTransferCountryIds()
    {
        return explode(',', $this->getConfig()->getBankTransferCountryIds());
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getPSPID($storeId = null)
    {
        return Mage::getModel('ops/config')->getPSPID($storeId);
    }

    /**
     * @param null $storeId
     * @param bool $admin
     * @return string
     */
    public function getGenerateHashUrl($storeId = null, $admin = false)
    {
        return Mage::getModel('ops/config')->getGenerateHashUrl($storeId, $admin);
    }

    /**
     * @return string
     */
    public function getValidationUrl()
    {
        return Mage::getModel('ops/config')->getValidationUrl();
    }

    /**
     * @return array
     */
    public function getDirectEbankingBrands()
    {
        return explode(',', $this->getConfig()->getDirectEbankingBrands());
    }


    /**
     * wrapper for Netresearch_OPS_Helper_Data::checkIfUserRegistering
     *
     * @return type bool
     */
    public function isUserRegistering()
    {
        return Mage::Helper('ops/data')->checkIfUserIsRegistering();
    }

    /**
     * wrapper for Netresearch_OPS_Helper_Data::checkIfUserRegistering
     *
     * @return type bool
     */
    public function isUserNotRegistering()
    {
        return Mage::Helper('ops/data')->checkIfUserIsNotRegistering();
    }

    /**
     * @return string
     */
    public function getPmLogo()
    {
        return $this->pmLogo;
    }

    /**
     * @return array
     */
    protected function getFieldMapping()
    {
        return $this->getConfig()->getFrontendFieldMapping();
    }

    /**
     * @param $mappedFields
     * @param $key
     * @param $value
     * @param $frontendFields
     *
     * @return mixed
     */
    public function getFrontendValidationFields($mappedFields, $key, $value, $frontendFields)
    {
        if (!is_array($mappedFields[$key])) {
            $frontendFields[$mappedFields[$key]] = $value;
        } else {
            foreach ($mappedFields[$key] as $mKey) {
                $frontendFields[$mKey] = $value;
            }
        }

        return $frontendFields;
    }

    public function getImageForBrand($brand)
    {
        $brandName = str_replace(' ', '', $brand);
        return $this->getSkinUrl('images/ops/alias/brands/'. $brandName .'.png');
    }

    /**
     * Function to add the Payment Logo to the according title
     *
     * @return string
     */
    public function getMethodLabelAfterHtml()
    {
        $paymentCode  = $this->getMethod()->getCode();
        $logoValue    = Mage::getStoreConfig('payment/' . $paymentCode . '/image');
        $logoPosition = Mage::getStoreConfig('payment/' . $paymentCode . '/position');

        if ($logoPosition != 'hidden') {
            if (!empty($logoValue)) {
                $url = Mage::getBaseUrl('media') . 'ops/paymentLogo/' . $logoValue;
            } else {
                $url = Mage::helper('ops/payment')->getPaymentDefaultLogo($paymentCode);
            }

            return "<span class='payment-logo $logoPosition'><img src='$url' alt='$paymentCode' title='$paymentCode'/></span>";
        }

        return '';
    }

    /**
     * Obtain redirect message.
     *
     * @return string
     */
    public function getRedirectMessage()
    {
        return $this->__('You will be redirected to finalize your payment.');
    }

    /**
     * retrieves the alias data for the logged in customer
     *
     * @return array | null - array the alias data or null if the customer
     * is not logged in
     */
    protected function getStoredAliasForCustomer()
    {
        if (Mage::helper('customer/data')->isLoggedIn()
            && Mage::getModel('ops/config')->isAliasManagerEnabled($this->getMethodCode())
        ) {
            $quote = $this->getQuote();
            $aliases = Mage::helper('ops/alias')->getAliasesForAddresses(
                $quote->getCustomer()->getId(), $quote->getBillingAddress(),
                $quote->getShippingAddress(), $quote->getStoreId()
            )
                ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
                ->addFieldToFilter('payment_method', $this->getMethodCode())
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC);


            foreach ($aliases as $key => $alias) {
                $this->_aliasDataForCustomer[$key] = $alias;
            }
        }

        return $this->_aliasDataForCustomer;
    }

    /**
     * @param null $storeId
     * @param bool $admin
     *
     * @return string
     */
    public function getAliasAcceptUrl($storeId = null, $admin = false)
    {
        return Mage::getModel('ops/config')->getAliasAcceptUrl($storeId, $admin);
    }

    /**
     * @param null $storeId
     * @param bool $admin
     *
     * @return string
     */
    public function getAliasExceptionUrl($storeId = null, $admin = false)
    {
        return Mage::getModel('ops/config')->getAliasExceptionUrl($storeId, $admin);
    }


    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getAliasGatewayUrl($storeId = null)
    {
        return Mage::getModel('ops/config')->getAliasGatewayUrl($storeId);
    }

    /**
     *
     * @param $aliasId
     *
     * @return null|string - the card holder either from alias data or
     * the name from the the user who is logged in, null otherwise
     */
    public function getCardHolderName($aliasId)
    {
        $cardHolderName = $this->getStoredAliasDataForCustomer($aliasId, 'card_holder');
        $customerHelper = Mage::helper('customer/data');
        if ((null === $cardHolderName || 0 === strlen(trim($cardHolderName)))
            && $customerHelper->isLoggedIn()
            && Mage::getModel('ops/config')->isAliasManagerEnabled($this->getMethodCode())
        ) {
            $cardHolderName = $customerHelper->getCustomerName();
        }

        return $cardHolderName;
    }

    /**
     * @param $aliasId
     * @return null|string
     */
    public function getAliasCardNumber($aliasId)
    {
        $aliasCardNumber = $this->getStoredAliasDataForCustomer($aliasId, 'pseudo_account_or_cc_no');
        if (0 < strlen(trim($aliasCardNumber))) {
            $aliasCardNumber = Mage::helper('ops/alias')->formatAliasCardNo(
                $this->getStoredAliasDataForCustomer($aliasId, 'brand'), $aliasCardNumber
            );
        }

        return $aliasCardNumber;
    }

    /**
     * retrieves single values to given keys from the alias data
     *
     * @param $aliasId
     * @param $key - string the key for the alias data
     *
     * @return null|string - null if key is not set in the alias data, otherwise
     * the value for the given key from the alias data
     */
    protected function getStoredAliasDataForCustomer($aliasId, $key)
    {
        $returnValue = null;
        $aliasData   = array();

        if (empty($this->_aliasDataForCustomer)) {
            $aliasData = $this->getStoredAliasForCustomer();
        } else {
            $aliasData = $this->_aliasDataForCustomer;
        }

        if (array_key_exists($aliasId, $aliasData) && $aliasData[$aliasId]->hasData($key)) {
            $returnValue = $aliasData[$aliasId]->getData($key);
        }

        return $returnValue;
    }

}
