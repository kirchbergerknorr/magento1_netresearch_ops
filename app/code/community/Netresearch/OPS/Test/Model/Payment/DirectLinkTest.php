<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Model_Payment_DirectLinkTest extends EcomDev_PHPUnit_Test_Case
{

    protected $testObjects = array();


    public function setUp()
    {
        parent::setUp();
        $payment = Mage::getModel('sales/order_payment');
        $payment->setAdditionalInformation('CC_BRAND', 'VISA')
            ->setMethod('ops_cc');
        $this->testObjects[] = Mage::getModel('ops/payment_cc')->setInfoInstance($payment);
        $payment2 = clone($payment);
        $payment2->setMethod('ops_directDebit');
        $this->testObjects[] = Mage::getModel('ops/payment_directDebit')->setInfoInstance($payment2);
    }


    public function testInstanceOfAbstractPayment()
    {
        foreach ($this->testObjects as $testObject) {
            $this->assertTrue($testObject instanceof Netresearch_OPS_Model_Payment_Abstract);
        }
    }


    public function testGetConfigPaymentActionReturnsMageAuthorizeWithOrderIdAsMerchRef()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference', 'getPaymentAction', 'getInlinePaymentCcTypes'));
        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID));
        $configMock->expects($this->any())
            ->method('getPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE));
        $configMock->expects($this->any())
            ->method('getInlinePaymentCcTypes')
            ->will($this->returnValue(array('VISA')));
        foreach ($this->testObjects as $testObject) {
            $testObject->setConfig($configMock);
            $this->assertEquals(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE, $testObject->getConfigPaymentAction());
        }
    }

    public function testGetConfigPaymentActionReturnsAuthorizeStringWithQuoteIdAsMerchRef()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference', 'getPaymentAction'));
        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID));
        $configMock->expects($this->any())
            ->method('getPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE));
        foreach ($this->testObjects as $testObject) {
            $testObject->setConfig($configMock);
            $this->assertEquals(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE, $testObject->getConfigPaymentAction());
        }
    }


    public function testGetConfigPaymentActionReturnsMageAuthorizeCaptureWithOrderIdAsMerchRef()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference', 'getPaymentAction', 'getInlinePaymentCcTypes'));
        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID));
        $configMock->expects($this->any())
            ->method('getPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE));
        $configMock->expects($this->any())
            ->method('getInlinePaymentCcTypes')
            ->will($this->returnValue(array('VISA')));

        foreach ($this->testObjects as $testObject) {
            $testObject->setConfig($configMock);
            $this->assertEquals(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE, $testObject->getConfigPaymentAction());
        }
    }

    public function testGetConfigPaymentActionReturnsAuthorizeCaptureStringForDirectSaleWithQuoteIdAsMerchRef()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference', 'getPaymentAction'));
        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID));
        $configMock->expects($this->any())
            ->method('getPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE));
        foreach ($this->testObjects as $testObject) {
            $testObject->setConfig($configMock);
            $this->assertEquals(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE, $testObject->getConfigPaymentAction());
        }
    }

    public function testIsInitializeNeededReturnsFalse()
    {

        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference'));
        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID));
        foreach ($this->testObjects as $testObject) {
            $testObject->setConfig($configMock);
            $this->assertFalse($testObject->isInitializeNeeded());
        }
    }

    public function testAuthorize()
    {
        $configMock = $this->getConfigMockWithOrderId();
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);

        $fakeQuote       = $this->getFakeQuote();
        $quoteHelperMock = $this->getQuoteHelperMock($fakeQuote);

        $testMock = $this->getModelMock('ops/payment_directDebit', array('confirmPayment'));

        $dataHelperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $dataHelperMock->expects($this->once())
            ->method('isAdminSession')
            ->will($this->returnValue(true));
        $testMock->setDataHelper($dataHelperMock);

        $testMock->setQuoteHelper($quoteHelperMock);
        $testMock->expects($this->once())
            ->method('confirmPayment')
            ->with($fakeOrder, $fakeQuote, $payment);
        $testMock->setConfig($configMock);
        $testMock->authorize($payment, 100);
    }

    public function testAuthorizeWithInlineCc()
    {
        $configMock = $this->getConfigMockWithOrderId();
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);
        $fakeCc = $this->getModelMock('ops/payment_cc', array('hasBrandAliasInterfaceSupport'));
        $fakeCc->expects($this->once())
            ->method('hasBrandAliasInterfaceSupport')
            ->will($this->returnValue(true));
//        $fakeCc->expects($this->once())
//            ->method('getInfoInstance')
//            ->will($this->returnValue($payment));
        $payment->setMethodInstance($fakeCc);
        $fakeCc->setInfoInstance($payment);
        $fakeQuote       = $this->getFakeQuote();
        $quoteHelperMock = $this->getQuoteHelperMock($fakeQuote);

        $testMock = $this->getModelMock('ops/payment_cc', array('confirmPayment', 'hasBrandAliasInterfaceSupport'));
        $testMock->setQuoteHelper($quoteHelperMock);
        $testMock->expects($this->once())
            ->method('confirmPayment')
            ->with($fakeOrder, $fakeQuote, $payment);
        $testMock->expects($this->any())
            ->method('hasBrandAliasInterfaceSupport')
            ->will($this->returnValue(true));
        ;
        $testMock->setInfoInstance($payment);
        $testMock->setConfig($configMock);
        $testMock->authorize($payment, 100);
    }


    public function testConfirmPaymentWithResponse()
    {
        $configMock = $this->getConfigMockWithOrderId();
        /** @var Mage_Sales_Model_Order $fakeOrder */
        $fakeOrder = $this->getFakeOrder();
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getFakePayment($fakeOrder);

        $fakeQuote       = $this->getFakeQuote();
        $quoteHelperMock = $this->getQuoteHelperMock($fakeQuote);
        /** @var Netresearch_OPS_Model_Payment_DirectDebit $testMock */
        $testMock = $this->getModelMock('ops/payment_directDebit', array('handleAdminPayment', 'performPreDirectLinkCallActions', 'performPostDirectLinkCallActions'));
        $testMock->setInfoInstance($payment);
        $dataHelperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $dataHelperMock->expects($this->once())
            ->method('isAdminSession')
            ->will($this->returnValue(true));
        $testMock->setDataHelper($dataHelperMock);

        $requestParams = array('ORDERID' => '123');
        $requestParamsHelperMock = $this->getHelperMock('ops/directDebit', array('getDirectLinkRequestParams'));

        $requestParamsHelperMock->expects($this->once())
            ->method('getDirectLinkRequestParams')
            ->with($fakeQuote, $fakeOrder, $payment)
            ->will($this->returnValue($requestParams));
        $testMock->setRequestParamsHelper($requestParamsHelperMock);

        $response = array('PAYID' => 4711, 'ORDERID' => '123', 'STATUS' => 5);
        $directLinkHelperMock = $this->getHelperMock('ops/directlink', array('performDirectLinkRequest'));
        $directLinkHelperMock->expects($this->once())
            ->method('performDirectLinkRequest')
            ->with($fakeQuote, $requestParams, 0)
            ->will($this->returnValue($response));
        $testMock->setDirectLinkHelper($directLinkHelperMock);
        $testMock->setQuoteHelper($quoteHelperMock);

        $testMock->setConfig($configMock);
        $testMock->authorize($payment, 100);

        $this->assertEquals(5, $payment->getAdditionalInformation('status'));
        $this->assertNotEmpty($fakeOrder->getAllStatusHistory());
    }

    public function testConfirmPaymentWithInvalidResponse()
    {
        $configMock = $this->getConfigMockWithOrderId();
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);

        $fakeQuote       = $this->getFakeQuote();
        $quoteHelperMock = $this->getQuoteHelperMock($fakeQuote);
        $testMock = $this->getModelMock('ops/payment_directDebit', array('handleAdminPayment', 'performPreDirectLinkCallActions', 'performPostDirectLinkCallActions'));

        $dataHelperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $dataHelperMock->expects($this->once())
            ->method('isAdminSession')
            ->will($this->returnValue(true));
        $testMock->setDataHelper($dataHelperMock);

        $requestParams = array('ORDERID' => '123');
        $requestParamsHelperMock = $this->getHelperMock('ops/directDebit', array('getDirectLinkRequestParams'));

        $requestParamsHelperMock->expects($this->once())
            ->method('getDirectLinkRequestParams')
            ->with($fakeQuote, $fakeOrder, $payment)
            ->will($this->returnValue($requestParams));
        $testMock->setRequestParamsHelper($requestParamsHelperMock);

        $response = array();
        $directLinkHelperMock = $this->getHelperMock('ops/directlink', array('performDirectLinkRequest'));
        $directLinkHelperMock->expects($this->once())
            ->method('performDirectLinkRequest')
            ->with($fakeQuote, $requestParams, 0)
            ->will($this->returnValue($response));
        $testMock->setDirectLinkHelper($directLinkHelperMock);
        $testMock->setQuoteHelper($quoteHelperMock);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('handleUnknownStatus'));
        $paymentHelperMock->expects($this->once())
            ->method('handleUnknownStatus')
            ->with($fakeOrder);
        $testMock->setPaymentHelper($paymentHelperMock);
        $testMock->setConfig($configMock);
        $testMock->authorize($payment, 100);

    }


    /**
     * @expectedException Mage_Core_Exception
     */
    public function testConfirmPaymentWithException()
    {
        $configMock = $this->getConfigMockWithOrderId();
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);

        $fakeQuote       = $this->getFakeQuote();
        $quoteHelperMock = $this->getQuoteHelperMock($fakeQuote);
        $testMock = $this->getModelMock('ops/payment_directDebit', array('handleAdminPayment', 'performPreDirectLinkCallActions', 'performPostDirectLinkCallActions', 'getOnepage'));

        $dataHelperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $dataHelperMock->expects($this->once())
            ->method('isAdminSession')
            ->will($this->returnValue(true));
        $testMock->setDataHelper($dataHelperMock);

        $fakeOnepage = new Varien_Object();
        $fakeOnepage->setCheckout(new Varien_Object());
        $testMock->expects($this->once())
            ->method('getOnepage')
            ->will($this->returnValue($fakeOnepage));

        $requestParams = array('ORDERID' => '123');
        $requestParamsHelperMock = $this->getHelperMock('ops/directDebit', array('getDirectLinkRequestParams'));

        $requestParamsHelperMock->expects($this->once())
            ->method('getDirectLinkRequestParams')
            ->with($fakeQuote, $fakeOrder, $payment)
            ->will($this->returnValue($requestParams));
        $testMock->setRequestParamsHelper($requestParamsHelperMock);

        $fakeValidator = $this->getModelMock('ops/validator_parameter_length', array('isValid'));
        $fakeValidator->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $validationMock = $this->getModelMock('ops/validator_parameter_factory', array('getValidatorFor'));
        $validationMock->expects($this->once())
            ->method('getValidatorFor')
            ->will($this->returnValue($fakeValidator));

        $testMock->setValidationFactory($validationMock);
        $testMock->setQuoteHelper($quoteHelperMock);

        $testMock->setConfig($configMock);
        $testMock->authorize($payment, 100);

    }


    public function testGetDefaultHelpers()
    {
        foreach ($this->testObjects as $testObject) {
            $this->assertTrue($testObject->getQuoteHelper() instanceof Netresearch_OPS_Helper_Quote);
            $this->assertTrue($testObject->getDirectLinkHelper() instanceof Netresearch_OPS_Helper_Directlink);
            $this->assertTrue($testObject->getPaymentHelper() instanceof Netresearch_OPS_Helper_Payment);
        }
    }


    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigMockWithOrderId()
    {
        $configMock = $this->getModelMock('ops/config', array('getInlineOrderReference', 'getPaymentAction'));
        $configMock->expects($this->any())
            ->method('getInlineOrderReference')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID));
        $configMock->expects($this->any())
            ->method('getPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE));
        return $configMock;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFakeOrder()
    {
        $fakeOrder = $this->getModelMock('sales/order', array('save', '_beforeSave'));
        $fakeOrder->setState(Mage_Sales_Model_Order::STATE_NEW);
        return $fakeOrder;
    }

    /**
     * @param $fakeOrder
     *
     * @return Varien_Object
     */
    protected function getFakePayment($fakeOrder)
    {
        $payment = $this->getModelMock('sales/order_payment', array('save'));
        $payment->setMethodInstance(Mage::getModel('ops/payment_directDebit'));
        $payment->setOrder($fakeOrder);
        $fakeOrder->setPayment($payment);
        return $payment;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFakeQuote()
    {
        $fakeQuote = $this->getModelMock('sales/quote', array('save'));
        return $fakeQuote;
    }

    /**
     * @param $fakeQuote
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQuoteHelperMock($fakeQuote)
    {
        $quoteHelperMock = $this->getHelperMock('ops/quote', array('getQuote'));
        $quoteHelperMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($fakeQuote));
        return $quoteHelperMock;
    }

    public function testCaptureDirectSaleDirectDebit()
    {
        $directDebitMock = $this->getModelMock('ops/payment_directDebit', array('getConfigPaymentAction', 'confirmPayment'));
        $directDebitMock->expects($this->once())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE));
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('isInlinePayment'));
        $paymentHelperMock->expects($this->once())
            ->method('isInlinePayment')
            ->with($payment)
            ->will($this->returnValue(true));



        $fakeQuote       = $this->getFakeQuote();
        $quoteHelperMock = $this->getQuoteHelperMock($fakeQuote);

        $directDebitMock->expects($this->once())
            ->method('confirmPayment')
            ->with($fakeOrder, $fakeQuote, $payment);
        $directDebitMock->setPaymentHelper($paymentHelperMock);
        $directDebitMock->setQuoteHelper($quoteHelperMock);
        $directDebitMock->capture($payment, 100);
    }

    public function testCaptureDirectSaleDirectDebitInvoice()
    {
        $directDebitMock = $this->getModelMock('ops/payment_directDebit', array('getConfigPaymentAction', 'confirmPayment'));
        $directDebitMock->expects($this->once())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE));
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);
        $payment->setAdditionalInformation('paymentId', 4711);

        $directDebitMock->expects($this->never())
            ->method('confirmPayment');
        $directDebitMock->capture($payment, 0);
    }

    public function testCaptureDirectSaleCreditCard()
    {
        $directDebitMock = $this->getModelMock('ops/payment_cc', array('getConfigPaymentAction', 'confirmPayment'));
        $directDebitMock->expects($this->once())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE));
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('isInlinePayment'));
        $paymentHelperMock->expects($this->once())
            ->method('isInlinePayment')
            ->with($payment)
            ->will($this->returnValue(true));

        $fakeQuote       = $this->getFakeQuote();
        $quoteHelperMock = $this->getQuoteHelperMock($fakeQuote);

        $directDebitMock->expects($this->once())
            ->method('confirmPayment')
            ->with($fakeOrder, $fakeQuote, $payment);
        $directDebitMock->setPaymentHelper($paymentHelperMock);
        $directDebitMock->setQuoteHelper($quoteHelperMock);
        $directDebitMock->capture($payment, 100);
    }

    public function testCaptureDirectSaleCreditCardRedirect()
    {
        $directDebitMock = $this->getModelMock('ops/payment_cc', array('getConfigPaymentAction', 'confirmPayment'));
        $directDebitMock->expects($this->once())
            ->method('getConfigPaymentAction')
            ->will($this->returnValue(Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE));
        $fakeOrder = $this->getFakeOrder();
        $payment = $this->getFakePayment($fakeOrder);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('isInlinePayment'));
        $paymentHelperMock->expects($this->once())
            ->method('isInlinePayment')
            ->with($payment)
            ->will($this->returnValue(false));


        $directDebitMock->expects($this->never())
            ->method('confirmPayment');
        $directDebitMock->setPaymentHelper($paymentHelperMock);
        $directDebitMock->capture($payment, 100);
    }
} 