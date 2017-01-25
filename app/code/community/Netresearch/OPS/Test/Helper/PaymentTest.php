<?php

class Netresearch_OPS_Test_Helper_PaymentTest
    extends Netresearch_OPS_Test_Model_Response_TestCase
{
    /** @var  Netresearch_OPS_Helper_Payment $_helper */
    private $_helper;
    private $store;

    public function setUp()
    {
        parent::setUp();
        $this->_helper = Mage::helper('ops/payment');
        $this->store = Mage::app()->getStore(0)->load(0);
        $this->store->resetConfig();
    }

    public function testIsPaymentAuthorizeType()
    {
        $this->assertTrue(
            $this->_helper->isPaymentAuthorizeType(
                Netresearch_OPS_Model_Status::AUTHORIZED
            )
        );
        $this->assertTrue(
            $this->_helper->isPaymentAuthorizeType(
                Netresearch_OPS_Model_Status::AUTHORIZATION_WAITING
            )
        );
        $this->assertTrue(
            $this->_helper->isPaymentAuthorizeType(
                Netresearch_OPS_Model_Status::AUTHORIZED_UNKNOWN
            )
        );
        $this->assertTrue(
            $this->_helper->isPaymentAuthorizeType(
                Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT
            )
        );
        $this->assertFalse($this->_helper->isPaymentAuthorizeType(0));
    }

    public function testIsPaymentCaptureType()
    {
        $this->assertTrue(
            $this->_helper->isPaymentCaptureType(
                Netresearch_OPS_Model_Status::PAYMENT_REQUESTED
            )
        );
        $this->assertTrue(
            $this->_helper->isPaymentCaptureType(
                Netresearch_OPS_Model_Status::PAYMENT_PROCESSING
            )
        );
        $this->assertTrue(
            $this->_helper->isPaymentCaptureType(
                Netresearch_OPS_Model_Status::PAYMENT_UNCERTAIN
            )
        );
        $this->assertFalse($this->_helper->isPaymentCaptureType(0));
    }

    /**
     * send no invoice mail if it is denied by configuration
     */
    public function testSendNoInvoiceToCustomerIfDenied()
    {
        $this->store->setConfig('payment_services/ops/send_invoice', 0);
        $this->assertFalse(Mage::getModel('ops/config')->getSendInvoice());
        $sentInvoice = $this->getModelMock(
            'sales/order_invoice', array('getEmailSent', 'sendEmail')
        );
        $sentInvoice->expects($this->any())
            ->method('getEmailSent')
            ->will($this->returnValue(false));
        $sentInvoice->expects($this->never())
            ->method('sendEmail');
        $this->_helper->sendInvoiceToCustomer($sentInvoice);
    }

    /**
     * send no invoice mail if it was already sent
     */
    public function testSendNoInvoiceToCustomerIfAlreadySent()
    {
        $this->store->setConfig('payment_services/ops/send_invoice', 1);
        $this->assertTrue(Mage::getModel('ops/config')->getSendInvoice());
        $someInvoice = $this->getModelMock(
            'sales/order_invoice', array('getEmailSent', 'sendEmail')
        );
        $someInvoice->expects($this->any())
            ->method('getEmailSent')
            ->will($this->returnValue(true));
        $someInvoice->expects($this->never())
            ->method('sendEmail');
        $this->_helper->sendInvoiceToCustomer($someInvoice);
    }

    /**
     * send invoice mail
     */
    public function testSendInvoiceToCustomerIfEnabled()
    {
        $this->store->setConfig('payment_services/ops/send_invoice', 1);
        $this->assertTrue(Mage::getModel('ops/config')->getSendInvoice());
        $anotherInvoice = $this->getModelMock(
            'sales/order_invoice', array('getEmailSent', 'sendEmail')
        );
        $anotherInvoice->expects($this->any())
            ->method('getEmailSent')
            ->will($this->returnValue(false));
        $anotherInvoice->expects($this->once())
            ->method('sendEmail')
            ->with($this->equalTo(true));
        $this->_helper->sendInvoiceToCustomer($anotherInvoice);
    }

    public function testPrepareParamsAndSort()
    {
        $params = array(
            'CVC'          => '123',
            'CARDNO'       => '4111111111111111',
            'CN'           => 'JohnSmith',
            'PSPID'        => 'test1',
            'ED'           => '1212',
            'ACCEPTURL'    => 'https=//www.myshop.com/ok.html',
            'EXCEPTIONURL' => 'https=//www.myshop.com/nok.html',
            'BRAND'        => 'VISA',
        );
        $sortedParams = array(
            'ACCEPTURL'    => array('key'   => 'ACCEPTURL',
                                    'value' => 'https=//www.myshop.com/ok.html'),
            'BRAND'        => array('key' => 'BRAND', 'value' => 'VISA'),
            'CARDNO'       => array('key'   => 'CARDNO',
                                    'value' => '4111111111111111'),
            'CN'           => array('key' => 'CN', 'value' => 'JohnSmith'),
            'CVC'          => array('key' => 'CVC', 'value' => '123'),
            'ED'           => array('key' => 'ED', 'value' => '1212'),
            'EXCEPTIONURL' => array('key'   => 'EXCEPTIONURL',
                                    'value' => 'https=//www.myshop.com/nok.html'),
            'PSPID'        => array('key' => 'PSPID', 'value' => 'test1'),
        );
        $secret = 'Mysecretsig1875!?';
        $shaInSet
            = 'ACCEPTURL=https=//www.myshop.com/ok.htmlMysecretsig1875!?BRAND=VISAMysecretsig1875!?'
            . 'CARDNO=4111111111111111Mysecretsig1875!?CN=JohnSmithMysecretsig1875!?CVC=123Mysecretsig1875!?'
            . 'ED=1212Mysecretsig1875!?EXCEPTIONURL=https=//www.myshop.com/nok.htmlMysecretsig1875!?'
            . 'PSPID=test1Mysecretsig1875!?';
        $key = 'a28dc9fe69b63fe81da92471fefa80aca3f4851a';
        $this->assertEquals(
            $sortedParams, $this->_helper->prepareParamsAndSort($params)
        );
        $this->assertEquals(
            $shaInSet, $this->_helper->getSHAInSet($params, $secret)
        );
        $this->assertEquals($key, $this->_helper->shaCrypt($shaInSet, $secret));
    }

    public function testHandleUnknownStatus()
    {
        $order = $this->getModelMock('sales/order', array('save'));
        $order->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));
        $order->setState(
            Mage_Sales_Model_Order::STATE_NEW,
            Mage_Sales_Model_Order::STATE_NEW
        );
        $statusHistoryCount = $order->getStatusHistoryCollection()->count();
        Mage::helper('ops/payment')->handleUnknownStatus($order);
        $this->assertEquals(
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $order->getState()
        );
        $this->assertTrue(
            $statusHistoryCount < $order->getStatusHistoryCollection()->count()
        );
        $statusHistoryCount = $order->getStatusHistoryCollection()->count();
        $order->setState(
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage_Sales_Model_Order::STATE_PROCESSING
        );

        Mage::helper('ops/payment')->handleUnknownStatus($order);
        $this->assertEquals(
            Mage_Sales_Model_Order::STATE_PROCESSING, $order->getState()
        );
        $this->assertTrue(
            $statusHistoryCount < $order->getStatusHistoryCollection()->count()
        );
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testGetBaseGrandTotalFromSalesObject()
    {
        $helper = Mage::helper('ops/payment');
        $order = Mage::getModel('sales/order')->load(14);
        $amount = $helper->getBaseGrandTotalFromSalesObject($order);
        $this->assertEquals($order->getBaseGrandTotal(), $amount);
        $order = Mage::getModel('sales/order')->load(15);
        $amount = $helper->getBaseGrandTotalFromSalesObject($order);
        $this->assertEquals($order->getBaseGrandTotal(), $amount);
        $quote = Mage::getModel('sales/quote')->load(1);
        $amount = $helper->getBaseGrandTotalFromSalesObject($quote);
        $this->assertEquals($quote->getBaseGrandTotal(), $amount);
        $quote = Mage::getModel('sales/quote')->load(2);
        $amount = $helper->getBaseGrandTotalFromSalesObject($quote);
        $this->assertEquals($quote->getBaseGrandTotal(), $amount);
        $someOtherObject = new Varien_Object();
        $this->setExpectedException('Mage_Core_Exception');
        $helper->getBaseGrandTotalFromSalesObject($someOtherObject);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSaveOpsRefundOperationCodeToPayment()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $payment = $order->getPayment();
        $helper = Mage::helper('ops/payment');

        // no last refund operation code is set if an empty string is passed
        $helper->saveOpsRefundOperationCodeToPayment($payment, '');
        $this->assertFalse(
            array_key_exists(
                'lastRefundOperationCode', $payment->getAdditionalInformation()
            )
        );

        // no last refund operation code is set if it's no refund operation code
        $helper->saveOpsRefundOperationCodeToPayment(
            $payment, Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_FULL
        );
        $this->assertFalse(
            array_key_exists(
                'lastRefundOperationCode', $payment->getAdditionalInformation()
            )
        );

        // last ops refund code is present if a valid refund code is passed
        $helper->saveOpsRefundOperationCodeToPayment(
            $payment, Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL
        );
        $this->assertTrue(
            array_key_exists(
                'lastRefundOperationCode', $payment->getAdditionalInformation()
            )
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL,
            $payment->getAdditionalInformation('lastRefundOperationCode')
        );

        // last ops refund code is present if a valid refund code is passed and will override a previous one
        $helper->saveOpsRefundOperationCodeToPayment(
            $payment, Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL
        );
        $this->assertTrue(
            array_key_exists(
                'lastRefundOperationCode', $payment->getAdditionalInformation()
            )
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL,
            $payment->getAdditionalInformation('lastRefundOperationCode')
        );
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testSetCanRefundToPayment()
    {

        $helper = Mage::helper('ops/payment');
        $order = Mage::getModel('sales/order')->load(11);
        $payment = $order->getPayment();
        $helper->setCanRefundToPayment($payment);
        $this->assertFalse(
            array_key_exists('canRefund', $payment->getAdditionalInformation())
        );

        $order = Mage::getModel('sales/order')->load(15);
        $payment = $order->getPayment();
        $helper->setCanRefundToPayment($payment);
        $this->assertTrue($payment->getAdditionalInformation('canRefund'));

        $order = Mage::getModel('sales/order')->load(16);
        $payment = $order->getPayment();
        $helper->setCanRefundToPayment($payment);
        $this->assertFalse($payment->getAdditionalInformation('canRefund'));
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     *
     */
    public function testSetPaymentTransactionInformation()
    {
        $dataMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $dataMock->expects($this->any())
            ->method('isAdminSession')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'ops/data', $dataMock);

        $order = Mage::getModel('sales/order')->load(15);
        $reflectionClass = new ReflectionClass(
            get_class(
                Mage::helper('ops/payment')
            )
        );
        $method = $reflectionClass->getMethod(
            'setPaymentTransactionInformation'
        );
        $method->setAccessible(true);
        $paymentHelper = Mage::helper('ops/payment');
        $params = array(
            'PAYID'  => '0815',
            'STATUS' => 9
        );
        $method->invoke(
            $paymentHelper, $order->getPayment(), $params, 'accept'
        );
        $this->assertEquals(
            '0815', $order->getPayment()->getAdditionalInformation('paymentId')
        );
        $this->assertEquals(
            9, $order->getPayment()->getAdditionalInformation('status')
        );

        $params = array(
            'PAYID'      => '0815',
            'STATUS'     => 9,
            'ACCEPTANCE' => ''
        );
        $method->invoke(
            $paymentHelper, $order->getPayment(), $params, 'accept'
        );
        $this->assertEquals(
            '0815', $order->getPayment()->getAdditionalInformation('paymentId')
        );
        $this->assertEquals(
            '', $order->getPayment()->getAdditionalInformation('acceptance')
        );

        $params = array(
            'PAYID'      => '0815',
            'STATUS'     => 9,
            'ACCEPTANCE' => 'Akzeptanz'
        );
        $method->invoke(
            $paymentHelper, $order->getPayment(), $params, 'accept'
        );
        $this->assertEquals(
            '0815', $order->getPayment()->getAdditionalInformation('paymentId')
        );
        $this->assertEquals(
            'Akzeptanz',
            $order->getPayment()->getAdditionalInformation('acceptance')
        );

        $params = array(
            'PAYID'       => '0815',
            'STATUS'      => 9,
            'ACCEPTANCE'  => 'Akzeptanz',
            'HTML_ANSWER' => '3D Secure',
            'BRAND'       => 'Brand'
        );
        $method->invoke(
            $paymentHelper, $order->getPayment(), $params, 'accept'
        );
        $this->assertEquals(
            '0815', $order->getPayment()->getAdditionalInformation('paymentId')
        );
        $this->assertEquals(
            'Akzeptanz',
            $order->getPayment()->getAdditionalInformation('acceptance')
        );
        $this->assertEquals(
            '3D Secure',
            $order->getPayment()->getAdditionalInformation('HTML_ANSWER')
        );
        $this->assertEquals(
            'Brand',
            $order->getPayment()->getAdditionalInformation('CC_BRAND')
        );
    }
    
    /**
     * @param int    $opsStatus      Incoming postBack status
     * @param bool   $sendMail       Indicates whether opsStatus should trigger order confirmation mail
     * @param string $feedbackStatus Indicates the route that the customer should get redirected to
     *
     * @loadFixture  ../../../var/fixtures/orders.yaml
     * @dataProvider applyStateForOrderProvider
     */
    public function testApplyStateForOrder($opsStatus, $sendMail, $feedbackStatus)
    {
        $this->mockEmailHelper($this->exactly(intval($sendMail)));
        $this->mockOrderConfig();

        $helperMock = $this->getHelperMock('ops', array('isAdminSession', 'sendTransactionalEmail'));
        $helperMock->expects($this->any())
            ->method('isAdminSession')
            ->will($this->returnValue(false));
        $helperMock->expects($this->any())
            ->method('sendTransactionalEmail')
            ->will($this->returnArgument(0));
        $this->replaceByMock('helper', 'ops', $helperMock);

        /** @var Netresearch_OPS_Helper_Payment $paymenthelperMock */
        $paymenthelperMock = $this->getHelperMock(
            'ops/payment', array(
                'acceptOrder', 'waitOrder', 'declineOrder', 'cancelOrder', 'handleException',
            )
        );

        $order = Mage::getModel('sales/order')->load(19);
        $this->assertEquals(
            $feedbackStatus,
            $paymenthelperMock->applyStateForOrder($order, array('STATUS' => $opsStatus))
        );
    }

    public function applyStateForOrderProvider()
    {
        return array(
            // assertion for WAITING_FOR_IDENTIFICATION = 46
            array(
                $opsStatus = Netresearch_OPS_Model_Status::WAITING_FOR_IDENTIFICATION,
                $sendMail = false,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            ),
            // assertion for AUTHORIZED = 5
            array(
                $opsStatus = Netresearch_OPS_Model_Status::AUTHORIZED,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            ),
            // assertion for AUTHORIZED_WAITING_EXTERNAL_RESULT = 50
            array(
                $opsStatus = Netresearch_OPS_Model_Status::AUTHORIZED_WAITING_EXTERNAL_RESULT,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            ),
            // assertion for AUTHORIZATION_WAITING = 51
            array(
                $opsStatus = Netresearch_OPS_Model_Status::AUTHORIZATION_WAITING,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            ),
            // assertion for AUTHORIZED_UNKNOWN = 52
            array(
                $opsStatus = Netresearch_OPS_Model_Status::AUTHORIZED_UNKNOWN,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_EXCEPTION,
            ),
            // assertion for WAITING_CLIENT_PAYMENT = 41
            array(
                $opsStatus = Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            ),
            // assertion for PAYMENT_REQUESTED = 9
            array(
                $opsStatus = Netresearch_OPS_Model_Status::PAYMENT_REQUESTED,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            ),
            // assertion for PAYMENT_PROCESSING = 91
            array(
                $opsStatus = Netresearch_OPS_Model_Status::PAYMENT_PROCESSING,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            ),
            // assertion for AUTHORISATION_DECLINED = 2
            array(
                $opsStatus = Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_DECLINE,
            ),
            // assertion for PAYMENT_REFUSED = 93
            array(
                $opsStatus = Netresearch_OPS_Model_Status::PAYMENT_REFUSED,
                $sendMail = true,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_DECLINE,
            ),
            // assertion for CANCELED_BY_CUSTOMER = 1
            array(
                $opsStatus = Netresearch_OPS_Model_Status::CANCELED_BY_CUSTOMER,
                $sendMail = false,
                $feedbackStatus = Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_CANCEL,
            ),
        );
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testForceAuthorize()
    {
        $helper = Mage::helper('ops/payment');
        $reflectionClass = new ReflectionClass(get_class($helper));
        $method = $reflectionClass->getMethod("forceAuthorize");
        $method->setAccessible(true);

        $order = Mage::getModel('sales/order')->load(11);
        $this->assertFalse($method->invoke($helper, $order));

        $order = Mage::getModel('sales/order')->load(27);
        $this->assertTrue($method->invoke($helper, $order));

        $order = Mage::getModel('sales/order')->load(28);
        $this->assertTrue($method->invoke($helper, $order));

        //        $order = Mage::getModel('sales/order')->load(29);
        //        $this->assertTrue($method->invoke($helper, $order));
    }

    public function testCheckIfCCisInCheckoutMethodsFalse()
    {
        $testMethod = $this->getProtectedMethod($this->_helper, 'checkIfCCisInCheckoutMethods');
        $paymentMethods = new Varien_Object();
        $paymentMethods->setCode('ops_iDeal');
        $this->assertFalse($testMethod->invoke($this->_helper, array($paymentMethods)));


    }

    public function testCheckIfCCisInCheckoutMethodsTrue()
    {
        $testMethod = $this->getProtectedMethod($this->_helper, 'checkIfCCisInCheckoutMethods');
        $paymentMethods = new Varien_Object();
        $paymentMethods->setCode('ops_cc');
        $this->assertTrue($testMethod->invoke($this->_helper, array($paymentMethods)));


    }

    public function testAddCCForZeroAmountCheckout()
    {
        $block = new Mage_Payment_Block_Form_Container();
        $method = new Varien_Object();
        $method->setCode('ops_ideal');
        $block->setData('methods', array($method));
        $quote = Mage::getModel('sales/quote');
        $block->setQuote($quote);

        $featureModelMock = $this->getModelMock(
            'ops/payment_features_zeroAmountAuth', array('isCCAndZeroAmountAuthAllowed')
        );
        $featureModelMock->expects($this->any())
            ->method('isCCAndZeroAmountAuthAllowed')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/payment_features_zeroAmountAuth', $featureModelMock);

        $this->_helper->addCCForZeroAmountCheckout($block);

        $methods = $block->getMethods();
        $this->assertTrue($methods[1] instanceof Netresearch_OPS_Model_Payment_Cc);
        $this->assertFalse($methods[0] instanceof Netresearch_OPS_Model_Payment_Cc);

    }

    protected function getProtectedMethod($class, $method)
    {
        $reflection_class = new ReflectionClass(get_class($class));
        $method = $reflection_class->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }
    
    public function testIsInlinePaymentWithOrderIdIsTrueForInlineCcWithOrderId()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference'));

        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID));
        $this->replaceByMock('singleton', 'ops/config', $configMock);

        $ccMock = $this->getModelMock('ops/payment_cc', array('hasBrandAliasInterfaceSupport'));
        $ccMock->expects($this->once())
            ->method('hasBrandAliasInterfaceSupport')
            ->will($this->returnValue(true));


        $payment = $this->getModelMock('payment/info', array('getMethodInstance'));
        $payment->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($ccMock));

        $this->assertTrue(Mage::helper('ops/payment')->isInlinePaymentWithOrderId($payment));
    }

    public function testIsInlinePaymentWithOrderIdIsFalseForRedirectCcWithOrderId()
    {
        $ccMock = $this->getModelMock(
            'ops/payment_cc', array('getConfigPaymentAction', 'hasBrandAliasInterfaceSupport')
        );
        $ccMock->expects($this->any())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue('authorize'));
        $ccMock->expects($this->once())
            ->method('hasBrandAliasInterfaceSupport')
            ->will($this->returnValue(false));


        $payment = $this->getModelMock('payment/info', array('getMethodInstance'));
        $payment->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($ccMock));

        $this->assertFalse(Mage::helper('ops/payment')->isInlinePaymentWithOrderId($payment));
    }

    public function testIsInlinePaymentWithOrderIdIsFalseIfQuoteIdIsConfigured()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference'));

        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID));
        $this->replaceByMock('singleton', 'ops/config', $configMock);

        $ccMock = $this->getModelMock('ops/payment_cc', array('hasBrandAliasInterfaceSupport', 'getConfig'));
        $ccMock->expects($this->once())
            ->method('hasBrandAliasInterfaceSupport')
            ->will($this->returnValue(true));


        $payment = $this->getModelMock('payment/info', array('getMethodInstance'));
        $payment->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($ccMock));

        $this->assertFalse(Mage::helper('ops/payment')->isInlinePaymentWithOrderId($payment));
    }

    public function testIsInlinePaymentWithOrderIdIsFalseIfQuoteIdIsConfiguredForDirectDebit()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference'));

        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID));
        $this->replaceByMock('singleton', 'ops/config', $configMock);

        $payment = $this->getModelMock('payment/info', array('getMethodInstance'));
        $payment->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue(Mage::getModel('ops/payment_directDebit')));

        $this->assertFalse(Mage::helper('ops/payment')->isInlinePaymentWithOrderId($payment));
    }

    public function testIsInlinePaymentWithOrderIdIsTrueIfOrderIdIsConfiguredForDirectDebit()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference'));

        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID));
        $this->replaceByMock('singleton', 'ops/config', $configMock);


        $payment = $this->getModelMock('payment/info', array('getMethodInstance'));
        $payment->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue(Mage::getModel('ops/payment_directDebit')));

        $this->assertTrue(Mage::helper('ops/payment')->isInlinePaymentWithOrderId($payment));
    }

    public function testIsInlinePaymentWithQuoteId()
    {
        $directDebitMock = $this->getModelMock('ops/payment_directDebit', array('getConfigPaymentAction'));
        $directDebitMock->expects($this->once())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue(''));

        $payment = $this->getModelMock('payment/info', array('getMethodInstance'));
        $payment->expects($this->any())
            ->method('getMethodInstance')
            ->will($this->returnValue($directDebitMock));

        $this->assertTrue(Mage::helper('ops/payment')->isInlinePaymentWithQuoteId($payment));
    }

    public function testSetInvoicesToPaid()
    {
        /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoiceCollection */
        $invoiceCollection = $this->getResourceModelMock('sales/order_invoice_collection', array('save'));
        $invoiceCollection->addItem(Mage::getModel('sales/order_invoice'));
        $order = $this->getModelMock('sales/order', array('save', 'getInvoiceCollection'));
        $order->expects($this->any())
            ->method('getInvoiceCollection')
            ->will($this->returnValue($invoiceCollection));
        Mage::helper('ops/payment')->setInvoicesToPaid($order);
        foreach ($order->getInvoiceCollection() as $invoice) {
            $this->assertEquals(Mage_Sales_Model_Order_Invoice::STATE_PAID, $invoice->getState());
        }
    }
    
}
