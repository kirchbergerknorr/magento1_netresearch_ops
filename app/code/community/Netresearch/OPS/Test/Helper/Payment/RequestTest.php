<?php

class Netresearch_OPS_Test_Helper_Payment_RequestTest extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @return Netresearch_OPS_Helper_Payment_RequestTest
     */
    protected function getRequestHelper()
    {
        return Mage::helper('ops/payment_request');
    }

    protected function getShipToArrayKeys()
    {
        return array(
            'ECOM_SHIPTO_POSTAL_NAME_FIRST',
            'ECOM_SHIPTO_POSTAL_NAME_LAST',
            'ECOM_SHIPTO_POSTAL_STREET_LINE1',
            'ECOM_SHIPTO_POSTAL_STREET_LINE2',
            'ECOM_SHIPTO_POSTAL_COUNTRYCODE',
            'ECOM_SHIPTO_POSTAL_CITY',
            'ECOM_SHIPTO_POSTAL_POSTALCODE',
            'ECOM_SHIPTO_POSTAL_STATE',
        );
    }

    public function testExtractShipToParameters()
    {
        $address = Mage::getModel('sales/quote_address');
        $helper = $this->getRequestHelper();
        $configMock = $this->getModelMock('ops/config', array('canSubmitExtraParameter'));
        $configMock->expects($this->any())
            ->method('canSubmitExtraParameter')
            ->will($this->returnValue(true));
        $helper->setConfig($configMock);
        $params = $helper->extractShipToParameters($address, Mage::getModel('sales/quote'));
        $this->assertTrue(is_array($params));
        foreach ($this->getShipToArrayKeys() as $key) {
            $this->assertArrayHasKey($key, $params);
        }

        $address->setFirstname('Hans');
        $address->setLastname('Wurst');
        $address->setStreet('Nonnenstrasse 11d');
        $address->setCountry('DE');
        $address->setCity('Leipzig');
        $address->setPostcode('04229');
        $params = $this->getRequestHelper()->extractShipToParameters($address, Mage::getModel('sales/quote'));
        $this->assertEquals('Hans', $params['ECOM_SHIPTO_POSTAL_NAME_FIRST']);
        $this->assertEquals('Wurst', $params['ECOM_SHIPTO_POSTAL_NAME_LAST']);
        $this->assertEquals('Nonnenstrasse' , $params['ECOM_SHIPTO_POSTAL_STREET_LINE1']);
        $this->assertEquals('Nonnenstrasse' , $params['ECOM_SHIPTO_POSTAL_STREET_LINE1']);
        $this->assertEquals('', $params['ECOM_SHIPTO_POSTAL_STREET_LINE2']);
        $this->assertEquals('DE', $params['ECOM_SHIPTO_POSTAL_COUNTRYCODE']);
        $this->assertEquals('Leipzig', $params['ECOM_SHIPTO_POSTAL_CITY']);
        $this->assertEquals('04229', $params['ECOM_SHIPTO_POSTAL_POSTALCODE']);
        $this->assertEquals('11d', $params['ECOM_SHIPTO_POSTAL_STREET_NUMBER']);

    }

    public function testGetIsoRegionCodeWithIsoRegionCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('SN');
        $address->setCountry('DE');
        $this->assertEquals('SN', $this->getRequestHelper()->getIsoRegionCode($address));
    }

    public function testGetIsoRegionCodeWithIsoRegionCodeContainingTheCountryCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('ES-AB');
        $address->setCountry('ES');
        $this->assertEquals('AB', $this->getRequestHelper()->getIsoRegionCode($address));
    }


    public function testGetIsoRegionCodeWithGermanMageRegionCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('SAS');
        $address->setCountry('DE');
        $this->assertEquals('SN', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('NDS');
        $this->assertEquals('NI', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('THE');
        $this->assertEquals('TH', $this->getRequestHelper()->getIsoRegionCode($address));
    }

    public function testGetIsoRegionCodeWithAustrianMageRegionCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('WI');
        $address->setCountry('AT');
        $this->assertEquals('9', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('NO');
        $this->assertEquals('3', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('VB');
        $this->assertEquals('8', $this->getRequestHelper()->getIsoRegionCode($address));
    }

    public function testGetIsoRegionCodeWithSpanishMageRegionCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('A Coruсa');
        $address->setCountry('ES');
        $this->assertEquals('C', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('Barcelona');
        $this->assertEquals('B', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('Madrid');
        $this->assertEquals('M', $this->getRequestHelper()->getIsoRegionCode($address));
    }

    public function testGetIsoRegionCodeWithFinnishMageRegionCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('Lappi');
        $address->setCountry('FI');
        $this->assertEquals('10', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('Etelä-Savo');
        $this->assertEquals('04', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('Itä-Uusimaa');
        $this->assertEquals('19', $this->getRequestHelper()->getIsoRegionCode($address));
    }

    public function testGetIsoRegionCodeWithLatvianMageRegionCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('Ādažu novads');
        $address->setCountry('LV');
        $this->assertEquals('LV', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('Engures novads');
        $this->assertEquals('029', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('Viļakas novads');
        $this->assertEquals('108', $this->getRequestHelper()->getIsoRegionCode($address));
    }

    public function testGetIsoRegionCodeWithUnknownRegionCode()
    {
        $address = Mage::getModel('customer/address');
        $address->setRegionCode('DEFG');
        $address->setCountry('AB');
        $this->assertEquals('AB', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('DEF');
        $this->assertEquals('DEF', $this->getRequestHelper()->getIsoRegionCode($address));
        $address->setRegionCode('DF');
        $this->assertEquals('DF', $this->getRequestHelper()->getIsoRegionCode($address));
    }

    public function testGetTemplateParamsIframeMode()
    {
        $config = $this->getModelMock('ops/config', array('getConfigData'));
        $config->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_IFRAME));
        $helper = Mage::helper('ops/payment_request');
        $helper->setConfig($config);

        $params = $helper->getTemplateParams();

        $this->assertArrayHasKey('PARAMPLUS', $params);
        $this->assertEquals('IFRAME=1', $params['PARAMPLUS']);
        $this->assertArrayHasKey('TITLE', $params);
        $this->assertArrayNotHasKey('TP', $params);
    }

    public function testGetTemplateParamsNoMode()
    {
        $config = $this->getModelMock('ops/config', array('getConfigData'));
        $config->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(null));
        $helper = Mage::helper('ops/payment_request');
        $helper->setConfig($config);

        $params = $helper->getTemplateParams();

        $this->assertArrayNotHasKey('PARAMPLUS', $params);
        $this->assertArrayNotHasKey('TITLE', $params);
        $this->assertArrayNotHasKey('TP', $params);

    }

    public function testGetTemplateParamsRedirectMode()
    {
        $config = $this->getModelMock('ops/config', array('getConfigData'));
        $config->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_REDIRECT));
        $helper = Mage::helper('ops/payment_request');
        $helper->setConfig($config);

        $params = $helper->getTemplateParams();
        $this->assertArrayNotHasKey('PARAMPLUS', $params);
        $this->assertArrayHasKey('TITLE', $params);
        $this->assertArrayNotHasKey('TP', $params);
    }

    public function testExtractOrderItemParametersWithAllItems()
    {
        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        // setup one item
        $item = Mage::getModel('sales/order_item');
        $item->setId(1);
        $item->setName('Item');
        $item->setBasePriceInclTax(10.00);
        $item->setQtyOrdered(1);
        $item->setTaxPercent(19.00);
        $item->setProductType(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

        $order = $this->getModelMock('sales/order', array('getAllItems'));
        $order->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue(array($item)));

        // prepare discount item
        $order->setBaseDiscountAmount(1.00)
            ->setCouponRuleName('DISCOUNT');

        //prepare shipping Item
        $order->setShippingDescription('SHIPPING')
            ->setBaseShippingInclTax(5.00)
            ->setIsVirtual(0);

        $helper = Mage::helper('ops/payment_request');

        $formFields = $helper->extractOrderItemParameters($order);

        $this->assertArrayHasKey('ITEMID1', $formFields);
        $this->assertArrayHasKey('ITEMID2', $formFields);
        $this->assertArrayHasKey('ITEMID3', $formFields);
    }

    public function testExtractOrderItemParametersWithNoItems()
    {
        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        // setup one item
        $item = Mage::getModel('sales/order_item');
        $item->setProductType(Mage_Catalog_Model_Product_Type::TYPE_BUNDLE);

        $order = $this->getModelMock('sales/order', array('getAllItems'));
        $order->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue(array($item)));

        // prepare discount item
        $order->setBaseDiscountAmount(0.00);

        //prepare shipping Item
        $order->setIsVirtual(true);

        $helper = Mage::helper('ops/payment_request');

        $formFields = $helper->extractOrderItemParameters($order);

        $this->assertArrayNotHasKey('ITEMID1', $formFields);
        $this->assertArrayNotHasKey('ITEMID2', $formFields);
        $this->assertArrayNotHasKey('ITEMID3', $formFields);
    }

    public function testGetTemplateParamsTemplateMode()
    {
        $config = $this->getModelMock('ops/config', array('getConfigData'));
        $config->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_TEMPLATE));
        $helper = Mage::helper('ops/payment_request');
        $helper->setConfig($config);

        $params = $helper->getTemplateParams();
        $this->assertArrayNotHasKey('PARAMPLUS', $params);
        $this->assertArrayNotHasKey('TITLE', $params);
        $this->assertArrayHasKey('TP', $params);

    }

    public function testGetTemplateParamsInternalTemplateMode()
    {
        $config = $this->getModelMock('ops/config', array('getConfigData'));
        $config->expects($this->any())
            ->method('getConfigData')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_MAGENTO_INTERNAL));
        $helper = Mage::helper('ops/payment_request');
        $helper->setConfig($config);

        $params = $helper->getTemplateParams();
        $this->assertArrayNotHasKey('PARAMPLUS', $params);
        $this->assertArrayNotHasKey('TITLE', $params);
        $this->assertArrayHasKey('TP', $params);
        $this->assertEquals($config->getPayPageTemplate(), $params['TP']);
    }

    public function testGetMandatoryRequestFieldsWithFPActiveOff()
    {
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_cc');
        $order = Mage::getModel('sales/order');
        $order->setStoreId(0)->setPayment($payment);
        $subject = Mage::helper('ops/payment_request');
        $sessionMock = $this->mockSession('customer/session', array('getData'));
        $sessionMock->expects($this->any())
            ->method('getData')
            ->with(Netresearch_OPS_Model_Payment_Abstract::FINGERPRINT_CONSENT_SESSION_KEY)
            ->will($this->returnValue(true));

        $params = $subject->getMandatoryRequestFields($order);

        $this->assertEquals(0, $params['FP_ACTIV']);

    }

}
