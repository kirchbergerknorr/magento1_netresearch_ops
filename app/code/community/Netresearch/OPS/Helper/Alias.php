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
class Netresearch_OPS_Helper_Alias extends Mage_Core_Helper_Abstract
{

    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }

    public function isAdminSession()
    {
        if ($this->getAdminSession()->getUser()) {
            return 0 < $this->getAdminSession()->getUser()->getUserId();
        }
        return false;
    }
    
    /**
     * PM value is not used for payments with Alias Manager
     *
     * @param Mage_Sales_Model_Quote_Payment|null Payment
     *
     * @return null
     */
    public function getOpsCode($payment = null)
    {
        return $payment;
    }

    /**
     * BRAND value is not used for payments with Alias Manager
     *
     * @param Mage_Sales_Model_Quote_Payment|null Payment
     *
     * @return null
     */
    public function getOpsBrand($payment = null)
    {
        return $payment;
    }

    /**
     * saves the alias if customer is logged in (and want to create an alias)
     *
     * @param                        $params
     *
     * @return Netresearch_OPS_Model_Alias | null
     */
    public function saveAlias($params)
    {
        $quote = null;
        $aliasModel = null;
        Mage::helper('ops')->log('aliasData ' . Zend_Json::encode(Mage::helper('ops/data')->clearMsg($params)));
        if (array_key_exists('Alias_OrderId', $params) && is_numeric($params['Alias_OrderId'])) {
            $quote = Mage::getModel('sales/quote')->load($params['Alias_OrderId']);
        }

        if ($quote instanceof Mage_Sales_Model_Quote
            && $quote->getPayment()
            && Mage::getSingleton('checkout/type_onepage')->getCheckoutMethod()
            != Mage_Checkout_Model_Type_Onepage::METHOD_GUEST
            && (array_key_exists('Alias_StorePermanently', $params) && 'Y' == $params['Alias_StorePermanently'])
        ) {

            // alias does not exist -> create a new one if requested
            if (null != $quote  && $quote->getPayment()) {
                // create new alias
                $aliasModel = $this->saveNewAliasFromQuote($quote, $params);
                $quote->getPayment()->setAdditionalInformation(
                    'opsAliasId', $aliasModel->getId()
                );
                $quote->getPayment()->save();
            }
        } elseif (array_key_exists('orderid', $params)) {
            /** @var Mage_Sales_Model_Order $order */
            $order      = Mage::helper('ops/order')->getOrder($params['orderid']);
            $aliasModel = $this->saveNewAliasFromOrder($order, $params);
            $order->getPayment()->setAdditionalInformation('opsAliasId', $aliasModel->getId());
        }

        return $aliasModel;
    }

    /**
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function deleteAlias(Mage_Sales_Model_Quote $quote)
    {
        $customerId = $quote->getCustomer()->getId();
        $billingAddressHash = $this->generateAddressHash(
            $quote->getBillingAddress()
        );
        $shippingAddressHash = $this->generateAddressHash(
            $quote->getShippingAddress()
        );
        $aliasModel = Mage::getModel('ops/alias');
        $aliasCollection = $aliasModel->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('billing_address_hash', $billingAddressHash)
            ->addFieldToFilter('shipping_address_hash', $shippingAddressHash)
            ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::PENDING)
            ->addFieldToFilter('store_id', array(array('eq' => $quote->getStoreId()), array('null' => true)))
            ->setOrder('created_at', 'DESC')
            ->setPageSize(1);
        $aliasCollection->load();
        foreach ($aliasCollection as $alias) {
            $alias->delete();
        }
    }

    protected function saveNewAliasFromQuote(Mage_Sales_Model_Quote $quote, $params)
    {
        $customerId = $quote->getCustomer()->getId();
        $billingAddressHash = $this->generateAddressHash(
            $quote->getBillingAddress()
        );
        $shippingAddressHash = $this->generateAddressHash(
            $quote->getShippingAddress()
        );


        $aliasData = array();
        $aliasData['customer_id']               = $customerId;
        $aliasData['alias']                     = $params['Alias_AliasId'];
        $aliasData['expiration_date']           = $params['Card_ExpiryDate'];
        $aliasData['billing_address_hash']      = $billingAddressHash;
        $aliasData['shipping_address_hash']     = $shippingAddressHash;
        $aliasData['brand']                     = $params['Card_Brand'];
        $aliasData['payment_method']            = $quote->getPayment()->getMethod();
        $aliasData['pseudo_account_or_cc_no']   = $params['Card_CardNumber'];
        $aliasData['state']                     = Netresearch_OPS_Model_Alias_State::PENDING;
        $aliasData['store_id']                  = $quote->getStoreId();

        if (array_key_exists('Card_CardHolderName', $params)) {
            $aliasData['card_holder'] = $params['Card_CardHolderName'];
        }

        $aliasModel = $this->persistAlias($aliasData);

        return $aliasModel;
    }

    public function saveNewAliasFromOrder(Mage_Sales_Model_Order $order, $params)
    {
        $customerId = $order->getCustomerId();
        $billingAddressHash = $this->generateAddressHash(
            $order->getBillingAddress()
        );
        $shippingAddressHash = $this->generateAddressHash(
            $order->getShippingAddress()
        );

        $aliasData = array();
        $aliasData['customer_id']               = $customerId;
        $aliasData['alias']                     = $params['alias'];
        $aliasData['expiration_date']           = $params['ed'];
        $aliasData['billing_address_hash']      = $billingAddressHash;
        $aliasData['shipping_address_hash']     = $shippingAddressHash;
        $aliasData['brand']                     = $params['brand'];
        $aliasData['payment_method']            = $order->getPayment()->getMethod();
        $aliasData['pseudo_account_or_cc_no']   = $params['cardno'];
        $aliasData['state']                     = Netresearch_OPS_Model_Alias_State::ACTIVE;
        $aliasData['store_id']                  = $order->getStoreId();
        $aliasData['card_holder']               = $params['cn'];

        $aliasModel = $this->persistAlias($aliasData);

        return $aliasModel;
    }


    public function persistAlias(array $aliasParams)
    {
        /** @var Netresearch_OPS_Model_Alias $aliasModel */
        $aliasModel = Mage::getModel('ops/alias')->load($aliasParams['alias'], 'alias');

        Mage::helper('ops')->log(
            'saving alias' . Zend_Json::encode($aliasModel->getData())
        );

        $aliasModel->addData($aliasParams);
        $aliasModel->save();

        return $aliasModel;
    }

    /**
     * generates hash from address data
     *
     * @param Mage_Customer_Model_Address_Abstract $address the address data to hash
     *
     * @returns string hash of address
     */
    public function generateAddressHash(Mage_Customer_Model_Address_Abstract $address) {
        /** @var Netresearch_OPS_Helper_Payment $opsHelper */
        $opsHelper = Mage::helper('ops/payment');
        $addressString = $address->getFirstname();
        $addressString .= $address->getMiddlename();
        $addressString .= $address->getLastname();
        $addressString .= $address->getCompany();
        $street = $address->getStreetFull();
        if (is_array($street)) {
            $street = implode('', $street);
        }
        $addressString .= $street;
        $addressString .= $address->getPostcode();
        $addressString .= $address->getCity();
        $addressString .= $address->getCountryId();

        return hash($opsHelper->getCryptMethod(), $addressString);
    }

    /**
     * retrieves the aliases for a given customer
     *
     * @param int $customerId
     * @param     Mage_Sales_Model_Quote
     *
     * @return Netresearch_OPS_Model_Mysql4_Alias_Collection - collection
     *  of aliases for the given customer
     */
    public function getAliasesForCustomer(
    $customerId, Mage_Sales_Model_Quote $quote = null
    )
    {
        $billingAddressHash = null;
        $shippingAddressHash = null;
        $storeId = null;
        if (null !=($quote)) {
            $billingAddressHash = $this->generateAddressHash(
                $quote->getBillingAddress()
            );
            $shippingAddressHash = $this->generateAddressHash(
                $quote->getShippingAddress()
            );
            $storeId = $quote->getStoreId();
        }
        return Mage::getModel('ops/alias')
                ->getAliasesForCustomer(
                    $customerId, $billingAddressHash, $shippingAddressHash, $storeId
                );
    }

    /**
     * if alias is valid for address
     *
     * @param int                            $customerId
     * @param string                         $alias
     * @param Mage_Sales_Model_Quote_Address $billingAddress
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     * @param int $storeId
     *
     * @return boolean
     */
    public function isAliasValidForAddresses(
    $customerId, $alias, $billingAddress, $shippingAddress, $storeId = null
    )
    {
        $aliasCollection = $this->getAliasesForAddresses(
            $customerId, $billingAddress, $shippingAddress, $storeId
        )
            ->addFieldToFilter('alias', $alias)
            ->setPageSize(1);
        return (1 == $aliasCollection->getSize());
    }

    /**
     * get aliases that are allowed for customer with given addresses
     *
     * @param int                            $customerId      Id of customer
     * @param Mage_Sales_Model_Quote_Address $billingAddress  billing address
     * @param Mage_Sales_Model_Quote_Address $shippingAddress shipping address
     * @param int $storeId
     *
     * @return Netresearch_OPS_Model_Mysql4_Alias_Collection
     */
    public function getAliasesForAddresses(
    $customerId, $billingAddress, $shippingAddress, $storeId = null
    )
    {
        $billingAddressHash = $this->generateAddressHash($billingAddress);
        $shippingAddressHash = $this->generateAddressHash($shippingAddress);
        return Mage::getModel('ops/alias')->getCollection()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('billing_address_hash', $billingAddressHash)
                ->addFieldToFilter('shipping_address_hash', $shippingAddressHash)
                ->addFieldToFilter('store_id', array(array('eq' => $storeId), array('null' => true)));
    }

    /**
     * formats the pseudo cc number in a brand specific format
     * supported brand (so far):
     *      - MasterCard
     *      - Visa
     *      - American Express
     *      - Diners Club
     *
     * @param $brand - the cc brand we need to format the pseudo cc number
     * @param $aliasCcNo - the pseudo cc number itself
     *
     * @return string - the formatted pseudo cc number
     */
    public function formatAliasCardNo($brand, $aliasCcNo)
    {

        if (in_array(strtolower($brand), array('visa', 'mastercard'))) {
            $aliasCcNo = implode(' ', str_split($aliasCcNo, 4));
        }
        if (in_array(strtolower($brand), array('american express', 'diners club', 'maestrouk'))) {
            $aliasCcNo = str_replace('-', ' ', $aliasCcNo);
        }

        return strtoupper($aliasCcNo);
    }

    /**
     * saves the alias and if given the cvc to the payment information
     *
     * @param Mage_Payment_Model_Info $payment - the payment which should be updated
     * @param array                   $aliasData - the data we will update
     * @param boolean                 $userIsRegistering - is registering method in checkout
     * @param boolean                 $paymentSave - is it necessary to save the payment afterwards
     */
    public function setAliasToPayment(
        Mage_Payment_Model_Info $payment,
        array $aliasData,
        $userIsRegistering = false,
        $paymentSave = false
    )
    {
        if (array_key_exists('alias_aliasid', $aliasData) && 0 < strlen(trim($aliasData['alias_aliasid']))) {
            $payment->setAdditionalInformation('alias', trim($aliasData['alias_aliasid']));
            $payment->setAdditionalInformation('userIsRegistering', $userIsRegistering);
            if (array_key_exists('card_cvc', $aliasData)) {
                $payment->setAdditionalInformation('cvc', $aliasData['card_cvc']);
                $this->setCardHolderToAlias($payment->getQuote(), $aliasData);
            }

            if ( array_key_exists('method', $aliasData)) {
                $alias = Mage::getModel('ops/alias')->load($aliasData['alias_aliasid'], 'alias');
                $alias->setPaymentMethod($aliasData['method']);
                $alias->save();
            }

            $payment->setDataChanges(true);
            if ($paymentSave === true) {
                $payment->save();
            }
        } else {
            Mage::helper('ops/data')->log('did not save alias due to empty alias');
            Mage::helper('ops/data')->log(serialize($aliasData));
        }
    }

    protected function setCardHolderToAlias($quote, $aliasData)
    {
        $customerId = $quote->getCustomerId();
        $billingAddressHash = $this->generateAddressHash($quote->getBillingAddress());
        $shippingAddressHash = $this->generateAddressHash($quote->getShippingAddress());
        $oldAlias = Mage::getModel('ops/alias')->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('billing_address_hash', $billingAddressHash)
            ->addFieldToFilter('shipping_address_hash', $shippingAddressHash)
            ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
            ->addFieldToFilter('store_id', array(array('eq' => $quote->getStoreId()), array('null' => true)))
            ->getFirstItem();
        // and if so update this alias with alias data from alias gateway
        if (is_numeric($oldAlias->getId()) && null === $oldAlias->getCardHolder()
            && array_key_exists('Card_CardHolderName', $aliasData)
        ) {
            $oldAlias->setCardHolder($aliasData['Card_CardHolderName']);
            $oldAlias->save();
        }
    }

    /**
     * set the last pending alias to active and remove other aliases for customer based on address
     *
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order|null $order
     * @param bool $saveSalesObjects
     */
    public function setAliasActive(
        Mage_Sales_Model_Quote $quote,
        Mage_Sales_Model_Order $order = null,
        $saveSalesObjects = false
    ) 
    {
        if (null === $quote->getPayment()->getAdditionalInformation('userIsRegistering')
            || false == $quote->getPayment()->getAdditionalInformation('userIsRegistering')
        ) {
            $aliasesToDelete = Mage::helper('ops/alias')->getAliasesForAddresses(
                $quote->getCustomer()->getId(), $quote->getBillingAddress(), $quote->getShippingAddress()
            )
                ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE);
            $lastPendingAlias = Mage::helper('ops/alias')->getAliasesForAddresses(
                $quote->getCustomer()->getId(),
                $quote->getBillingAddress(),
                $quote->getShippingAddress(),
                $quote->getStoreId()
            )
                ->addFieldToFilter('alias', $quote->getPayment()->getAdditionalInformation('alias'))
                ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::PENDING)
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
                ->getFirstItem();
            if (0 < $lastPendingAlias->getId()) {
                foreach ($aliasesToDelete as $alias) {
                    $alias->delete();
                }
                $lastPendingAlias->setState(Netresearch_OPS_Model_Alias_State::ACTIVE);
                $lastPendingAlias->setPaymentMethod($order->getPayment()->getMethod());
                $lastPendingAlias->save();
            }
        } else {
            $this->setAliasToActiveAfterUserRegisters($order, $quote);
        }
        $this->cleanUpAdditionalInformation($order->getPayment(), false, $saveSalesObjects);
        $this->cleanUpAdditionalInformation($quote->getPayment(), false, $saveSalesObjects);
    }

    public function setAliasToActiveAfterUserRegisters(
    Mage_Sales_Model_Order $order, Mage_Sales_Model_Quote $quote
    )
    {
        if (true == $quote->getPayment()->getAdditionalInformation('userIsRegistering')
        ) {
            $customerId = $order->getCustomerId();
            $billingAddressHash = $this->generateAddressHash(
                $quote->getBillingAddress()
            );
            $shippingAddressHash = $this->generateAddressHash(
                $quote->getShippingAddress()
            );
            $aliasId = $quote->getPayment()->getAdditionalInformation(
                'opsAliasId'
            );
            if (is_numeric($aliasId) && 0 < $aliasId) {
                $alias = Mage::getModel('ops/alias')->getCollection()
                    ->addFieldToFilter(
                        'alias', $quote->getPayment()->getAdditionalInformation('alias')
                    )
                    ->addFieldToFilter(
                        'billing_address_hash', $billingAddressHash
                    )
                    ->addFieldToFilter(
                        'shipping_address_hash', $shippingAddressHash
                    )
                    ->addFieldToFilter('store_id', array('eq' => $quote->getStoreId()))
                    ->getFirstItem();

                $alias->setState(Netresearch_OPS_Model_Alias_State::ACTIVE);
                $alias->setPaymentMethod($order->getPayment()->getMethod());
                $alias->setCustomerId($customerId);
                $alias->save();
            }
        }
    }

    /**
     * cleans up the stored cvc and storedOPSId
     *
     * @param Mage_Sales_Model_Quote_Payment || Mage_Sales_Model_Order_Payment $payment
     * @param bool $cvcOnly
     * @param bool $savePayment
     *
     */
    public function cleanUpAdditionalInformation($payment, $cvcOnly = false, $savePayment = false)
    {
        if (is_array($payment->getAdditionalInformation())
            && array_key_exists('cvc', $payment->getAdditionalInformation())
        ) {
            $payment->unsAdditionalInformation('cvc');
        }

        if ($cvcOnly === false && is_array($payment->getAdditionalInformation())
            && array_key_exists('storedOPSId', $payment->getAdditionalInformation())
        ) {
            $payment->unsAdditionalInformation('storedOPSId');
        }

        /* OGNH-7: seems not to needed anymore since payment and quote is saved after this call,
         otherwise admin payments will fail */
        if ($savePayment) {
            $payment->save();
        }
    }
}
