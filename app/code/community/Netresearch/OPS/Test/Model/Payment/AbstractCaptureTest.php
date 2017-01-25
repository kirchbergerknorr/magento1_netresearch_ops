<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Model_Payment_AbstractCaptureTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{

    protected $testObject = null;

    /**
     * set up a clean environment for the tests
     */
    public function setUp()
    {
        parent::setUp();
        $this->testObject = Mage::getModel('ops/payment_abstract');
    }

    public function tearDown()
    {
        parent::tearDown();
        Mage::unregister('ops_auto_capture');
    }

    public function testCaptureWithAutoCapture()
    {
        Mage::register('ops_auto_capture', true);
        $payment = new Varien_Object();
        $amount  = null;
        $this->testObject->capture($payment, $amount);
    }

    public function testCaptureWithPreviousCaptureRequestLeadToRedirect()
    {
        $testOrder         = $this->getOrderMock();
        $testPayment       = $this->preparePayment($testOrder);
        $amount            = 1;
        $captureHelperMock = $this->getHelperMock('ops/order_capture', array('prepareOperation'));
        $this->replaceByMock('helper', 'ops/order_capture', $captureHelperMock);
        $this->mockDirectlinkHelperCheckDirectLinkTransact(
            $this->returnValue(true),
            $this->equalTo(Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_TRANSACTION_TYPE),
            $this->equalTo($testOrder->getId())
        );

        $message = Mage::helper('ops/data')->__(
            'You already sent a capture request. Please wait until the capture request is acknowledged.'
        );
        $this->mockDataHelperMockRedirectNoticed($testOrder->getId(), $message);

        $this->testObject->capture($testPayment, $amount);
    }

    public function testCaptureWithPreviousVoidRequestLeadToRedirect()
    {
        $testOrder         = $this->getOrderMock();
        $testPayment       = $this->preparePayment($testOrder);
        $amount            = 1;
        $captureHelperMock = $this->getHelperMock('ops/order_capture', array('prepareOperation'));
        $this->replaceByMock('helper', 'ops/order_capture', $captureHelperMock);
        $this->mockDirectlinkHelperCheckDirectLinkTransact($this->onConsecutiveCalls(false, true));
        $message = Mage::helper('ops/data')->__(
            'There is one void request waiting. Please wait until this request is acknowledged.'
        );
        $this->mockDataHelperMockRedirectNoticed($testOrder->getId(), $message);

        $this->testObject->capture($testPayment, $amount);
    }

    public function testCaptureWillPerformRequest()
    {
//        $this->markTestIncomplete();

        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $testPayment->setAdditionalInformation('paymentId', 'payID');
        $amount = 10;
        $this->mockOrderCaptureHelper();
        $requestParams   = $this->getCaptureRequestParams($amount, $testPayment);
        $testOpsResponse = $this->returnValue(
            array('STATUS' => Netresearch_OPS_Model_Status::PAYMENT_PROCESSED_BY_MERCHANT)
        );
        $this->mockApiDirectLink($requestParams, $testOpsResponse);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment'));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);
        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->capture($testPayment, $amount);
    }


    public function testCaptureWillPerformRequestWithPaymentProcessing()
    {
//        $this->markTestIncomplete();

        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $amount      = 10;
        $this->mockDirectlinkHelperCheckDirectLinkTransact($this->returnValue(false));

        $this->mockOrderCaptureHelper(array('operation' => 'SAS', 'type' => 'capture'));
        $requestParams   = $this->getCaptureRequestParams($amount, $testPayment);
        $testOpsResponse = $this->returnValue(
            array(
                'STATUS'   => Netresearch_OPS_Model_Status::PAYMENT_PROCESSING,
                'PAYID'    => 4711,
                'PAYIDSUB' => 1
            )
        );
        $this->mockApiDirectLink($requestParams, $testOpsResponse);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment'));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);
        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->capture($testPayment, $amount);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testCaptureWillPerformRequestWithInvalidResponseLeadToException()
    {
        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $testPayment->setAdditionalInformation('paymentId', 'payID');
        $amount = 10;
        $this->mockOrderCaptureHelper();
        $requestParams   = $this->getCaptureRequestParams($amount, $testPayment);
        $testOpsResponse = $this->returnValue(array('STATUS' => 320));
        $this->mockApiDirectLink($requestParams, $testOpsResponse);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment'));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);

        $statusUpdateMock = $this->getModelMock('ops/status_update', array('updateStatusFor'));
        $this->replaceByMock('model', 'ops/status_update', $statusUpdateMock);
        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->capture($testPayment, $amount);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testCaptureWillPerformRequestWithExceptionWillThrowException()
    {
        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $amount      = 10;
        $this->mockOrderCaptureHelper();
        $requestParams   = $this->getCaptureRequestParams($amount, $testPayment);
        $testOpsResponse = $this->throwException(new Exception('foo'));
        $this->mockApiDirectLink($requestParams, $testOpsResponse);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment'));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);
        $statusUpdateMock = $this->getModelMock('ops/status_update', array('updateStatusFor'));
        $this->replaceByMock('model', 'ops/status_update', $statusUpdateMock);

        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->capture($testPayment, $amount);
    }

    protected function mockOrderCaptureHelper($returnValue = array('operation' => 'SAS'))
    {
        $captureHelperMock = $this->getHelperMock('ops/order_capture', array('prepareOperation'));
        $captureHelperMock->expects($this->once())
            ->method('prepareOperation')
            ->will($this->returnValue($returnValue));
        $this->replaceByMock('helper', 'ops/order_capture', $captureHelperMock);
    }

    /**
     * @param $firstArg
     * @param $secondArg
     */
    protected function mockDataHelperMockRedirectNoticed($firstArg, $secondArg, $returnValue = true)
    {
        $dataHelperMock = $this->getHelperMock('ops/data', array('redirectNoticed'));
        $dataHelperMock->expects($this->once())
            ->method('redirectNoticed')
            ->with($this->equalTo($firstArg), $this->equalTo($secondArg))
            ->will($this->returnValue($returnValue));

        $this->replaceByMock('helper', 'ops/data', $dataHelperMock);
    }

    /**
     * @return array
     */
    protected function preparePayment($order, $method = 'ops_iDeal')
    {
        $payment = Mage::getModel('sales/order_payment');
        $payment->setOrder($order);
        $payment->setMethod($method);
        $payment->setAdditionalInformation('paymentId', 'payID');

        $order->setPayment($payment);
        return $payment;
    }

    /**
     * @param $order
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOrderMock()
    {
        $order = $this->getModelMock('sales/order', array('save', 'load', '_beforeSave'));
        $order->expects($this->any())
            ->method('load')
            ->will($this->returnValue($order));
        $order->setId(1);
        $this->replaceByMock('model', 'sales/order', $order);

        return $order;
    }

    /**
     * @param $amount
     * @param $testPayment
     *
     * @return array
     */
    protected function getCaptureRequestParams($amount, $testPayment)
    {
        $requestParams = array(
            'AMOUNT'    => Mage::helper('ops/data')->getAmount($amount),
            'PAYID'     => $testPayment->getAdditionalInformation('paymentId'),
            'OPERATION' => Mage::helper('ops/order_capture')->determineOperationCode($testPayment, $amount),
            'CURRENCY'  => Mage::app()->getStore()->getBaseCurrencyCode()
        );

        return $requestParams;
    }

    /**
     * @param $requestParams
     * @param $testOpsResponse
     */
    protected function mockApiDirectLink($requestParams, $testOpsResponse)
    {
        $apiDirectLinkMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $apiDirectLinkMock->expects($this->once())
            ->method('performRequest')
            ->with(
                $this->equalTo($requestParams),
                $this->equalTo(Mage::getModel('ops/config')->getDirectLinkGatewayPath()),
                $this->equalTo(null)
            )
            ->will($testOpsResponse);
        $this->replaceByMock('model', 'ops/api_directlink', $apiDirectLinkMock);
    }

    protected function mockDirectlinkHelperCheckDirectLinkTransact($will, $arg1 = null, $arg2 = null)
    {

        $helperMock = $this->getHelperMock('ops/directlink', array('checkExistingTransact', 'directLinkTransact'));
        $helperMock->expects($this->any())
            ->method('checkExistingTransact')
            ->with($this->getConstraintForArg($arg1), $this->getConstraintForArg($arg2))
            ->will($will);
        $this->replaceByMock('helper', 'ops/directlink', $helperMock);
    }

    protected function getConstraintForArg($arg)
    {
        if (null === $arg) {
            $arg = $this->anything();
        }

        return $arg;
    }


}