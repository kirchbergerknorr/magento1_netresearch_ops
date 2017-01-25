<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AliasTest
 *
 * @author sebastian
 */
class Netresearch_OPS_Test_Helper_AliasTest extends EcomDev_PHPUnit_Test_Case
{

    private $_helper;
    private $store;

    public function setUp()
    {
        parent::setup();
        $this->_helper = Mage::helper('ops/alias');
        $this->store = Mage::app()->getStore(0)->load(0);
        $this->mockSessions();
    }

    protected function mockSessions()
    {
        $sessionMock = $this->getModelMockBuilder('checkout/session')
            ->disableOriginalConstructor() // This one removes session_start and other methods usage
            ->getMock();
        $this->replaceByMock('singleton', 'checkout/session', $sessionMock);

        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor() // This one removes session_start and other methods usage
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
    }

    protected function mockCheckoutOnepage($method)
    {
        $onepage = $this->getModelMock('checkout/type_onepage', array('getCheckoutMethod'));
        $onepage->expects($this->any())
            ->method('getCheckoutMehtod')
            ->will($this->returnValue($method));
        $this->replaceByMock('singleton', 'checkout/type_onepage', $onepage);
    }



    public function testGetOpsCode()
    {
        $this->assertEquals(null, Mage::helper('ops/alias')->getOpsCode());
    }

    public function testGetOpsBrand()
    {
        $this->assertEquals(null, Mage::helper('ops/alias')->getOpsBrand());
    }

    public function testSaveAliasIfCustomerIsNotLoggedIn()
    {
        $this->mockCheckoutOnepage(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
        $quote = Mage::getModel('sales/quote');
        $this->assertEquals(
            null,
            $this->_helper->saveAlias(
                array(
                'OrderID' => 4711,
                'StorePermanently' => 'N'
                )
            )
        );
    }

    public function testSaveAliasIfCustomerIsLoggedIn()
    {
        $this->mockCheckoutOnepage(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER);
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $this->getModelMock('sales/quote', array('save'));
        $this->replaceByMock('model', 'sales/quote', $quote);
        $quote->setBillingAddress($this->getAddressData());
        $quote->setShippingAddress($this->getAddressData());
        $quote->setId(4711);
        $customer = Mage::getModel('customer/customer');
        $customer->setId(1);
        $aliasData['Alias_OrderId'] = 4711;
        $aliasData['Alias_AliasId'] = 4711;
        $aliasData['Card_Brand'] = 'Visa';
        $aliasData['Card_CardNumber'] = 'xxxx0815';
        $aliasData['Card_ExpiryDate'] = '1212';
        $aliasData['Card_CardHolderName'] = 'Foo Baar';
        $aliasData['Alias_StorePermanently'] = 'Y';
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->setMethod('CreditCard');
        $payment->setAdditionalInformation(array('saveOpsAlias' => '1'));
//        $quote->setCustomer($customer);
        $quote->setPayment($payment);

        $quoteMock = $this->getModelMock(
            'sales/quote', array('load', 'getPayment', 'getCustomer')
        );
        $quoteMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));
        $quoteMock->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));
        $this->replaceByMock('model', 'sales/quote', $quoteMock);
        $alias = $this->_helper->saveAlias($aliasData);
        $this->assertEquals('4711', $alias->getAlias());
        $this->assertEquals('Visa', $alias->getBrand());
        $this->assertEquals('xxxx0815', $alias->getPseudoAccountOrCcNo());
        $this->assertEquals('1212', $alias->getExpirationDate());
        $this->assertEquals(
            'da39a3ee5e6b4b0d3255bfef95601890afd80709',
            $alias->getBillingAddressHash()
        );
        $this->assertEquals(
            'da39a3ee5e6b4b0d3255bfef95601890afd80709',
            $alias->getShippingAddressHash()
        );
        $this->assertEquals('CreditCard', $alias->getPaymentMethod());
        $this->assertEquals(1, $alias->getCustomerId());

        $oldAliasId = $alias->getId();
        $alias = $this->_helper->saveAlias($aliasData, $quote);
        $this->assertEquals($oldAliasId, $alias->getId());
    }
    
    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSaveAliasUpdate()
    {
        $this->mockCheckoutOnepage(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER);
        $quote = $this->getModelMock('sales/quote', array('save'));
        $this->replaceByMock('model', 'sales/quote', $quote);
        $quote->setBillingAddress($this->getAddressData());
        $quote->setShippingAddress($this->getAddressData());
        $quote->setId(4711);
        $customer = new Varien_Object();
        $customer->setId(1);
        $aliasData['Alias_OrderId'] = 4711;
        $aliasData['Alias_AliasId'] = 4711;
        $aliasData['Card_Brand'] = 'Visa';
        $aliasData['Card_CardNumber'] = 'xxxx0815';
        $aliasData['Card_ExpiryDate'] = '1212';
        $aliasData['Card_CardHolderName'] = 'Foo Baar';
        $aliasData['Alias_StorePermanently'] = 'Y';
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->setMethod('CreditCard');
        $payment->setAdditionalInformation(array('saveOpsAlias' => '1'));
//        $quote->setCustomer($customer);
        $quote->setPayment($payment);

        $quoteMock = $this->getModelMock(
            'sales/quote', array('load', 'getPayment', 'getCustomer')
        );
        $quoteMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));
        $quoteMock->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));
        $this->replaceByMock('model', 'sales/quote', $quoteMock);
        $oldAlias = $this->_helper->saveAlias($aliasData);
        $oldAlias->setState(Netresearch_OPS_Model_Alias_State::ACTIVE);
        $oldAlias->save();

        $aliasData['Alias_OrderId'] = 4711;
        $aliasData['Alias_AliasId'] = 4711;
        $aliasData['Card_Brand'] = 'Mastercard';
        $aliasData['Card_CardNumber'] = 'xxxx01111';
        $aliasData['Card_ExpiryDate'] = '1213';
        $aliasData['Card_CardHolderName'] = 'Max Power';
        $aliasData['Alias_StorePermanently'] = 'Y';
        
        $updatedAlias = $this->_helper->saveAlias($aliasData);



        $this->assertEquals(4711, $updatedAlias->getAlias());
        $this->assertEquals('Mastercard', $updatedAlias->getBrand());
        $this->assertEquals('xxxx01111', $updatedAlias->getPseudoAccountOrCcNo());
        $this->assertEquals('1213', $updatedAlias->getExpirationDate());
        $this->assertEquals('Max Power', $updatedAlias->getCardHolder());
        
    }

    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSaveNewAliasFromQuote()
    {
        $reflection_class = new ReflectionClass("Netresearch_OPS_Helper_Alias");
        $method = $reflection_class->getMethod("saveNewAliasFromQuote");
        $method->setAccessible(true);

        $quote = Mage::getModel('sales/quote')->load(10);
        $aliasData = array(
            'Alias_AliasId'  => 'TestAlias',
            'Card_ExpiryDate'     => '12.12.0012',
            'Card_Brand'  => 'Visa',
            'Card_CardNumber' => '12345678',
            'Card_CardHolderName'     => 'Foo',
            'Alias_StorePermanently' => 'Y'
        );
        $aliasHelper = Mage::helper('ops/alias');
        $alias = $method->invoke($aliasHelper, $quote, $aliasData);
        $this->assertEquals('TestAlias', $alias->getAlias());
        $this->assertEquals('12.12.0012', $alias->getExpirationDate());
        $this->assertEquals('Foo', $alias->getCardHolder());
        $this->assertEquals(Netresearch_OPS_Model_Alias_State::PENDING, $alias->getState());

        $aliasData = array(
            'Alias_AliasId'  => 'TestAlias',
            'Card_ExpiryDate'     => '12.12.0012',
            'Card_Brand'  => 'Visa',
            'Card_CardNumber' => '12345678',
            'Alias_StorePermanently' => 'Y'
        );
        $aliasHelper = Mage::helper('ops/alias');
        $alias = $method->invoke($aliasHelper, $quote, $aliasData);
        $this->assertEquals('TestAlias', $alias->getAlias());
        $this->assertEquals('12.12.0012', $alias->getExpirationDate());
        $this->assertEquals('Foo', $alias->getCardHolder());
        $this->assertEquals(Netresearch_OPS_Model_Alias_State::PENDING, $alias->getState());

        $aliasData = array(
            'Alias_AliasId'  => 'TestAlias',
            'Card_ExpiryDate'     => '12.12.0012',
            'Card_Brand'  => 'Visa',
            'Card_CardNumber' => '12345678',
            'Card_CardHolderName'     => '',
            'Alias_StorePermanently' => 'Y'
        );
        $aliasHelper = Mage::helper('ops/alias');
        $alias = $method->invoke($aliasHelper, $quote, $aliasData);
        $this->assertEquals('TestAlias', $alias->getAlias());
        $this->assertEquals('12.12.0012', $alias->getExpirationDate());
        $this->assertEquals('', $alias->getCardHolder());
        $this->assertEquals(Netresearch_OPS_Model_Alias_State::PENDING, $alias->getState());
    }



    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSaveNewAliasFromOrder()
    {
        $reflection_class = new ReflectionClass("Netresearch_OPS_Helper_Alias");
        $method = $reflection_class->getMethod("saveNewAliasFromOrder");
        $method->setAccessible(true);

        $order = Mage::getModel('sales/order')->load(11);



        $aliasData = array(
            'alias'  => 'TestAlias',
            'brand'  => 'Visa',
            'cardno' => '12345678',
            'cn'     => 'Foo',
            'Alias_StorePermanently' => 'Y',
            'ed'            => '12.12.0012'
        );


        $aliasHelper = Mage::helper('ops/alias');
        $alias = $method->invoke($aliasHelper, $order, $aliasData);
        $this->assertEquals('TestAlias', $alias->getAlias());
        $this->assertEquals('12.12.0012', $alias->getExpirationDate());
        $this->assertEquals('Foo', $alias->getCardHolder());
        $this->assertEquals(Netresearch_OPS_Model_Alias_State::ACTIVE, $alias->getState());

        $aliasData = array(
            'alias'  => 'TestAlias',
            'brand'  => 'Visa',
            'cardno' => '12345678',
            'Alias_StorePermanently' => 'Y',
            'ed'            => '12.12.0012',
            'cn'            => null
        );
        $aliasHelper = Mage::helper('ops/alias');
        $alias = $method->invoke($aliasHelper, $order, $aliasData);
        $this->assertEquals('TestAlias', $alias->getAlias());
        $this->assertEquals('12.12.0012', $alias->getExpirationDate());
        $this->assertNull($alias->getCardHolder());
        $this->assertEquals(Netresearch_OPS_Model_Alias_State::ACTIVE, $alias->getState());

        $aliasData = array(
            'alias'  => 'TestAlias',
            'brand'  => 'Visa',
            'cardno' => '12345678',
            'Alias_StorePermanently' => 'Y',
            'ed'            => '12.12.0012',
            'cn'            => ''
        );
        $aliasHelper = Mage::helper('ops/alias');
        $alias = $method->invoke($aliasHelper, $order, $aliasData);
        $this->assertEquals('TestAlias', $alias->getAlias());
        $this->assertEquals('12.12.0012', $alias->getExpirationDate());
        $this->assertEquals('', $alias->getCardHolder());
        $this->assertEquals(Netresearch_OPS_Model_Alias_State::ACTIVE, $alias->getState());
    }

    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testDeleteAlias()
    {
        $reflection_class = new ReflectionClass("Netresearch_OPS_Helper_Alias");
        $method = $reflection_class->getMethod("deleteAlias");
        $method->setAccessible(true);
        $aliasModel = Mage::getModel('ops/alias');
        $quote = Mage::getModel('sales/quote')->load(10);
        $quote->setBillingAddress($this->getAddressData());
        $quote->setShippingAddress($this->getAddressData());
        $customer = Mage::getModel('customer/customer');
        $customer->setId(1);

        $quote->setCustomer($customer);

        $customerId = $quote->getCustomer()->getId();
        $aliasesForCustomer = $aliasCollection = $aliasModel->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->load();
        $oldAliasSize = count($aliasesForCustomer);

        $aliasData = array(
            'Alias_AliasId'  => '4711',
            'Card_ExpiryDate'     => '0117',
            'Card_Brand'  => 'Visa',
            'Card_CardNumber' => 'xxxx1111'
        );
        $aliasHelper = Mage::helper('ops/alias');
        $method->invoke($aliasHelper, $quote, $aliasData);
        $newAliasForCustomer = $aliasCollection = $aliasModel->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->load();
        $newAliasSize = count($newAliasForCustomer);
        $this->assertGreaterThan($newAliasSize, $oldAliasSize);
    }

    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testDeleteAliasDoesNotDeleteAliasIfAddressDoesNotMatch()
    {
        $reflection_class = new ReflectionClass("Netresearch_OPS_Helper_Alias");
        $method = $reflection_class->getMethod("deleteAlias");
        $method->setAccessible(true);
        $aliasModel = Mage::getModel('ops/alias');
        $quote = Mage::getModel('sales/quote')->load(10);
        $customer = Mage::getModel('customer/customer');
        $customer->setId(1);

        $quote->setCustomer($customer);

        $customerId = $quote->getCustomer()->getId();
        $aliasesForCustomer = $aliasCollection = $aliasModel->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->load();
        $oldAliasSize = count($aliasesForCustomer);

        $aliasData = array(
            'Alias_AliasId'  => '4711',
            'Card_ExpiryDate'     => '0117',
            'Card_Brand'  => 'Visa',
            'Card_CardNumber' => 'xxxx1111'
        );
        $aliasHelper = Mage::helper('ops/alias');
        $method->invoke($aliasHelper, $quote, $aliasData);
        $newAliasForCustomer = $aliasCollection = $aliasModel->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->load();
        $newAliasSize = count($newAliasForCustomer);
        $this->assertEquals($newAliasSize, $oldAliasSize);
    }

    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testGetAliasesForCustomer()
    {
        $quote = Mage::getModel('sales/quote')->load(10);

        $aliasesCollection = $this->_helper->getAliasesForCustomer(1);
        $this->assertEquals(5, count($aliasesCollection));

        $aliasesCollection = $this->_helper->getAliasesForCustomer(2);
        $this->assertEquals(1, count($aliasesCollection));

        $aliasesCollection = $this->_helper->getAliasesForCustomer(3);
        $this->assertEquals(0, count($aliasesCollection));

        $aliasesCollection = $this->_helper->getAliasesForCustomer(
            null, $quote
        );
        $this->assertEquals(0, count($aliasesCollection));
    }

    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     */
    public function testIsAliasValidForAddresses()
    {
        $billingAddress = Mage::getModel('sales/quote_address');
        $shippingAddress = Mage::getModel('sales/quote_address');
        $this->assertFalse(
            $this->_helper->isAliasValidForAddresses(
                1, '4711', $shippingAddress, $billingAddress
            )
        );

        $billingAddress = $this->getAddressData();
        $shippingAddress = $this->getAddressData();

        $this->assertTrue(
            $this->_helper->isAliasValidForAddresses(
                1, '4711', $shippingAddress, $billingAddress
            )
        );

        $this->assertFalse(
            $this->_helper->isAliasValidForAddresses(
                2, '4711', $shippingAddress, $billingAddress
            )
        );
    }

    protected function getAddressData()
    {
        $address = new Mage_Sales_Model_Quote_Address();
        $address->setFirstname('foo');
        $address->setLastname('bert');
        $address->setStreet1('bla street 1');
        $address->setZipcode('4711');
        $address->setCity('Cologne');
        $address->setCountry_id(1);
        return $address;
    }

    public function testGenerateAddressHash()
    {
        $address = $this->getAddressData();
        $this->assertEquals(
            '1b9ecdf409e240717f04b7155712658ab09116bb',
            $this->_helper->generateAddressHash($address)
        );
        $address->setData('street', array('wuseldusel', 'foo'));
        $this->assertEquals(
            '260a9287b2964d3674f49f589d5e5fd7143041cf',
            $this->_helper->generateAddressHash($address)
        );
    }

    public function testFormatAliasCardNo()
    {
        $helper = Mage::helper('ops/alias');
        $cardNo = 'xxxxxxxxxxxx1111';
        $cardType = 'VISA';
        $this->assertEquals(
            'XXXX XXXX XXXX 1111',
            $helper->formatAliasCardNo($cardType, $cardNo)
        );

        $cardNo = 'xxxxxxxxxxxx9999';
        $cardType = 'MasterCard';
        $this->assertEquals(
            'XXXX XXXX XXXX 9999',
            $helper->formatAliasCardNo($cardType, $cardNo)
        );

        $cardNo = '3750-xxxxxx-03';
        $cardType = 'american express';
        $this->assertEquals(
            '3750 XXXXXX 03', $helper->formatAliasCardNo($cardType, $cardNo)
        );

        $cardNo = '3750-xxxxxx-03';
        $cardType = 'DINERS CLUB';
        $this->assertEquals(
            '3750 XXXXXX 03', $helper->formatAliasCardNo($cardType, $cardNo)
        );


        $cardNo = '675941-XXXXXXXX-08';
        $cardType = 'MaestroUK';
        $this->assertEquals(
            '675941 XXXXXXXX 08', $helper->formatAliasCardNo($cardType, $cardNo)
        );

        $cardNo = '675956-XXXXXXXX-54';
        $cardType = 'MaestroUK';
        $this->assertEquals(
            '675956 XXXXXXXX 54', $helper->formatAliasCardNo($cardType, $cardNo)
        );

        $cardNo = '564182-XXXXXXXX-69';
        $cardType = 'MaestroUK';
        $this->assertEquals(
            '564182 XXXXXXXX 69', $helper->formatAliasCardNo($cardType, $cardNo)
        );

        $cardNo = '3750-xxxxxx-03';
        $cardType = 'PostFinance Card';
        $this->assertEquals(
            '3750-XXXXXX-03', $helper->formatAliasCardNo($cardType, $cardNo)
        );
    }

    /**
     *
     * @loadFixture ../../../var/fixtures/orders.yaml
     * @loadFixture ../../../var/fixtures/aliases.yaml
     */
    public function testSetAliasToPayment()
    {
        $params = array();
        $quote = Mage::getModel('sales/quote')->load(11);
        $helper = Mage::helper('ops/alias');
        $helper->setAliasToPayment($quote->getPayment(), $params);
        $payment = $quote->getPayment();
        $this->assertArrayNotHasKey('alias', $payment->getAdditionalInformation());
        $this->assertArrayNotHasKey('cvc', $payment->getAdditionalInformation());

        $params = array(
            'alias_aliasid' => '4711'
        );
        $quote = Mage::getModel('sales/quote')->load(10);
        $helper = Mage::helper('ops/alias');
        $helper->setAliasToPayment($quote->getPayment(), $params);
        $payment = $quote->getPayment();

        $this->assertArrayHasKey('alias', $payment->getAdditionalInformation());
        $this->assertEquals('4711', $payment->getAdditionalInformation('alias'));
        $this->assertArrayNotHasKey('cvc', $payment->getAdditionalInformation());

        $params = array(
            'alias_aliasid' => '4712',
            'card_cvc'   => '123'
        );

        $dataHelperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $dataHelperMock->expects($this->any())
            ->method('isAdminSession')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'ops/data', $dataHelperMock);
        $quote = Mage::getModel('sales/quote')->load(10);
        $helper = Mage::helper('ops/alias');
        $helper->setAliasToPayment($quote->getPayment(), $params);
        $payment = $quote->getPayment();
        $this->assertEquals('4712', $payment->getAdditionalInformation('alias'));
        $this->assertArrayHasKey('cvc', $payment->getAdditionalInformation());
        $this->assertEquals('123', $payment->getAdditionalInformation('cvc'));
    }
    
     /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetCardHolder()
    {
        $reflection_class = new ReflectionClass("Netresearch_OPS_Helper_Alias");
        $method = $reflection_class->getMethod('setCardHolderToAlias');
        $method->setAccessible(true);
        
        $helperObject = Mage::helper('ops/alias');
        
        $quote = Mage::getModel('sales/quote')->load(10);
        $quote->setBillingAddress($this->getAddressData());
        $quote->setShippingAddress($this->getAddressData());
        
        $oldAlias = Mage::getModel('ops/alias')->load(7);
        $aliasData = array(
            'alias_aliasid' => '4712',
            'card_cvc'   => '123',
            'Card_CardHolderName'    => 'Max Muster'
        );
        
        $method->invoke($helperObject, $quote, $aliasData);
        $updatedAlias = Mage::getModel('ops/alias')->load(7);
        $this->assertEquals($aliasData['Card_CardHolderName'], $updatedAlias->getCardHolder());
        
    }
    
    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetAliasActive()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $quote = Mage::getModel('sales/quote')->load(10);
        $quote->setBillingAddress($this->getAddressData());
        $quote->setShippingAddress($this->getAddressData());
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->setAdditionalInformation('alias', '4714');
        $payment->setAdditionalInformation('userIsRegistering', false);
        $orderPayment = $this->getModelMock('sales/order_payment', array('save'));
        $orderPayment->setAdditionalInformation('alias', '4714');
        $orderPayment->setAdditionalInformation('userIsRegistering', false);
        $quote->setPayment($payment);
        $order->setPayment($orderPayment);
        $customer = Mage::getModel('customer/customer');
        $customer->setId(1);
        $quote->setCustomer($customer);
        $billingAddressHash = Mage::helper('ops/alias')->generateAddressHash($quote->getBillingAddress());
        $shippingAddressHash = Mage::helper('ops/alias')->generateAddressHash($quote->getShippingAddress());
        $aliasesToDelete = Mage::getModel('ops/alias')
            ->getCollection()
            ->addFieldToFilter('customer_id', $quote->getCustomer()->getId())
            ->addFieldToFilter('billing_address_hash', $billingAddressHash)
            ->addFieldToFilter('shipping_address_hash', $shippingAddressHash)
            ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE);
        $oldAlias = $aliasesToDelete->getFirstItem();
        $helperMock = $this->getHelperMock('ops/alias', array('cleanUpAdditionalInformation'));
        $helperMock->setAliasActive($quote, $order);
        $aliasesToUpdate = Mage::getModel('ops/alias')
            ->getCollection()
            ->addFieldToFilter('customer_id', $quote->getCustomer()->getId())
            ->addFieldToFilter('billing_address_hash', $billingAddressHash)
            ->addFieldToFilter('shipping_address_hash', $shippingAddressHash)
            ->addFieldToFilter('alias', '4714')
            ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
            ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC);
        $newAlias = $aliasesToUpdate->getFirstItem();
        
        $testAlias = Mage::getModel('ops/alias')->load($oldAlias->getId());
        $this->assertEquals(null, $testAlias->getId());
        $this->assertEquals('active', $newAlias->getState());
    }

    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetAliasActiveWhenUserRegisters()
    {
        $order = $this->getModelMock('sales/order', array('getPayment'));
        $quote = $this->getModelMock('sales/quote', array('getPayment'));
        $quote->setBillingAddress($this->getAddressData());
        $quote->setShippingAddress($this->getAddressData());
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->setAdditionalInformation('alias', '4714');
        $payment->setAdditionalInformation('userIsRegistering', true);
        $orderPayment = $this->getModelMock('sales/order_payment', array('save'));
        $orderPayment->setAdditionalInformation('alias', '4714');
        $orderPayment->setAdditionalInformation('userIsRegistering', true);
        $quote->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));

        $order->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($orderPayment));
        $customer = Mage::getModel('customer/customer');
        $customer->setId(1);
        $quote->setCustomer($customer);
        $helperMock = $this->getHelperMock('ops/alias', array('cleanUpAdditionalInformation', 'setAliasToActiveAfterUserRegisters'));
        $helperMock->expects($this->once())
            ->method('setAliasToActiveAfterUserRegisters')
            ->with($order, $quote);

        $helperMock->setAliasActive($quote, $order);

    }
    
    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetAliasToActiveAfterUserRegisters()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $order->setCustomerId(123);
        $quote = Mage::getModel('sales/quote')->load(10);
        $quote->setBillingAddress($this->getAddressData());
        $quote->setShippingAddress($this->getAddressData());
        $quote->getPayment()->setAdditionalInformation('alias', '4714');
        $quote->getPayment()->setAdditionalInformation('opsAliasId', '11111');
        $quote->getPayment()->setAdditionalInformation('userIsRegistering', true);
        $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
        $quote->setStoreId(null);
        $billingAddressHash = Mage::helper('ops/alias')->generateAddressHash($quote->getBillingAddress());
        $shippingAddressHash = Mage::helper('ops/alias')->generateAddressHash($quote->getShippingAddress());
        
        $oldAlias = Mage::getModel('ops/alias')->getCollection()
                    ->addFieldToFilter('alias', $quote->getPayment()->getAdditionalInformation('alias'))
                    ->addFieldToFilter('billing_address_hash', $billingAddressHash)
                    ->addFieldToFilter('shipping_address_hash', $shippingAddressHash)
                    ->getFirstItem();
        
        Mage::helper('ops/alias')->setAliasToActiveAfterUserRegisters($order, $quote);
        $testAlias = Mage::getModel('ops/alias')->load($oldAlias->getId());
        $this->assertEquals(Netresearch_OPS_Model_Alias_State::ACTIVE, $testAlias->getState());
        $this->assertEquals(123, $testAlias->getCustomerId());
    }
    
    /**
     * @loadFixture ../../../var/fixtures/aliases.yaml
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testCleanUpAdditionalInformation()
    {
        $quote   = Mage::getModel('sales/quote')->load(11);
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation('cvc', 'cvctest');
        $payment->setAdditionalInformation('storedOPSId', 'storedOPSIdTest');
        
        
        
        $this->assertTrue(array_key_exists('cvc', $payment->getAdditionalInformation()));
        $this->assertTrue(array_key_exists('storedOPSId', $payment->getAdditionalInformation()));
        
        Mage::helper('ops/alias')->cleanUpAdditionalInformation($payment);
        
        $this->assertTrue(is_array($payment->getAdditionalInformation()));
        $this->assertFalse(array_key_exists('cvc', $payment->getAdditionalInformation()));
        $this->assertFalse(array_key_exists('storedOPSId', $payment->getAdditionalInformation()));

        $quote   = Mage::getModel('sales/quote')->load(11);
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation('cvc', 'cvctest');
        $payment->setAdditionalInformation('storedOPSId', 'storedOPSIdTest');



        $this->assertTrue(array_key_exists('cvc', $payment->getAdditionalInformation()));
        $this->assertTrue(array_key_exists('storedOPSId', $payment->getAdditionalInformation()));

        Mage::helper('ops/alias')->cleanUpAdditionalInformation($payment, true);

        $this->assertTrue(is_array($payment->getAdditionalInformation()));
        $this->assertFalse(array_key_exists('cvc', $payment->getAdditionalInformation()));
        $this->assertTrue(array_key_exists('storedOPSId', $payment->getAdditionalInformation()));
    }

    /**
     */
    public function testCleanUpAdditionalInformationWithSave()
    {
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->expects($this->once())
            ->method('save');

        Mage::helper('ops/alias')->cleanUpAdditionalInformation($payment, true, true);

    }
}