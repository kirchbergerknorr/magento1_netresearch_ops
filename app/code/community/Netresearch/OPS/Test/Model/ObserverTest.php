<?php

class Netresearch_OPS_Test_Model_ObserverTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{
    private $_model;

    public function setUp()
    {
        parent::setup();
        $this->_model = Mage::getModel('ops/observer');
    }

    public function testType()
    {
        $this->assertInstanceOf('Netresearch_OPS_Model_Observer', $this->_model);
    }

    public function testPerformDirectLinkRequestWithUnknownResponse()
    {
        $quote = $this->getModelMock('sales/quote', array('save'));
        $aliasHelperMock = $this->getHelperMock('ops/alias', array('setAliasActive'));
        $this->replaceByMock('helper', 'ops/alias', $aliasHelperMock);
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->expects($this->any())
                ->method('save')
                ->will($this->returnValue(null));
        $this->replaceByMock('model', 'sales/quote_payment', $payment);
        $quote->setPayment($payment);
        $response = null;
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkMock->expects($this->any())
                       ->method('performRequest')
                       ->will($this->returnValue($response));
        $this->replaceByMock('model', 'ops/api_directlink', $directLinkMock);
        $observer = Mage::getModel('ops/observer');
        $observer->performDirectLinkRequest($quote, array());
        $this->assertFalse($this->setExpectedException('PHPUnit_Framework_ExpectationFailedException'));
        $this->assertTrue(array_key_exists('ops_response', $quote->getPayment()->getAdditionalInformation()));
    }

    public function testPerformDirectLinkRequestWithInvalidResponse()
    {
        $quote = new Varien_Object();
        $aliasHelperMock = $this->getHelperMock('ops/alias', array('setAliasActive'));
        $this->replaceByMock('helper', 'ops/alias', $aliasHelperMock);
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->expects($this->any())
                ->method('save')
                ->will($this->returnValue(null));
        $this->replaceByMock('model', 'sales/quote_payment', $payment);
        $quote->setPayment($payment);
        $response = '';
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkMock->expects($this->any())
                       ->method('performRequest')
                       ->will($this->returnValue($response));
        $this->replaceByMock('model', 'ops/api_directlink', $directLinkMock);
        $observer = Mage::getModel('ops/observer');
        $this->assertTrue($this->setExpectedException('PHPUnit_Framework_ExpectationFailedException'));
        $observer->performDirectLinkRequest($quote, array());
        $this->assertFalse(array_key_exists('ops_response', $quote->getPayment()->getAdditionalInformation()));
    }

    public function testPerformDirectLinkRequestWithValidResponse()
    {
        $quote = new Varien_Object();
        $aliasHelperMock = $this->getHelperMock('ops/alias', array('setAliasActive'));
        $this->replaceByMock('helper', 'ops/alias', $aliasHelperMock);
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->expects($this->any())
                ->method('save')
                ->will($this->returnValue(null));
        $this->replaceByMock('model', 'sales/quote_payment', $payment);
        $quote->setPayment($payment);
        $response = array('STATUS' => Netresearch_OPS_Model_Status::AUTHORIZED);
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkMock->expects($this->any())
                       ->method('performRequest')
                       ->will($this->returnValue($response));
        $this->replaceByMock('model', 'ops/api_directlink', $directLinkMock);
        $observer = Mage::getModel('ops/observer');
        $this->assertFalse($this->setExpectedException('PHPUnit_Framework_ExpectationFailedException'));
        $observer->performDirectLinkRequest($quote, array());
        $this->assertTrue(array_key_exists('ops_response', $quote->getPayment()->getAdditionalInformation()));
    }

    public function testPerformDirectLinkRequestWithValidResponseButInvalidStatus()
    {
        $quote = new Varien_Object();
        $aliasHelperMock = $this->getHelperMock('ops/alias', array('setAliasActive'));
        $this->replaceByMock('helper', 'ops/alias', $aliasHelperMock);
        $payment = $this->getModelMock('sales/quote_payment', array('save'));
        $payment->expects($this->any())
                ->method('save')
                ->will($this->returnValue(null));
        $this->replaceByMock('model', 'sales/quote_payment', $payment);
        $quote->setPayment($payment);
        $response = array('STATUS' => Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED);
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkMock->expects($this->any())
                       ->method('performRequest')
                       ->will($this->returnValue($response));
        $this->replaceByMock('model', 'ops/api_directlink', $directLinkMock);
        $observer = Mage::getModel('ops/observer');
        $this->assertTrue($this->setExpectedException('PHPUnit_Framework_ExpectationFailedException'));
        $observer->performDirectLinkRequest($quote, array());
        $this->assertFalse(array_key_exists('ops_response', $quote->getPayment()->getAdditionalInformation()));
    }

    public function testShowWarningForClosedTransactions()
    {
        Mage::register('current_creditmemo', null);
        $transport = new Varien_Object();
        $transport->setHtml('Foo');
        $observer = Mage::getModel('ops/observer');
        $event = new Varien_Object();
        $event->setBlock('');
        $this->assertEquals('', $observer->showWarningForClosedTransactions($event));

        $order = new Varien_Object();
        $payment = new Varien_Object();
        $methodInstance = Mage::getModel('ops/payment_cc');
        $payment->setMethodInstance($methodInstance);
        $order->setPayment($payment);
        $invoice = new Varien_Object();
        $invoice->setTransactionId(1);
        $creditMemo = $this->getModelMock(
            'sales/order_creditmemo',
            array('getOrder', 'getInvoice', 'canRefund', 'getOrderId')
        );
        $creditMemo->expects($this->any())
                   ->method('getOrder')
                   ->will($this->returnValue($order));
        $creditMemo->expects($this->any())
                   ->method('getInvoice')
                   ->will($this->returnValue($invoice));
        $creditMemo->expects($this->any())
                   ->method('canRefund')
                   ->will($this->returnValue(false));
        $creditMemo->expects($this->any())
                   ->method('getOrderId')
                   ->will($this->returnValue(1));
        Mage::register('current_creditmemo', $creditMemo);
        $block = Mage::app()->getLayout()->getBlockSingleton('adminhtml/sales_order_creditmemo_create');

        $blockMock = $this->getBlockMock(
            'ops/adminhtml_sales_order_creditmemo_closedTransaction_warning',
            array('renderView')
        );
        $blockMock->expects($this->once())
                  ->method('renderView')
                  ->will($this->returnValue('<b>warning</b>'));
        $this->replaceByMock('block', 'ops/adminhtml_sales_order_creditmemo_closedTransaction_warning', $blockMock);
        $event->setBlock($block);
        $event->setTransport($transport);
        $html = $observer->showWarningForClosedTransactions($event);
        $this->assertEquals('<b>warning</b>Foo', $html);
        $this->assertNotEquals('Bar<span>warning</span>', $html);

        Mage::unregister('current_creditmemo');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testConfirmAliasPayment()
    {
        $quote = Mage::getModel('sales/quote')->load(23);
        $order = Mage::getModel('sales/order')->load(11);
        $payment = $quote->getPayment();
//        $payment->expects($this->any())
//            ->method('getMethodInstance')
//            ->will($this->returnValue(Mage::getModel('ops/payment_cc')));
        $payment->setAdditionalInformation(array('cvc' => '123', 'alias' => '99'));
        $payment->setMethod('ops_cc');
        $quote->setPayment($payment);
        $requestParams = $this->getRequestParamsWithAlias($quote, $order);

        $helperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $helperMock->expects($this->any())
                   ->method('isAdminSession')
                   ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'ops/data', $helperMock);

        $quoteHelperMock = $this->getHelperMock('ops/quote', array('getQuoteCurrency'));
        $quoteHelperMock->expects($this->any())
                        ->method('getQuoteCurrency')
                        ->will($this->returnValue('USD'));
        $this->replaceByMock('helper', 'ops/quote', $quoteHelperMock);

        $observerMock = $this->getModelMock(
            'ops/observer',
            array('performDirectLinkRequest', 'invokeRequestParamValidation')
        );
        $observerMock->expects($this->any())
                     ->method('performDirectLinkRequest')
                     ->will($this->returnValue('WuselDusel'));

        $orderHelperMock = $this->getHelperMock('ops/order', array('checkIfAddressesAreSame'));
        $orderHelperMock->expects($this->any())
                        ->method('checkIfAddressesAreSame')
                        ->will($this->returnValue(1));
        $this->replaceByMock('helper', 'ops/order', $orderHelperMock);

        $customerSessionMock = $this->getModelMock('customer/session', array('isLoggedIn'));
        $customerSessionMock->expects($this->any())
                            ->method('isLoggedIn')
                            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $customerSessionMock);

        $configModelMock = $this->getModelMock(
            'ops/config', array(
                'get3dSecureIsActive',
                'getAcceptUrl',
                'getDeclineUrl',
                'getExceptionUrl'
            )
        );

        $configModelMock->expects($this->any())
                        ->method('get3dSecureIsActive')
                        ->will($this->returnValue(true));
        $configModelMock->expects($this->any())
                        ->method('getAcceptUrl')
                        ->will($this->returnValue('www.abc.com'));
        $configModelMock->expects($this->any())
                        ->method('getDeclineUrl')
                        ->will($this->returnValue('www.abcd.com'));
        $configModelMock->expects($this->any())
                        ->method('getExceptionUrl')
                        ->will($this->returnValue('www.abcde.com'));
        $this->replaceByMock('model', 'ops/config', $configModelMock);

        $aliashelperMock = $this->getHelperMock('ops/alias', array('getAlias', 'cleanUpAdditionalInformation'));
        $aliashelperMock->expects($this->any())
                        ->method('getAlias')
                        ->with($quote)
                        ->will($this->returnValue('99'));
        $this->replaceByMock('helper', 'ops/alias', $aliashelperMock);
        $this->assertEquals('WuselDusel', $observerMock->confirmAliasPayment($order, $quote));

        $helperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $helperMock->expects($this->any())
                   ->method('isAdminSession')
                   ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'ops/data', $helperMock);

        $observerMock = $this->getModelMock(
            'ops/observer',
            array('performDirectLinkRequest', 'invokeRequestParamValidation')
        );

        $requestParams = $this->getRequestParamsWithoutAlias($quote, $order);
        $observerMock->expects($this->any())
                     ->method('performDirectLinkRequest')
                     ->will($this->returnValue('wrong'));
        $this->assertEquals('wrong', $observerMock->confirmAliasPayment($order, $quote));


        $observerMock = $this->getModelMock('ops/observer', array('performDirectLinkRequest'));
        $validatorMock = $this->getModelMock('ops/validator_parameter_validator', array('isValid'));
        $validatorMock->expects($this->once())
                      ->method('isValid')
                      ->will($this->returnValue(false));
        $validatorFactoryMock = $this->getModelMock('ops/validator_parameter_factory', array('getValidatorFor'));
        $validatorFactoryMock->expects($this->once())
                             ->method('getValidatorFor')
                             ->will($this->returnValue($validatorMock));
        $this->replaceByMock('model', 'ops/validator_parameter_factory', $validatorFactoryMock);
        try {
            $observerMock->confirmAliasPayment($order, $quote);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof Mage_Core_Exception);
        }
    }


    private function getRequestParamsWithAlias($quote, $order)
    {
        return array(
            'ALIAS'                           => '99',
            'AMOUNT'                          => 0.0,
            'CURRENCY'                        => 'USD',
            'OPERATION'                       => 'RES',
            'ORDERID'                         => Mage::getSingleton('ops/config')->getConfigData('devprefix')
                . $order->getQuoteId(),
            'EMAIL'                           => 'hubertus.von.fuerstenberg@trash-mail.com',
            'OWNERADDRESS'                    => utf8_decode('An der Tabaksmühle 3a'),
            'OWNERZIP'                        => '04229',
            'OWNERTELNO'                      => null,
            'OWNERCTY'                        => 'DE',
            'ADDMATCH'                        => 1,

            'RTIMEOUT'                        => 45,
            'CREDITDEBIT'                     => 'C',
            'ECOM_SHIPTO_POSTAL_POSTALCODE'   => '04229',
            'ECOM_BILLTO_POSTAL_POSTALCODE'   => '04229',
            'CVC'                             => '123',
            'REMOTE_ADDR'                     => 'NONE',
            'CUID'                            => null,
            'ECI'                             => Netresearch_OPS_Model_Eci_Values::MANUALLY_KEYED_FROM_MOTO,
            'OWNERTOWN'                       => 'Leipzig',
            'ORIG'                            => Mage::helper('ops/data')->getModuleVersionString(),
            'ECOM_SHIPTO_POSTAL_NAME_FIRST'   => 'Hubertus',
            'ECOM_SHIPTO_POSTAL_NAME_LAST'    => utf8_decode('Fürstenberg'),
            'ECOM_SHIPTO_POSTAL_STREET_LINE1' => utf8_decode('An der Tabaksmühle 3a'),
            'ECOM_SHIPTO_POSTAL_STREET_LINE2' => '',
            'ECOM_SHIPTO_POSTAL_STREET_LINE3' => '',
            'ECOM_SHIPTO_POSTAL_COUNTRYCODE'  => 'DE',
            'ECOM_SHIPTO_POSTAL_CITY'         => 'Leipzig',
            'ECOM_SHIPTO_POSTAL_STATE'        => '',
        );
    }

    private function getRequestParamsWithoutAlias($quote, $order)
    {
        return array(
            'ALIAS'                           => '99',
            'AMOUNT'                          => 0.0,
            'CURRENCY'                        => 'USD',
            'OPERATION'                       => 'RES',
            'ORDERID'                         => Mage::getSingleton('ops/config')->getConfigData('devprefix')
                . $order->getQuoteId(),
            'EMAIL'                           => 'hubertus.von.fuerstenberg@trash-mail.com',
            'OWNERADDRESS'                    => utf8_decode('An der Tabaksmühle 3a'),
            'OWNERZIP'                        => '04229',
            'OWNERTELNO'                      => null,
            'OWNERCTY'                        => 'DE',
            'ADDMATCH'                        => 1,
            'RTIMEOUT'                        => 45,
            'CREDITDEBIT'                     => 'C',
            'ECOM_SHIPTO_POSTAL_POSTALCODE'   => '04229',
            'ECOM_BILLTO_POSTAL_POSTALCODE'   => '04229',
            'CVC'                             => '123',
            'REMOTE_ADDR'                     => 'NONE',
            'OWNERTOWN'                       => 'Leipzig',
            'ORIG'                            => Mage::helper('ops/data')->getModuleVersionString(),
            'ECOM_SHIPTO_POSTAL_NAME_FIRST'   => 'Hubertus',
            'ECOM_SHIPTO_POSTAL_NAME_LAST'    => utf8_decode('Fürstenberg'),
            'ECOM_SHIPTO_POSTAL_STREET_LINE1' => utf8_decode('An der Tabaksmühle 3a'),
            'ECOM_SHIPTO_POSTAL_STREET_LINE2' => '',
            'ECOM_SHIPTO_POSTAL_STREET_LINE3' => '',
            'ECOM_SHIPTO_POSTAL_COUNTRYCODE'  => 'DE',
            'ECOM_SHIPTO_POSTAL_CITY'         => 'Leipzig',
            'ECI'                             => 1,
            'CUID'                            => null,
            'ECOM_SHIPTO_POSTAL_STATE'        => '',
        );
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testConfirmDdPayment()
    {
        $quote = Mage::getModel('sales/quote')->load(10);
        $order = Mage::getModel('sales/order')->load(11);

        $observerMock = $this->getModelMock(
            'ops/observer',
            array('performDirectLinkRequest', 'invokeRequestParamValidation')
        );

        $requestParams = array(
            'AMOUNT'                        => 0.0,
            'CARDNO'                        => '12335BLZ12345566',
            'CN'                            => utf8_decode('Hubertus zu Fürstenberg'),
            'CURRENCY'                      => 'USD',
            'ED'                            => '9999',
            'OPERATION'                     => 'RES',
            'ORDERID'                       => Mage::getSingleton('ops/config')->getConfigData('devprefix')
                . $quote->getId(),
            'PM'                            => 'Direct Debits DE',
            'OWNERADDRESS'                  => utf8_decode('An der Tabaksmühle 3a'),
            'OWNERZIP'                      => '04229',
            'OWNERTELNO'                    => null,
            'OWNERCTY'                      => 'DE',
            'ADDMATCH'                      => 1,
            'ECOM_SHIPTO_POSTAL_POSTALCODE' => '04229',
            'ECOM_BILLTO_POSTAL_POSTALCODE' => '04229',
            'CUID'                          => null,
            'BRAND'                         => 'Direct Debits DE',
            'ECI'                           => Netresearch_OPS_Model_Eci_Values::MANUALLY_KEYED_FROM_MOTO,
            'OWNERTOWN'                     => 'Leipzig',
        );

        $directDebitHelperMock = $this->getHelperMock('ops/directDebit', array('getDirectLinkRequestParams'));
        $directDebitHelperMock->expects($this->any())
                              ->method('getDirectLinkRequestParams')
                              ->will($this->returnValue($requestParams));
        $this->replaceByMock('helper', 'ops/directDebit', $directDebitHelperMock);

        $observerMock->expects($this->any())
                     ->method('performDirectLinkRequest')
                     ->with($quote, $requestParams, 1)
                     ->will($this->returnValue('MOTO'));
        $this->assertEquals('MOTO', $observerMock->confirmDdPayment($order, $quote));
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testConfirmDdPaymentWithNoECI()
    {
        $quote = Mage::getModel('sales/quote')->load(10);
        $order = Mage::getModel('sales/order')->load(11);


        $observerMock = $this->getModelMock(
            'ops/observer',
            array('performDirectLinkRequest', 'invokeRequestParamValidation')
        );


        $requestParams = array(
            'AMOUNT'                        => 0.0,
            'CARDNO'                        => '12335BLZ12345566',
            'CN'                            => utf8_decode('Hubertus zu Fürstenberg'),
            'CURRENCY'                      => 'USD',
            'ED'                            => '9999',
            'OPERATION'                     => 'RES',
            'ORDERID'                       => Mage::getSingleton('ops/config')->getConfigData('devprefix')
                . $quote->getId(),
            'PM'                            => 'Direct Debits DE',
            'OWNERADDRESS'                  => utf8_decode('An der Tabaksmühle 3a'),
            'OWNERZIP'                      => '04229',
            'OWNERTELNO'                    => null,
            'OWNERCTY'                      => 'DE',
            'ADDMATCH'                      => 1,
            'ECOM_SHIPTO_POSTAL_POSTALCODE' => '04229',
            'ECOM_BILLTO_POSTAL_POSTALCODE' => '04229',
            'CUID'                          => null,
            'OWNERTOWN'                     => 'Leipzig',
            'BRAND'                         => 'Direct Debits DE'
        );

        $directDebitHelperMock = $this->getHelperMock('ops/directDebit', array('getDirectLinkRequestParams'));
        $directDebitHelperMock->expects($this->any())
                              ->method('getDirectLinkRequestParams')
                              ->will($this->returnValue($requestParams));
        $this->replaceByMock('helper', 'ops/directDebit', $directDebitHelperMock);


        $observerMock->expects($this->any())
                     ->method('performDirectLinkRequest')
                     ->with($quote, $requestParams, 1)
                     ->will($this->returnValue('ECOM'));

        $this->assertEquals('ECOM', $observerMock->confirmDdPayment($order, $quote));
    }

    /**
     * tests that the payment method is cleared before importing new data
     * following conditions must be met in order to get the method gets cleared:
     *      1. event must be sales_quote_payment_import_data_before
     *      2. payment must be an instance of Mage_Sales_Quote_Payment
     *
     */
    public function testClearPaymentMethodFromQuote()
    {
        $observer = Mage::getModel('ops/observer');
        $event = new Varien_Event_Observer();
        $payment = Mage::getModel('sales/quote_payment');
        $payment->setMethod('not relevant');
        $eventData = new Varien_Event();
        $event->setEvent($eventData);
        $event->getEvent()->setData('payment', $payment);

        // method is not cleared because of wring event
        $observer->clearPaymentMethodFromQuote($event);
        $this->assertEquals('not relevant', $event->getEvent()->getPayment()->getMethod());

        // method is cleared
        $event->setEventName('sales_quote_payment_import_data_before');
        $observer->clearPaymentMethodFromQuote($event);
        $this->assertEquals(null, $event->getEvent()->getPayment()->getMethod());

        // method is not cleared because the payment is not a Mage_Sales_Quote_Payment
        $payment = new Varien_Object();
        $payment->setMethod('not relevant');
        $event->getEvent()->setData('payment', $payment);
        $observer->clearPaymentMethodFromQuote($event);
        $this->assertEquals('not relevant', $event->getEvent()->getPayment()->getMethod());
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testaddStatusUpdateButtonToOrderView()
    {
        $adminSessionMock = $this->getModelMock('admin/session', array('isAllowed', 'init', 'save'));
        $adminSessionMock->expects($this->any())
                         ->method('isAllowed')
                         ->will($this->returnValue(true));
        $this->replaceByMock('model', 'admin/session', $adminSessionMock);

        $order = Mage::getModel('sales/order')->load(11);
        Mage::register('sales_order', $order);
        $block = Mage::app()->getLayout()->getBlockSingleton('adminhtml/sales_order_view');
        $event = new Varien_Event_Observer();
        $event->setBlock($block);
        $observer = Mage::getModel('ops/observer');
        $observer->addStatusUpdateButtonToOrderView($event);
        $buttons = $block->getButtonsHtml();
        $this->assertContains(Mage::helper('ops/data')->__('Refresh payment status'), $buttons);
        $this->assertContains('setLocation(\'' . $block->getUrl('adminhtml/opsstatus/update') . '\')', $buttons);
        Mage::unregister('sales_order');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testAddStatusUpdateButtonToOrderViewWillNotAddButton()
    {
        $adminSessionMock = $this->getModelMock('admin/session', array('isAllowed', 'init', 'save'));
        $adminSessionMock->expects($this->any())
                         ->method('isAllowed')
                         ->will($this->returnValue(false));
        $this->replaceByMock('model', 'admin/session', $adminSessionMock);

        $order = Mage::getModel('sales/order')->load(11);
        Mage::register('sales_order', $order);
        $block = Mage::app()->getLayout()->getBlockSingleton('adminhtml/sales_order_view');
        $event = new Varien_Event_Observer();
        $event->setBlock($block);
        $observer = Mage::getModel('ops/observer');
        $observer->addStatusUpdateButtonToOrderView($event);
        $buttons = $block->getButtonsHtml();
        $this->assertNotContains(Mage::helper('ops/data')->__('Refresh payment status'), $buttons);
        $this->assertNotContains('setLocation(\'' . $block->getUrl('adminhtml/opsstatus/update') . '\')', $buttons);
        Mage::unregister('sales_order');

        $adminSessionMock = $this->getModelMock('admin/session', array('isAllowed', 'init', 'save'));
        $adminSessionMock->expects($this->any())
                         ->method('isAllowed')
                         ->will($this->returnValue(true));
        $this->replaceByMock('model', 'admin/session', $adminSessionMock);

        $order = Mage::getModel('sales/order')->load(30);
        Mage::register('sales_order', $order);
        $block = Mage::app()->getLayout()->getBlockSingleton('adminhtml/sales_order_view');
        $event = new Varien_Event_Observer();
        $event->setBlock($block);
        $observer = Mage::getModel('ops/observer');
        $observer->addStatusUpdateButtonToOrderView($event);
        $buttons = $block->getButtonsHtml();
        $this->assertNotContains(Mage::helper('ops/data')->__('Refresh payment status'), $buttons);
        $this->assertNotContains('setLocation(\'' . $block->getUrl('adminhtml/opsstatus/update') . '\')', $buttons);
        Mage::unregister('sales_order');
    }

    public function testAddCcPaymentMethod()
    {
        $observer = Mage::getModel('ops/observer');
        $event = new Varien_Event_Observer();
        $block = new Mage_Payment_Block_Form_Container();
        $quote = Mage::getModel('sales/quote');
        $block->setQuote($quote);
        $eventData = new Varien_Event();
        $event->setEvent($eventData);
        $event->getEvent()->setData('block', $block);

        $versionHelperMock = $this->getHelperMock('ops/version', array('canUseApplicableForQuote'));
        $versionHelperMock->expects($this->any())
                          ->method('canUseApplicableForQuote')
                          ->will($this->returnvalue(false));
        $this->replaceByMock('helper', 'ops/version', $versionHelperMock);

        $paymentHelperMock = $this->getHelperMock('ops/payment', array('addCCForZeroAmountCheckout'));
        $paymentHelperMock->expects($this->once())
                          ->method('addCCForZeroAmountCheckout')
                          ->will($this->returnValue($paymentHelperMock));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);

        $observer->addCcPaymentMethod($event);
    }

    public function testDisableCaptureForZeroAmountInvoice()
    {
        $ccPaymentObject = Mage::getModel('ops/payment_cc');
        $block = new Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items();
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethodInstance($ccPaymentObject);
        $order = Mage::getModel('sales/order');
        $order->setPayment($payment);
        $invoice = Mage::getModel('sales/order_invoice');
        $invoice->setBaseGrandTotal(0.00);
        $invoice->setOrder($order);
        $observer = Mage::getModel('ops/observer');
        $event = new Varien_Event_Observer();
        Mage::register('current_invoice', $invoice, true);
        $eventData = new Varien_Event();
        $event->setEvent($eventData);
        $event->getEvent()->setData('block', $block);

        $this->assertTrue($ccPaymentObject->canCapture());
        $observer->disableCaptureForZeroAmountInvoice($event);
        $this->assertFalse($ccPaymentObject->canCapture());

        // clean up invoice from registry after test
        Mage::unregister('current_invoice');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testCheckoutTypeOnepageSaveOrderBefore()
    {
        $event = new Varien_Event_Observer();
        $order = Mage::getModel('sales/order')->load(11);
        $quotePayment = Mage::getModel('sales/quote_payment')->load(4);
        $pmMock = $this->getModelMock('ops/payment_bankTransfer', array('getFormFields'));
        $quotePayment->setMethodInstance($pmMock);
        $quote = $this->getModelMock('sales/quote', array('getPayment'));
        $quote->expects($this->any())
              ->method('getPayment')
              ->will($this->returnValue($quotePayment));

        $quotePayment->setQuote($quote);
        $quote->setPayment($quotePayment);
        $this->replaceByMock('model', 'ops/payment_bankTransfer', $pmMock);
        $event->setOrder($order);
        $event->setQuote($quote);
        $observerMock = $this->getModelMock('ops/observer', array('invokeRequestParamValidation'));
        $observerMock->expects($this->once())
                     ->method('invokeRequestParamValidation');
        $observerMock->checkoutTypeOnepageSaveOrderBefore($event);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testControllerActionCheckoutOnepagePostdispatch()
    {
        $fakeController = new Varien_Object();
        $fakeResponse = new Varien_Object();
        $quotePayment = Mage::getModel('sales/quote_payment')->load(4);
        $pmMock = $this->getModelMock('ops/payment_bankTransfer', array('isAvailable'));
        $pmMock->expects($this->any())
               ->method('isAvailable')
               ->will($this->returnValue(true));
        $quote = $this->getModelMock('sales/quote', array('getPayment'));
        $quote->expects($this->any())
              ->method('getPayment')
              ->will($this->returnValue($quotePayment));
        $quotePayment->setQuote($quote);
        $quote->setPayment($quotePayment);
        $this->replaceByMock('model', 'ops/payment_bankTransfer', $pmMock);


        $fakeOnePage = new Varien_Object();
        $fakeOnePage->setQuote($quote);

        $observerMock = $this->getModelMock('ops/observer', array('getOnepage'));
        $observerMock->expects($this->once())
                     ->method('getOnepage')
                     ->will($this->returnValue($fakeOnePage));
        $helperMock = $this->getHelperMock('ops/payment_request', array('getOwnerParams', 'extractShipToParameters'));
        $helperMock->expects($this->once())
                   ->method('getOwnerParams')
                   ->will($this->returnValue(array()));
        $helperMock->expects($this->once())
                   ->method('extractShipToParameters')
                   ->will($this->returnValue(array()));
        $this->replaceByMock('helper', 'ops/payment_request', $helperMock);
        $validatorMock = $this->getModelMock('ops/validator_parameter_validator', array('isValid', 'getMessages'));
        $validatorMock->expects($this->once())
                      ->method('isValid')
                      ->will($this->returnValue(false));
        $validatorMock->expects($this->any())
                      ->method('getMessages')
                      ->will($this->returnValue(array('Foo' => 'Not Valid')));
        $this->replaceByMock('model', 'ops/validator_parameter_validator', $validatorMock);
        $fakeResponse->setBody(
            Mage::helper('core/data')->jsonEncode(array('error' => false, 'update_section' => 'foo'))
        );
        $fakeController->setResponse($fakeResponse);
        $event = new Varien_Event_Observer();
        $event->setControllerAction($fakeController);
        $observerMock->controllerActionCheckoutOnepagePostdispatch($event);
        $result = Mage::helper('core/data')->jsonDecode($fakeResponse->getBody());
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('goto_section', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayNotHasKey('update_section', $result);
    }

    public function testSalesOrderPaymentCapture()
    {
        $opsObserver = Mage::getModel('ops/observer');
        $event = new Varien_Event_Observer();
        $fakePayment = Mage::getModel('sales/order_payment');
        $fakePayment->setMethod('checkmo');
        $invoice = Mage::getModel('sales/order_invoice');
        $event->setPayment($fakePayment);
        $event->setInvoice($invoice);
        $opsObserver->salesOrderPaymentCapture($event);
        $this->assertNull($fakePayment->getInvoice());

        $fakePayment = Mage::getModel('sales/order_payment');
        $fakePayment->setMethod('ops_cc');
        $event->setPayment($fakePayment);
        $event->setInvoice($invoice);
        $opsObserver->salesOrderPaymentCapture($event);
        $this->assertEquals($invoice, $fakePayment->getInvoice());

    }

    public function testSetOrderStateForDirectLinkDoesNotChangeState()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests($order, 'payment/method_cc');
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getState());
    }


    public function testSetOrderStateForDirectLinkDoesNotChangeStateDueToHelper()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests($order, 'ops/payment_directDebit');
        $this->registerPaymentHelperMockForDirectDebitNlTests(false);
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getState());
    }

    public function testSetOrderStateForDirectLinkDoesNotChangeStateDueToMissingInfos()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests($order, 'ops/payment_directDebit');
        $this->registerPaymentHelperMockForDirectDebitNlTests(true);
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getState());
    }

    public function testSetOrderStateForDirectLinkDoesNotChangeStateDueToMissingPm()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests($order, 'ops/payment_directDebit', array('STATUS' => 51));
        $this->registerPaymentHelperMockForDirectDebitNlTests(true);
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getState());
    }

    public function testSetOrderStateForDirectLinkDoesNotChangeStateDueToMissingStatus()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests(
            $order, 'ops/payment_directDebit',
            array('PM' => 'Direct Debits NL')
        );
        $this->registerPaymentHelperMockForDirectDebitNlTests(true);
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getState());
    }

    public function testSetOrderStateForDirectLinkDoesNotChangeStateDueToWrongPm()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests(
            $order, 'ops/payment_directDebit',
            array('PM' => 'Direct Debits DE', 'STATUS' => 51)
        );
        $this->registerPaymentHelperMockForDirectDebitNlTests(true);
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getState());
    }

    public function testSetOrderStateForDirectLinkDoesNotChangeStateDueToWrongStatus()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests(
            $order, 'ops/payment_directDebit',
            array('PM' => 'Direct Debits NL', 'STATUS' => 5)
        );
        $this->registerPaymentHelperMockForDirectDebitNlTests(true);
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PROCESSING, $order->getState());
    }

    public function testSetOrderStateForDirectLinkDoesChangeState()
    {
        $order = $this->getOrderForDirectDebitNlTest();
        $event = $this->getEventForDirectDebitNlTests(
            $order, 'ops/payment_directDebit',
            array('PM'     => 'Direct Debits NL',
                  'status' => Netresearch_OPS_Model_Status::AUTHORIZED)
        );
        $this->registerPaymentHelperMockForDirectDebitNlTests(true);
        $observer = Mage::getModel('ops/observer');
        $observer->setOrderStateDirectLink($event);
        $this->assertEquals(Mage_sales_Model_Order::STATE_PENDING_PAYMENT, $order->getStatus());
        $this->assertEquals(Mage_sales_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
    }

    /**
     * @return array
     */
    protected function getEventForDirectDebitNlTests($order, $paymentMethod, array $addInfos = array())
    {
        $event = new Varien_Event_Observer();
        $payment = $this->getModelMock('payment/info', array('getMethodInstance', 'getOrder'));
        $payment->expects($this->any())
                ->method('getMethodInstance')
                ->will($this->returnValue(Mage::getModel($paymentMethod)));
        $payment->expects($this->any())
                ->method('getOrder')
                ->will($this->returnValue($order));
        $payment->setAdditionalInformation($addInfos);
        $event->setPayment($payment);

        return $event;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderForDirectDebitNlTest()
    {
        $order = $this->getModelMock('sales/order', array('save',));
        $order->setStatus(Mage_sales_Model_Order::STATE_PROCESSING);
        $order->setState(Mage_sales_Model_Order::STATE_PROCESSING);

        return $order;
    }

    protected function registerPaymentHelperMockForDirectDebitNlTests($isInlinePaymentWithOrderIdRetVal = true)
    {
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('isInlinePayment'));
        $paymentHelperMock->expects($this->once())
                          ->method('isInlinePayment')
                          ->will($this->returnValue($isInlinePaymentWithOrderIdRetVal));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);
    }

    public function testUpdateRecurringProfileButtons()
    {
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setMethodCode(Netresearch_OPS_Model_Payment_Recurring_Cc::CODE)
                ->setState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE)
                ->setId(1);

        $this->replaceRegistry('current_recurring_profile', $profile);

        /** @var  $block Mage_Sales_Block_Adminhtml_Recurring_Profile_View */
        $block = Mage::app()->getLayout()->createBlock('sales/adminhtml_recurring_profile_view');
        $block->setLayout(Mage::app()->getLayout());
        $observer = new Varien_Event_Observer();
        $event = new Varien_Event();
        $event->setData('block', $block);
        $observer->setEvent($event);

        $subject = Mage::getModel('ops/observer');
        $subject->updateRecurringProfileButtons($observer);
        $html = $block->getButtonsHtml('header');

        $this->assertContains(
            'Suspending the subscription here will not actually cancel the subscription on Ingenico ePayments side.',
            $html
        );
    }

    public function overrideRecurringProfileState()
    {
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setMethodCode(Netresearch_OPS_Model_Payment_Recurring_Cc::CODE)
                ->setState('abc')
                ->setNewState('def')
                ->setOverrideState(true);

        $event = new Varien_Event_Observer();
        $event->setObject($profile);

        $observer = Mage::getModel('ops/observer');
        $observer->overrideRecurringProfileState($event);

        $this->assertEquals($profile->getNewState(), $profile->getState());
        $this->assertEquals('def', $profile->getState());
    }

    /**
     * @test
     */
    public function sendTransactionalEmailsNoOps()
    {
        // no emails on third party payments
        $helperMock = $this->getHelperMock('ops/data', array('sendTransactionalEmail'));
        $helperMock
            ->expects($this->never())
            ->method('sendTransactionalEmail')
            ->with($this->isInstanceOf('Mage_Sales_Model_Order'));
        $this->replaceByMock('helper', 'ops/data', $helperMock);

        $payment = new Varien_Object(
            array(
            'method' => Mage::getModel('payment/method_checkmo')->getCode(),
            )
        );
        $order = new Varien_Object(
            array(
            'payment' => $payment,
            )
        );

        $observerMock = $this->getMockBuilder('Varien_Event_Observer')
            ->setMethods(array('getOrder'))
            ->getMock();
        $observerMock
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);

        Mage::getModel('ops/observer')->sendTransactionalEmails($observerMock);
    }

    /**
     * @test
     */
    public function sendTransactionalEmailsGatewayPayment()
    {
        // no emails on ingenico redirect payments
        $helperMock = $this->getHelperMock('ops/data', array('sendTransactionalEmail'));
        $helperMock
            ->expects($this->never())
            ->method('sendTransactionalEmail')
            ->with($this->isInstanceOf('Mage_Sales_Model_Order'));
        $this->replaceByMock('helper', 'ops/data', $helperMock);

        $payment = new Varien_Object(
            array(
            'method_instance' => Mage::getModel('ops/payment_bankTransfer'),
            'order_place_redirect_url' => 'foo',
            )
        );
        $order = new Varien_Object(
            array(
            'payment' => $payment,
            )
        );
        $quote = new Varien_Object(
            array(
            'payment' => $payment,
            )
        );

        $observerMock = $this->getMockBuilder('Varien_Event_Observer')
            ->setMethods(array('getOrder', 'getQuote'))
            ->getMock();
        $observerMock
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);
        $observerMock
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        Mage::getModel('ops/observer')->sendTransactionalEmails($observerMock);
    }

    /**
     * @test
     */
    public function sendTransactionalEmailsException()
    {
        // exception not really testable.
        // just assert that second call to sendTransactionalEmail is not invoked
        $helperMock = $this->getHelperMock('ops/data', array('sendTransactionalEmail'));
        $helperMock
            ->expects($this->exactly(1))
            ->method('sendTransactionalEmail')
            ->withConsecutive(
                $this->isInstanceOf('Mage_Sales_Model_Order'),
                $this->isInstanceOf('Mage_Sales_Model_Order_Invoice')
            )
            ->willThrowException(new Exception('no mails foo'));
        $this->replaceByMock('helper', 'ops/data', $helperMock);


        $payment = new Varien_Object(
            array(
            'method_instance' => Mage::getModel('ops/payment_bankTransfer'),
            )
        );
        $quote = new Varien_Object(
            array(
            'payment' => $payment,
            )
        );
        $orderMock = $this->getModelMock('sales/order', array('getPayment'));
        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($payment);

        $observerMock = $this->getMockBuilder('Varien_Event_Observer')
            ->setMethods(array('getOrder', 'getQuote'))
            ->getMock();
        $observerMock
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $observerMock
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        Mage::getModel('ops/observer')->sendTransactionalEmails($observerMock);
    }

    /**
     * @test
     */
    public function sendTransactionalEmailsSuccess()
    {
        $helperMock = $this->getHelperMock('ops/data', array('sendTransactionalEmail'));
        $helperMock
            ->expects($this->exactly(2))
            ->method('sendTransactionalEmail')
            ->withConsecutive(
                $this->isInstanceOf('Mage_Sales_Model_Order'),
                $this->isInstanceOf('Mage_Sales_Model_Order_Invoice')
            );
        $this->replaceByMock('helper', 'ops/data', $helperMock);

        // invoice
        $invoice = Mage::getModel('sales/order_invoice');

        // order payment
        $payment = new Varien_Object(
            array(
            'method_instance' => Mage::getModel('ops/payment_cc'),
            'created_invoice' => $invoice,
            )
        );

        // order
        $orderMock = $this->getModelMock('sales/order', array('getPayment'));
        $orderMock
            ->expects($this->exactly(2))
            ->method('getPayment')
            ->willReturn($payment);
        $this->replaceByMock('model', 'sales/order', $orderMock);
        $order = Mage::getModel('sales/order');

        // quote
        $quote = new Varien_Object(
            array(
            'payment' => $payment,
            )
        );

        $observerMock = $this->getMockBuilder('Varien_Event_Observer')
            ->setMethods(array('getOrder', 'getQuote'))
            ->getMock();
        $observerMock->expects($this->once())->method('getOrder')->willReturn($order);
        $observerMock->expects($this->once())->method('getQuote')->willReturn($quote);

        Mage::getModel('ops/observer')->sendTransactionalEmails($observerMock);
    }

    public function testSendPayPerMailInfoSuccess()
    {
        // order payment
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethodInstance(Mage::getModel('ops/payment_payPerMail'));

        $order = Mage::getModel('sales/order');
        $order->setPayment($payment);

        $observerMock = $this->getMockBuilder('Varien_Event_Observer')
            ->setMethods(['getOrder'])
            ->getMock()
        ;
        $observerMock->expects($this->once())->method('getOrder')->willReturn($order);
        Mage::getModel('ops/observer')->sendPayPerMailInfo($observerMock);
    }

    public function testSendPayPerMailInfoFailure()
    {
        // order payment
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethodInstance(Mage::getModel('ops/payment_cc'));

        $order = Mage::getModel('sales/order');
        $order->setPayment($payment);

        $observerMock = $this->getMockBuilder('Varien_Event_Observer')
            ->setMethods(['getOrder'])
            ->getMock()
        ;
        $observerMock->expects($this->once())->method('getOrder')->willReturn($order);
        Mage::getModel('ops/observer')->sendPayPerMailInfo($observerMock);
    }

}
