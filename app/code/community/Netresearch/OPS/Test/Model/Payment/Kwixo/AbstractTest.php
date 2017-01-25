<?php

class Netresearch_OPS_Test_Model_Payment_Kwixo_AbstractTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testGetMethodDependendFormFields()
    {
        $order       = Mage::getModel('sales/order')->load(11);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote')
        );
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);


        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);

        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getMethodDependendFormFields($order);


        $this->assertTrue(array_key_exists('CN', $formFields));
        $this->assertTrue(array_key_exists('OWNERZIP', $formFields));
        $this->assertTrue(array_key_exists('OWNERCTY', $formFields));
        $this->assertTrue(array_key_exists('OWNERTOWN', $formFields));
        $this->assertTrue(array_key_exists('COM', $formFields));
        $this->assertTrue(array_key_exists('OWNERTELNO', $formFields));
        $this->assertTrue(array_key_exists('OWNERADDRESS', $formFields));
        $this->assertTrue(array_key_exists('BRAND', $formFields));
        $this->assertTrue(array_key_exists('ADDMATCH', $formFields));
        $this->assertTrue(
            array_key_exists('ECOM_BILLTO_POSTAL_POSTALCODE', $formFields)
        );
        $this->assertTrue(array_key_exists('CUID', $formFields));
        $this->assertTrue(
            array_key_exists('ECOM_ESTIMATEDELIVERYDATE', $formFields)
        );
        $this->assertTrue(array_key_exists('RNPOFFERT', $formFields));
        $this->assertTrue(array_key_exists('ECOM_SHIPMETHODTYPE', $formFields));
        $this->assertTrue(
            array_key_exists('ECOM_SHIPMETHODSPEED', $formFields)
        );
        $this->assertTrue(array_key_exists('ORDERID', $formFields));
        $this->assertEquals(Mage::getModel('ops/config')->getConfigData('devprefix') . $order->getQuoteId(), $formFields['ORDERID']);
    }

    /**
     *
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetMethodDependendFormFieldsWithShipmentDetails()
    {
        $order       = Mage::getModel('sales/order')->load(11);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote')
        );
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);


        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);
        $modelMock = $this->getModelMock(
            'ops/payment_kwixo_abstract', array('getShippingMethodDetails')
        );
        $modelMock->expects($this->any())
            ->method('getShippingMethodDetails')
            ->will($this->returnValue('shipping method details'));
        $formFields = $modelMock->getMethodDependendFormFields($order);
        $this->assertArrayHasKey('ECOM_SHIPMETHODDETAILS', $formFields);
        $this->assertEquals(
            'shipping method details', $formFields['ECOM_SHIPMETHODDETAILS']
        );
    }

    /**
     *
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetMethodDependendFormFieldsWithShipmentDetailsFromAddress(
    )
    {
        $order       = Mage::getModel('sales/order')->load(11);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote')
        );
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);


        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);
        $modelMock = $this->getModelMock(
            'ops/payment_kwixo_abstract',
            array('getShippingMethodDetails', 'getShippingMethodType')
        );
        $modelMock->expects($this->any())
            ->method('getShippingMethodDetails')
            ->will($this->returnValue(''));
        $modelMock->expects($this->any())
            ->method('getShippingMethodType')
            ->will($this->returnValue(4));
        $formFields = $modelMock->getMethodDependendFormFields($order);
        $this->assertArrayHasKey('ECOM_SHIPMETHODDETAILS', $formFields);
    }

    /**
     * @test
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetMethodDependendFormFieldsCheckItemProductCateg()
    {
        $order       = Mage::getModel('sales/order')->load(11);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote')
        );
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);
        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);
        $itemProductCategories  = array(
            array(
                'value' => 1,
                'label' => 'Food & gastronomy'
            ),
            array(
                'value' => 2,
                'label' => 'Car & Motorbike'
            )
        );
        $kwixoAbstractModelMock = $this->getModelMock(
            'ops/payment_kwixo_abstract', array(
                'getItemFmdProductCateg'
            )
        );
        $kwixoAbstractModelMock->expects($this->any())
            ->method('getItemFmdProductCateg')
            ->will($this->returnValue($itemProductCategories));
        $this->replaceByMock(
            'model', 'ops/payment_kwixo_abstract', $kwixoAbstractModelMock
        );
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getMethodDependendFormFields($order);
        $this->assertTrue(
            array_key_exists('ITEMFDMPRODUCTCATEG1', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ITEMFDMPRODUCTCATEG2', $formFields)
        );
    }

    /**
     * @test
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetKwixoShipToParams()
    {
        $order       = Mage::getModel('sales/order')->load(11);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote')
        );
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);
        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);
        $shippingMethodTypeValues = array(1, 2, 3, 4);
        $kwixoAbstractModelMock   = $this->getModelMock(
            'ops/payment_kwixo_abstract', array(
                'getShippingMethodTypeValues',
                'getShippingMethodType',
            )
        );
        $kwixoAbstractModelMock->expects($this->any())
            ->method('getShippingMethodTypeValues')
            ->will($this->returnValue($shippingMethodTypeValues));

        $kwixoAbstractModelMock->expects($this->any())
            ->method('getShippingMethodType')
            ->will($this->returnValue(4));


        $this->replaceByMock(
            'model', 'ops/payment_kwixo_abstract', $kwixoAbstractModelMock
        );
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getKwixoShipToParams($order);
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_NAME_FIRST', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_NAME_LAST', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_NAME_PREFIX', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_TELECOM_PHONE_NUMBER', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_STREET_LINE1', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_STREET_NUMBER', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_POSTALCODE', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_CITY', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_COUNTRYCODE', $formFields)
        );


        $order->getShippingAddress()->setStreet(
            array('An der Tabaksmühle 3a', 'Etage 4')
        );
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getKwixoShipToParams($order);
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_STREET_LINE2', $formFields)
        );
        $this->assertEquals(
            'Etage 4', $formFields['ECOM_SHIPTO_POSTAL_STREET_LINE2']
        );

        $order->getShippingAddress()->setCompany('My great company');
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getKwixoShipToParams($order);
        $this->assertTrue(array_key_exists('ECOM_SHIPTO_COMPANY', $formFields));
        $this->assertEquals(
            'My great company', $formFields['ECOM_SHIPTO_COMPANY']
        );

        $order->getShippingAddress()->setFax('4711');
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getKwixoShipToParams($order);
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_TELECOM_FAX_NUMBER', $formFields)
        );
        $this->assertEquals(
            '4711', $formFields['ECOM_SHIPTO_TELECOM_FAX_NUMBER']
        );


        $order->getShippingAddress()->setAddressType('shipping2');
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getKwixoShipToParams($order);
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_NAME_FIRST', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_NAME_LAST', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_NAME_PREFIX', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_TELECOM_PHONE_NUMBER', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_STREET_LINE1', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_STREET_NUMBER', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_POSTALCODE', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_CITY', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_SHIPTO_POSTAL_COUNTRYCODE', $formFields)
        );

    }

    /**
     * @test
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetKwixoBillToParams()
    {
        $order       = Mage::getModel('sales/order')->load(11);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote')
        );
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);
        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);

        $addressData = array(
            'housenumber' => 44,
            'street'      => 'teststreet'
        );


        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getKwixoBillToParams($order);

        $this->assertTrue(
            array_key_exists('ECOM_BILLTO_POSTAL_NAME_FIRST', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_BILLTO_POSTAL_NAME_LAST', $formFields)
        );
        $this->assertTrue(
            array_key_exists('ECOM_BILLTO_POSTAL_STREET_NUMBER', $formFields)
        );
        $order->getBillingAddress()->setStreet(
            array('An der Tabaksmühle 3a', 'Etage 4')
        );
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getKwixoBillToParams($order);
        $this->assertTrue(array_key_exists('OWNERADDRESS2', $formFields));
        $this->assertEquals('Etage 4', $formFields['OWNERADDRESS2']);
    }

    public function testGetRnpFee()
    {

        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig('payment/ops_kwixoCredit/rnp_fee', 1);
        $this->assertEquals(
            1, Mage::getModel('ops/payment_kwixo_abstract')->getRnpFee(
                'ops_kwixoCredit', 0
            )
        );
        $store->setConfig('payment/ops_kwixoCredit/rnp_fee', 0);
        $this->assertEquals(
            0, Mage::getModel('ops/payment_kwixo_abstract')->getRnpFee(
                'ops_kwixoCredit', 0
            )
        );
        $store->resetConfig();
        $store->setConfig('payment/ops_kwixoApresReception/rnp_fee', 1);
        $this->assertEquals(
            1, Mage::getModel('ops/payment_kwixo_abstract')->getRnpFee(
                'ops_kwixoApresReception', 0
            )
        );
        $store->setConfig('payment/ops_kwixoApresReception/rnp_fee', 0);
        $this->assertEquals(
            0, Mage::getModel('ops/payment_kwixo_abstract')->getRnpFee(
                'ops_kwixoApresReception', 0
            )
        );
        $store->resetConfig();
        $store->setConfig('payment/ops_kwixoComptant/rnp_fee', 1);
        $this->assertEquals(
            1, Mage::getModel('ops/payment_kwixo_abstract')->getRnpFee(
                'ops_kwixoComptant', 0
            )
        );
        $store->setConfig('payment/ops_kwixoComptant/rnp_fee', 0);
        $this->assertEquals(
            0, Mage::getModel('ops/payment_kwixo_abstract')->getRnpFee(
                'ops_kwixoComptant', 0
            )
        );
    }

    public function testGetShippingMethodType()
    {
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoCredit/ecom_shipMethodType', 'Test'
        );
        $this->assertEquals(
            'Test',
            Mage::getModel('ops/payment_kwixo_abstract')->getShippingMethodType(
                'ops_kwixoCredit', 0
            )
        );

        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoApresReception/ecom_shipMethodType', 'Test1'
        );
        $this->assertEquals(
            'Test1',
            Mage::getModel('ops/payment_kwixo_abstract')->getShippingMethodType(
                'ops_kwixoApresReception', 0
            )
        );

        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoComptant/ecom_shipMethodType', 'Test2'
        );
        $this->assertEquals(
            'Test2',
            Mage::getModel('ops/payment_kwixo_abstract')->getShippingMethodType(
                'ops_kwixoComptant', 0
            )
        );

        $this->assertEquals(
            Netresearch_OPS_Model_Source_Kwixo_ShipMethodType::DOWNLOAD,
            Mage::getModel('ops/payment_kwixo_abstract')->getShippingMethodType(
                'ops_kwixoComptant', 0, true
            )
        );

        $kwixoShippingMock = $this->getModelMock(
            'ops/kwixo_shipping_setting', array('getKwixoShippingType')
        );
        $kwixoShippingMock->expects($this->any())
            ->method('getKwixoShippingType')
            ->will($this->returnValue(123));
        $model = Mage::getModel('ops/payment_kwixo_abstract');
        $model->setKwixoShippingModel($kwixoShippingMock);
        $this->assertEquals(
            123, $model->getShippingMethodType(
                'ops_kwixoComptant'
            )
        );
    }

    public function testGetShippingMethodSpeed()
    {
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig('payment/ops_kwixoCredit/ecom_shipMethodSpeed', 25);
        $this->assertEquals(
            25, Mage::getModel('ops/payment_kwixo_abstract')
                ->getShippingMethodSpeed('ops_kwixoCredit', 0)
        );

        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoApresReception/ecom_shipMethodSpeed', 28
        );
        $this->assertEquals(
            28, Mage::getModel('ops/payment_kwixo_abstract')
                ->getShippingMethodSpeed('ops_kwixoApresReception', 0)
        );

        $store->resetConfig();
        $store->setConfig('payment/ops_kwixoComptant/ecom_shipMethodSpeed', 32);
        $this->assertEquals(
            32, Mage::getModel('ops/payment_kwixo_abstract')
                ->getShippingMethodSpeed('ops_kwixoComptant', 0)
        );

        $kwixoShippingMock = $this->getModelMock(
            'ops/kwixo_shipping_setting', array('getKwixoShippingMethodSpeed')
        );
        $kwixoShippingMock->expects($this->any())
            ->method('getKwixoShippingMethodSpeed')
            ->will($this->returnValue(123));
        $model = Mage::getModel('ops/payment_kwixo_abstract');
        $model->setKwixoShippingModel($kwixoShippingMock);
        $this->assertEquals(
            123, $model->getShippingMethodSpeed(
                'ops_kwixoComptant'
            )
        );
    }

    public function testGetItemFmdProductCateg()
    {
        $itemProductCategories = array(
            array(
                'value' => 1,
                'label' => 'Food & gastronomy'
            ),
            array(
                'value' => 2,
                'label' => 'Car & Motorbike'
            )
        );
        $store                 = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig('payment/ops_kwixoCredit/product_categories', 'Cat1');
        $this->assertTrue(
            in_array(
                'Cat1', Mage::getModel('ops/payment_kwixo_abstract')
                    ->getItemFmdProductCateg('ops_kwixoCredit', 0)
            )
        );

        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoApresReception/product_categories', 'Cat2'
        );
        $this->assertTrue(
            in_array(
                'Cat2', Mage::getModel('ops/payment_kwixo_abstract')
                    ->getItemFmdProductCateg('ops_kwixoApresReception', 0)
            )
        );

        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoComptant/product_categories', 'Cat3'
        );
        $this->assertTrue(
            in_array(
                'Cat3', Mage::getModel('ops/payment_kwixo_abstract')
                    ->getItemFmdProductCateg('ops_kwixoComptant', 0)
            )
        );
    }

    public function testGetShippingMethodDetails()
    {
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoCredit/shiping_method_details', 'Cat1'
        );
        $this->assertEquals(
            'Cat1', Mage::getModel('ops/payment_kwixo_abstract')
                ->getShippingMethodDetails('ops_kwixoCredit', 0)
        );

        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoApresReception/shiping_method_details', 'Cat2'
        );
        $this->assertEquals(
            'Cat2', Mage::getModel('ops/payment_kwixo_abstract')
                ->getShippingMethodDetails('ops_kwixoApresReception', 0)
        );

        $store->resetConfig();
        $store->setConfig(
            'payment/ops_kwixoComptant/shiping_method_details', 'Cat3'
        );
        $this->assertEquals(
            'Cat3', Mage::getModel('ops/payment_kwixo_abstract')
                ->getShippingMethodDetails('ops_kwixoComptant', 0)
        );

        $kwixoShippingMock = $this->getModelMock(
            'ops/kwixo_shipping_setting', array('getKwixoShippingDetails')
        );
        $kwixoShippingMock->expects($this->any())
            ->method('getKwixoShippingDetails')
            ->will($this->returnValue('shipping details'));
        $model = Mage::getModel('ops/payment_kwixo_abstract');
        $model->setKwixoShippingModel($kwixoShippingMock);
        $this->assertEquals(
            'shipping details', $model->getShippingMethodDetails(
                'ops_kwixoComptant'
            )
        );
    }

    public function testGetQuestion()
    {
        $order  = new Varien_Object();
        $params = array();
        $this->assertEquals(
            'Please make sure that the displayed data is correct.',
            Mage::getModel('ops/payment_kwixo_abstract')->getQuestion());
    }

    public function testGetQuestionedFormFields()
    {
        $order  = new Varien_Object();
        $params = array();
        $fields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getQuestionedFormFields($order);
        $this->assertTrue(in_array('OWNERADDRESS', $fields));
        $this->assertTrue(
            in_array('ECOM_BILLTO_POSTAL_STREET_NUMBER', $fields)
        );
    }

    public function testGetQuestionedFormFieldsForAddrFields()
    {
        $order     = $this->getModelMock('sales/order');
        $params    = array();
        $modelMock = $this->getModelMock(
            'ops/payment_kwixo_abstract',
            array('getShippingMethodTypeValues', 'getShippingMethodType')
        );
        $modelMock->expects($this->any())
            ->method('getShippingMethodTypeValues')
            ->will($this->returnValue(array(4)));
        $modelMock->expects($this->any())
            ->method('getShippingMethodType')
            ->will($this->returnValue(4));

        $fields = $modelMock->getQuestionedFormFields($order, $params);
        $this->assertTrue(in_array('OWNERADDRESS', $fields));
        $this->assertTrue(
            in_array('ECOM_BILLTO_POSTAL_STREET_NUMBER', $fields)
        );
        $this->assertTrue(
            in_array('ECOM_SHIPTO_POSTAL_STREET_NUMBER', $fields)
        );
        $this->assertTrue(
            in_array('ECOM_SHIPTO_TELECOM_PHONE_NUMBER', $fields)
        );
    }


    public function testPopulateFromArray()
    {
        $kwixoMock = $this->getModelMock(
            'ops/payment_kwixo_abstract', array('getQuestionedFormFields')
        );
        $kwixoMock->expects($this->any())
            ->method('getQuestionedFormFields')
            ->will(
                $this->returnValue(
                    array('OWNERADDRESS', 'ECOM_BILLTO_POSTAL_STREET_NUMBER')
                )
            );
        $reflectionClass = new ReflectionClass(get_class($kwixoMock));
        $method          = $reflectionClass->getMethod("populateFromArray");
        $method->setAccessible(true);
        $formFields = array(
            'OWNERADDRESS'                     => 'bla',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER' => 'blub'
        );
        $order      = Mage::getModel('sales/order');
        $this->assertEquals(
            $formFields, $method->invoke($kwixoMock, $formFields, null, $order)
        );
        $this->assertEquals(
            $formFields,
            $method->invoke($kwixoMock, $formFields, array(), $order)
        );
        // $this->assertEquals($formFields, $method->invoke($kwixoMock, $formFields , array('wusel', 'dusel')));

        $questionedFields = array(
            'OWNERADDRESS'                     => 'blub',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER' => 'bla'
        );
        $order            = Mage::getModel('sales/order');
        $this->assertEquals(
            $questionedFields,
            $method->invoke($kwixoMock, $formFields, $questionedFields, $order)
        );
    }

    /**
     * @test
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetItemParams()
    {
        $fakeProduct = Mage::getModel('catalog/product');
        $fakeProduct->setCategoryIds(array(1, 2));
        $productMock = $this->getModelMock('catalog/product', array('load'));
        $productMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($fakeProduct));
        $this->replaceByMock('model', 'catalog/product', $productMock);
        $fakeKwixoCategory = new Varien_Object();
        $fakeKwixoCategory->setId(1);
        $fakeKwixoCategory->setKwixoCategoryId(123);
        $kwixoMapping = $this->getModelMock(
            'ops/kwixo_category_mapping', array('loadByCategoryId')
        );
        $kwixoMapping->expects($this->any())
            ->method('loadByCategoryId')
            ->will($this->returnValue($fakeKwixoCategory));
        $this->replaceByMock(
            'model', 'ops/kwixo_category_mapping', $kwixoMapping
        );
        $order      = Mage::getModel('sales/order')->load(11);
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getItemParams($order);
        $orderItems = $order->getAllItems();
        $i          = 1;


        foreach ($orderItems as $orderItem) {
            $this->assertTrue(array_key_exists('ITEMID' . $i, $formFields));
            $this->assertEquals(
                $orderItem->getId(), $formFields['ITEMID' . $i]
            );

            $this->assertTrue(array_key_exists('ITEMNAME' . $i, $formFields));
            $this->assertEquals(
                $orderItem->getName(), $formFields['ITEMNAME' . $i]
            );

            $this->assertTrue(array_key_exists('ITEMPRICE' . $i, $formFields));
            $this->assertEquals(
                $orderItem->getBasePrice(), $formFields['ITEMPRICE' . $i]
            );

            $this->assertTrue(array_key_exists('ITEMQUANT' . $i, $formFields));
            $this->assertEquals(
                $orderItem->getQtyOrdered(), $formFields['ITEMQUANT' . $i]
            );

            $this->assertTrue(array_key_exists('ITEMVAT' . $i, $formFields));
            $this->assertEquals(
                $orderItem->getBaseTaxAmount(), $formFields['ITEMVAT' . $i]
            );

            $i++;
        }


        $fakeItem = new Varien_Object();
        $fakeItem->setParentItemId(1);
        $fakeOrder = $this->getModelMock('sales/order', array('getAllItems'));
        $fakeOrder->expects($this->any())
            ->method('getAllItems')
            ->will($this->returnValue(array($fakeItem)));
        $formFields = Mage::getModel('ops/payment_kwixo_abstract')
            ->getItemParams($fakeOrder);
        $this->assertArrayNotHasKey('ITEMID0', $formFields);


    }
}
