<?php

class Netresearch_OPS_Test_Model_ConfigTest
    extends EcomDev_PHPUnit_Test_Case_Config
{
    /**
     * @var $_model Netresearch_OPS_Model_Config
     */
    private $_model;

    public function setUp()
    {
        parent::setup();
        $this->_model = Mage::getModel('ops/config');
    }


    public function testType()
    {
        $this->assertInstanceOf('Netresearch_OPS_Model_Config', $this->_model);
    }

    public function testGetIntersolveBrands()
    {
        $path = 'payment/ops_interSolve/brands';


        Mage::getConfig()->saveConfig($path, serialize(array()));
        Mage::getConfig()->cleanCache();
        $this->assertTrue(is_array($this->_model->getIntersolveBrands(null)));

        $this->assertEquals(
            sizeof(unserialize(Mage::getStoreConfig('payment/ops_interSolve/brands', 0))),
            sizeof($this->_model->getIntersolveBrands(0))
        );


        $newVouchers = array(
            array('brand' => '1234', 'value' => '1234'),
            array('brand' => '5678', 'value' => '5678'),
            array('brand' => '9012', 'value' => '9012'),
        );

        $store = Mage::app()->getStore(0)->load(0);
        $store->setConfig($path, serialize($newVouchers));
        $this->assertEquals(
            sizeof($newVouchers),
            sizeof($this->_model->getIntersolveBrands(null))
        );
    }

    public function testGetInlinePaymentCcTypes()
    {
        $sourceModel = Mage::getModel(
            'ops/source_cc_aliasInterfaceEnabledTypes'
        );

        $pathRedirectAll = 'payment/ops_cc/redirect_all';
        $pathSpecific = 'payment/ops_cc/inline_types';
        $store = Mage::app()->getStore(0)->load(0);

        $store->resetConfig();
        $store->setConfig($pathRedirectAll, 0);
        $store->setConfig($pathSpecific, 'MasterCard,VISA');
        $this->assertEquals(
            array('MasterCard', 'VISA'),
            $this->_model->getInlinePaymentCcTypes('ops_cc')
        );

        $store->resetConfig();
        $store->setConfig($pathRedirectAll, 1);
        $store->setConfig($pathSpecific, 'MasterCard,VISA');
        $this->assertEquals(array(), $this->_model->getInlinePaymentCcTypes('ops_cc'));

        $store->resetConfig();
    }

    public function testGetGenerateHashUrl()
    {
        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with(
                'ops/alias/generatehash',
                array('_secure' => false, '_nosid' => true)
            );
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getGenerateHashUrl();

        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with(
                'ops/alias/generatehash',
                array('_secure' => false, '_nosid' => true, '_store' => 1)
            );
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getGenerateHashUrl(1);
    }

    public function testGetAliasAcceptUrl()
    {
        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with(
                'ops/alias/accept',
                array('_secure' => false, '_nosid' => true)
            );
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getAliasAcceptUrl();

        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with(
                'ops/alias/accept',
                array('_secure' => false, '_nosid' => true, '_store' => 1)
            );
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getAliasAcceptUrl(1);
    }

    public function testGetAliasExceptionUrl()
    {
        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with(
                'ops/alias/exception',
                array('_secure' => false, '_nosid' => true)
            );
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getAliasExceptionUrl();

        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with(
                'ops/alias/exception',
                array('_secure' => false, '_nosid' => true, '_store' => 1)
            );
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getAliasExceptionUrl(1);
    }

    public function testGetCcSaveAliasUrl()
    {
        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with('ops/alias/save', array('_secure' => false));
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getCcSaveAliasUrl();

        $urlModel = $this->getModelMock('core/url', array('getUrl'));
        $urlModel->expects($this->any())
            ->method('getUrl')
            ->with(
                'ops/alias/save',
                array('_secure' => false, '_store' => 1)
            );
        $this->replaceByMock('model', 'core/url', $urlModel);
        $this->_model->getCcSaveAliasUrl(1);
    }

    public function testIsAliasInfoBlockEnabled()
    {
        $path = 'payment/ops_cc/show_alias_manager_info_for_guests';
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig($path, 0);
        $this->assertFalse($this->_model->isAliasInfoBlockEnabled());

        $store->resetConfig();
        $store->setConfig($path, 1);
        $this->assertTrue($this->_model->isAliasInfoBlockEnabled());
    }


    public function testObserveCreditMemoCreation()
    {
        $this->assertEventObserverDefined(
            'adminhtml',
            'core_block_abstract_to_html_after',
            'ops/observer',
            'showWarningForClosedTransactions'
        );
    }

    public function testAppendCheckboxToRefundForm()
    {
        $this->assertEventObserverDefined(
            'adminhtml',
            'core_block_abstract_to_html_after',
            'ops/observer',
            'appendCheckBoxToRefundForm'
        );
    }

    public function testGetOrderReference()
    {
        $store = Mage::app()->getStore(0)->load(0);
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID,
            $this->_model->getOrderReference()
        );

        $store->setConfig(
            'payment_services/ops/redirectOrderReference',
            Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID,
            $this->_model->getOrderReference()
        );
    }

    public function testGetShowQuoteIdInOrderGrid()
    {
        $store = Mage::app()->getStore(0)->load(0);
        $this->assertEquals(1, $this->_model->getShowQuoteIdInOrderGrid());

        $store->setConfig('payment_services/ops/showQuoteIdInOrderGrid', 0);
        $this->assertEquals(0, $this->_model->getShowQuoteIdInOrderGrid());
    }

    public function testIsAliasManagerEnabled()
    {
        $path = 'payment/ops_cc/active_alias';
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig($path, 0);
        $this->assertFalse($this->_model->isAliasManagerEnabled('ops_cc'));

        $store->resetConfig();
        $store->setConfig($path, 1);
        $this->assertTrue($this->_model->isAliasManagerEnabled('ops_cc'));

    }


    public function getAliasUsageForNewAlias()
    {
        $path = 'payment/ops_cc/alias_usage_for_new_alias';
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig($path, 'my text goes here');

        $this->assertEquals('my text goes here', $this->_model->getAliasUsageForNewAlias('ops_cc', 0));

        $store->resetConfig();
        $store->setConfig($path, 'my text goes here two');
        $this->assertNotEquals('my text goes here', $this->_model->getAliasUsageForNewAlias('ops_cc', 0));
    }


    public function getAliasUsageForExistingAlias()
    {
        $path = 'payment/ops_cc/alias_usage_for_existing_alias';
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig($path, 'my text goes here');

        $this->assertEquals('my text goes here', $this->_model->getAliasUsageForNewAlias('ops_cc', 0));

        $store->resetConfig();
        $store->setConfig($path, 'my text goes here two');
        $this->assertNotEquals('my text goes here', $this->_model->getAliasUsageForNewAlias('ops_cc', 0));
    }

    public function testGetAcceptRedirectLocation()
    {
        $this->assertEquals(
            Netresearch_OPS_Model_Config::OPS_CONTROLLER_ROUTE_PAYMENT
            . 'accept',
            $this->_model->getAcceptRedirectRoute()
        );
    }

    public function testGetCancelRedirectLocation()
    {
        $this->assertEquals(
            Netresearch_OPS_Model_Config::OPS_CONTROLLER_ROUTE_PAYMENT
            . 'cancel',
            $this->_model->getCancelRedirectRoute()
        );
    }

    public function testGetDeclineRedirectLocation()
    {
        $this->assertEquals(
            Netresearch_OPS_Model_Config::OPS_CONTROLLER_ROUTE_PAYMENT
            . 'decline',
            $this->_model->getDeclineRedirectRoute()
        );
    }

    public function testGetExceptionRedirectLocation()
    {
        $this->assertEquals(
            Netresearch_OPS_Model_Config::OPS_CONTROLLER_ROUTE_PAYMENT
            . 'exception',
            $this->_model->getExceptionRedirectRoute()
        );
    }

    /**
     * asserts that the event for clearing old data for the payment methods is set up properly
     */
    public function testClearMethodBeforeImportEventExists()
    {
        $this->assertEventObserverDefined(
            'global',
            'sales_quote_payment_import_data_before',
            'ops/observer',
            'clearPaymentMethodFromQuote'
        );
    }


    /**
     * asserts that the event for clearing old data for the payment methods is set up properly
     */
    public function testSalesOrderPaymentCapture()
    {
        $this->assertEventObserverDefined(
            'adminhtml',
            'sales_order_payment_capture',
            'ops/observer',
            'salesOrderPaymentCapture'
        );
    }

    public function testGetMethodsRequiringAdditionalParametersFor()
    {
        $capturePms = Mage::getModel('ops/config')->getMethodsRequiringAdditionalParametersFor('capture');
        $this->assertTrue(is_array($capturePms));
        $this->assertTrue(0 < count($capturePms));
        $this->assertTrue(array_key_exists('OpenInvoiceNl', $capturePms));
        $this->assertTrue(array_key_exists('OpenInvoiceNl', $capturePms));
        $this->assertEquals('Netresearch_OPS_Model_Payment_OpenInvoiceNl', $capturePms['OpenInvoiceNl']);
    }

    public function testDisableCaptureForZeroAmountInvoiceEventExists()
    {
        $this->assertEventObserverDefined(
            'adminhtml',
            'core_block_abstract_prepare_layout_before',
            'ops/observer',
            'disableCaptureForZeroAmountInvoice'
        );
    }

    public function testGetIdealIssuers()
    {
        $issuers = $this->_model->getIDealIssuers();
        $this->assertTrue(is_array($issuers));
        $this->assertTrue(array_key_exists('ABNANL2A', $issuers));
        $this->assertEquals('ABN AMRO', $issuers['ABNANL2A']);

        $this->assertTrue(array_key_exists('RABONL2U', $issuers));
        $this->assertEquals('Rabobank', $issuers['RABONL2U']);

        $this->assertTrue(array_key_exists('INGBNL2A', $issuers));
        $this->assertEquals('ING', $issuers['INGBNL2A']);

        $this->assertTrue(array_key_exists('SNSBNL2A', $issuers));
        $this->assertEquals('SNS Bank', $issuers['SNSBNL2A']);

        $this->assertTrue(array_key_exists('RBRBNL21', $issuers));
        $this->assertEquals('Regio Bank', $issuers['RBRBNL21']);

        $this->assertTrue(array_key_exists('ASNBNL21', $issuers));
        $this->assertEquals('ASN Bank', $issuers['ASNBNL21']);

        $this->assertTrue(array_key_exists('TRIONL2U', $issuers));
        $this->assertEquals('Triodos Bank', $issuers['TRIONL2U']);

        $this->assertTrue(array_key_exists('FVLBNL22', $issuers));
        $this->assertEquals('Van Lanschot Bankiers', $issuers['FVLBNL22']);

        $this->assertTrue(array_key_exists('KNABNL2H', $issuers));
        $this->assertEquals('Knab Bank', $issuers['KNABNL2H']);
    }

    public function testAddCcPaymentMethodEventExists()
    {
        $this->assertEventObserverDefined(
            'global',
            'core_block_abstract_prepare_layout_before',
            'ops/observer',
            'addCcPaymentMethod'
        );
    }


    public function testCanSubmitExtraParameters()
    {
        $this->assertTrue($this->_model->canSubmitExtraParameter());
        $path = 'payment_services/ops/submitExtraParameters';
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig($path, 0);
        $this->assertFalse($this->_model->isAliasManagerEnabled('ops_cc'));
    }


    public function testGetParameterLengths()
    {
        $fieldLengths = $this->_model->getParameterLengths();
        $this->assertEquals($this->validFieldLengths(), $fieldLengths);
    }


    protected function validFieldLengths()
    {
        return array(
            'ECOM_SHIPTO_POSTAL_NAME_FIRST'    => 50,
            'ECOM_SHIPTO_POSTAL_NAME_LAST'     => 50,
            'ECOM_SHIPTO_POSTAL_STREET_LINE1'  => 35,
            'ECOM_SHIPTO_POSTAL_STREET_LINE2'  => 35,
            'ECOM_SHIPTO_POSTAL_STREET_LINE3'  => 35,
            'ECOM_SHIPTO_POSTAL_COUNTRYCODE'   => 2,
            'ECOM_SHIPTO_POSTAL_COUNTY'        => 25,
            'ECOM_SHIPTO_POSTAL_POSTALCODE'    => 10,
            'ECOM_SHIPTO_POSTAL_CITY'          => 25,
            'ECOM_SHIPTO_POSTAL_STREET_NUMBER' => 10,
            'CN'                               => 35,
            'OWNERZIP'                         => 10,
            'OWNERCTY'                         => 2,
            'OWNERTOWN'                        => 40,
            'OWNERTELNO'                       => 30,
            'OWNERADDRESS'                     => 35,
            'ECOM_BILLTO_POSTAL_POSTALCODE'    => 10,
            'ECOM_BILLTO_POSTAL_NAME_FIRST'    => 50,
            'ECOM_BILLTO_POSTAL_NAME_LAST'     => 50,
            'ECOM_SHIPTO_POSTAL_STATE'         => 35
        );

    }

    public function testGetInlineOrderReference()
    {
        $store = Mage::app()->getStore(0)->load(0);
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID,
            $this->_model->getInlineOrderReference()
        );

        $store->setConfig(
            'payment_services/ops/inlineOrderReference',
            Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID,
            $this->_model->getInlineOrderReference()
        );
    }

    public function testSetOrderStateDirectLinkExists()
    {
        $this->assertEventObserverDefined(
            'global',
            'sales_order_payment_place_end',
            'ops/observer',
            'setOrderStateDirectLink'
        );
    }

    public function testGetFrontendGatewayPath()
    {
        $this->setMode(Netresearch_OPS_Model_Source_Mode::CUSTOM);
        $expectedResult = Mage::getStoreConfig('payment_services/ops/frontend_gateway');
        $this->assertEquals($expectedResult, $this->_model->getFrontendGatewayPath(0));
        $this->setMode(Netresearch_OPS_Model_Source_Mode::TEST);
        $this->assertContains('test', $this->_model->getFrontendGatewayPath(0));
    }

    public function testGetDirectLinkGatewayPath()
    {
        $this->setMode(Netresearch_OPS_Model_Source_Mode::CUSTOM);
        $expectedResult = Mage::getStoreConfig('payment_services/ops/directlink_gateway');
        $this->assertEquals($expectedResult, $this->_model->getDirectLinkGatewayPath(0));
        $this->setMode(Netresearch_OPS_Model_Source_Mode::TEST);
        $this->assertContains('test', $this->_model->getDirectLinkGatewayPath(0));
    }

    public function testGetDirectLinkGatewayOrderPath()
    {
        $this->setMode(Netresearch_OPS_Model_Source_Mode::CUSTOM);
        $expectedResult = Mage::getStoreConfig('payment_services/ops/directlink_gateway_order');
        $this->assertEquals($expectedResult, $this->_model->getDirectLinkGatewayOrderPath(0));
        $this->setMode(Netresearch_OPS_Model_Source_Mode::TEST);
        $this->assertContains('test', $this->_model->getDirectLinkGatewayOrderPath(0));
    }

    public function testGetAliasGatewayUrl()
    {
        $this->setMode(Netresearch_OPS_Model_Source_Mode::CUSTOM);
        $expectedResult = Mage::getStoreConfig('payment_services/ops/ops_alias_gateway');
        $this->assertEquals($expectedResult, $this->_model->getAliasGatewayUrl(0));

        // test with standard alias gateway
        Mage::app()->getStore(0)->setConfig(
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'ops_alias_gateway_test', ''
        );
        $this->setMode(Netresearch_OPS_Model_Source_Mode::TEST);
        $this->assertContains('ncol/test', $this->_model->getAliasGatewayUrl(0));

        Mage::app()->getStore(0)->setConfig(
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'ops_alias_gateway_test', 'abc'
        );
        $this->assertEquals('abc', $this->_model->getAliasGatewayUrl(0));

        $this->setMode(Netresearch_OPS_Model_Source_Mode::PROD);
        $this->assertNotContains('ncol/prod', $this->_model->getAliasGatewayUrl(0));
    }

    public function testGetDirectLinkMaintenanceApiPath()
    {
        $this->setMode(Netresearch_OPS_Model_Source_Mode::CUSTOM);
        $expectedResult = Mage::getStoreConfig('payment_services/ops/directlink_maintenance_api');
        $this->assertEquals($expectedResult, $this->_model->getDirectLinkMaintenanceApiPath(0));
        $this->setMode(Netresearch_OPS_Model_Source_Mode::TEST);
        $this->assertContains('test', $this->_model->getDirectLinkMaintenanceApiPath(0));
    }

    public function testGetMode()
    {
        $this->setMode(Netresearch_OPS_Model_Source_Mode::CUSTOM);
        $this->assertEquals($this->_model->getMode(0), Netresearch_OPS_Model_Source_Mode::CUSTOM);
    }

    protected function setMode($mode, $storeId = 0)
    {
        Mage::app()->getStore($storeId)->setConfig(Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'mode', $mode);
    }

    public function testGetResendPaymentInfoTemplate()
    {
        $config = Mage::getModel('ops/config');
        $this->assertEquals($config->getResendPaymentInfoTemplate(), 'payment_services_ops_resendPaymentInfo_template');
    }


    public function testGetPayPerMailTemplate()
    {
        $config = Mage::getModel('ops/config');
        $this->assertEquals($config->getPayPerMailTemplate(), 'payment_services_ops_payPerMail_template');
    }


    public function testGetResendPaymentInfoIdentity()
    {
        $config = Mage::getModel('ops/config');
        $this->assertEquals($config->getResendPaymentInfoIdentity(), 'sales');
    }

    public function testGetOpsBaseUrl()
    {
        $this->setMode(Netresearch_OPS_Model_Source_Mode::TEST);
        $this->assertEquals('https://secure.domain.tld/ncol/test', $this->_model->getOpsBaseUrl(0));
        $this->setMode(Netresearch_OPS_Model_Source_Mode::PROD);
        $this->assertEquals('https://secure.domain.tld/ncol/prod', $this->_model->getOpsBaseUrl(0));
        $this->setMode(Netresearch_OPS_Model_Source_Mode::CUSTOM);
        $this->assertEmpty($this->_model->getOpsBaseUrl(0));
    }

    public function testGetAllRecurringCcTypes()
    {
        /** @var Netresearch_OPS_Model_Config $config */
        $config = Mage::getModel('ops/config');
        $ccTypes = $config->getAllRecurringCcTypes();
        $this->assertEquals(
            $ccTypes, array('American Express', 'Diners Club', 'MaestroUK', 'MasterCard', 'VISA', 'JCB')
        );
    }

    public function testGetAcceptedRecurringCcTypes()
    {
        /** @var Netresearch_OPS_Model_Config $config */
        $config = Mage::getModel('ops/config');
        $ccTypes = $config->getAcceptedRecurringCcTypes();
        $this->assertEquals(
            $ccTypes, array('American Express', 'Diners Club', 'MaestroUK', 'MasterCard', 'VISA', 'JCB')
        );
    }

    public function testGetDeviceFingerPrinting()
    {
        $config = Mage::getModel('ops/config');
        // default false
        $this->assertFalse($config->getDeviceFingerPrinting(0));
        Mage::app()->getStore(0)->setConfig(
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'device_fingerprinting', 1
        );
        $this->assertTrue($config->getDeviceFingerPrinting(0));
    }

    public function testGetTransActionTimeout()
    {
        $config = Mage::getModel('ops/config');
        // default false
        Mage::app()->getStore(0)->setConfig(Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'ops_rtimeout', 0);
        $this->assertEquals(0, $config->getTransActionTimeout(0));
        Mage::app()->getStore(0)->setConfig(Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'ops_rtimeout', 45);
        $this->assertEquals(45, $config->getTransActionTimeout(0));
    }
}

