<?php


class Netresearch_OPS_Test_Model_Payment_AbstractRefundTest extends EcomDev_PHPUnit_Test_Case
{

    protected $testObject = null;

    /**
     * Set up test enviroment
     */
    public function setUp()
    {
        parent::setUp();
        $this->testObject = $this->getModelMock('ops/payment_cc', array('canRefund'));
        $this->testObject->expects($this->any())
            ->method('canRefund')
            ->will($this->returnValue(true));
        $this->mockRefundHelper();
        $this->mockDataHelper();
        $this->mockPaymentHelper();
        $sessionMock = $this->getModelMockBuilder('core/session')
                            ->disableOriginalConstructor()
                            ->setMethods(null)
                            ->getMock();
        $this->replaceByMock('singleton', 'core/session', $sessionMock);
    }

    public function tearDown()
    {
        parent::tearDown();
        Mage::unregister('ops_auto_creditmemo');
    }

    /**
     *
     */
    public function testRefundWillPerformRequestWithRefundPending()
    {
        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $testPayment->setAdditionalInformation('paymentId', 'payID');
        $amount          = 10;
        $requestParams   = $this->getRequestParams($amount, $testPayment);
        $testOpsResponse = $this->returnValue(
            array('STATUS' => Netresearch_OPS_Model_Status::REFUND_UNCERTAIN)
        );
        $this->mockApiDirectLink($requestParams, $testOpsResponse);
        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->refund($testPayment, $amount);
    }

    /**
     *
     */
    public function testRefundWillPerformRequestWithRefundProcessed()
    {
        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $testPayment->setAdditionalInformation('paymentId', 'payID');
        $amount          = 10;
        $requestParams   = $this->getRequestParams($amount, $testPayment);
        $testOpsResponse = $this->returnValue(
            array('STATUS' => Netresearch_OPS_Model_Status::REFUND_PROCESSED_BY_MERCHANT)
        );
        $this->mockApiDirectLink($requestParams, $testOpsResponse);
        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->refund($testPayment, $amount);

    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testRefundWillPerformRequestWithInvalidResponseLeadToException()
    {
        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $testPayment->setAdditionalInformation('paymentId', 'payID');
        $amount          = 10;
        $requestParams   = $this->getRequestParams($amount, $testPayment);
        $testOpsResponse = $this->returnValue(
            array('STATUS' => 500)
        );
        $this->mockApiDirectLink($requestParams, $testOpsResponse);

        $statusUpdateMock = $this->getModelMock('ops/status_update', array('updateStatusFor'));
        $this->replaceByMock('model', 'ops/status_update', $statusUpdateMock);
        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->refund($testPayment, $amount);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testCaptureWillPerformRequestWithExceptionWillThrowException()
    {
        $this->mockRefundHelper();
        $testOrder   = $this->getOrderMock();
        $testPayment = $this->preparePayment($testOrder);
        $testPayment->setAdditionalInformation('paymentId', 'payID');
        $amount          = 10;
        $requestParams   = $this->getRequestParams($amount, $testPayment);
        $testOpsResponse = $this->throwException(new Exception('foo'));
        $this->mockApiDirectLink($requestParams, $testOpsResponse);

        $statusUpdateMock = $this->getModelMock('ops/status_update', array('updateStatusFor'));
        $this->replaceByMock('model', 'ops/status_update', $statusUpdateMock);
        $this->testObject->setInfoInstance($testPayment);
        $this->testObject->refund($testPayment, $amount);
    }

    /**
     * @param $amount
     * @param $testPayment
     */
    protected function getRequestParams($amount, $testPayment)
    {
        $requestParams = array(
            'AMOUNT'    => Mage::helper('ops/data')->getAmount($amount),
            'PAYID'     => $testPayment->getAdditionalInformation('paymentId'),
            'OPERATION' => Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL,
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

    /**
     * @return array
     */
    protected function preparePayment($order, $method = 'ops_cc')
    {
        $payment = Mage::getModel('sales/order_payment');
        $payment->setOrder($order);
        $order->setPayment($payment);
        $payment->setAdditionalInformation('paymentId', 'payID');
        $payment->setMethod($method);

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

    protected function mockRefundHelper()
    {
        $helperMock = $this->getHelperMock('ops/order_refund', array('getCreditMemoRequestParams', 'createRefundTransaction'));
        $params     = array(
            'creditmemo' => array(
                'items'               => array(
                    1 => array(
                        'qty' => 0
                    ),
                    2 => array(
                        'qty' => 0
                    )
                ),
                'shipping_amount'     => 0,
                'adjustment_positive' => 10,
                'adjustment_negative' => 0

            )
        );
        $helperMock->expects($this->any())
                   ->method('getCreditMemoRequestParams')
                   ->will($this->returnValue($params));
        $this->replaceByMock('helper', 'ops/order_refund', $helperMock);
    }

    protected function mockDataHelper()
    {
        $helperMock = $this->getHelperMock('ops/data', array('redirect'));
        $this->replaceByMock('helper', 'ops/data', $helperMock);
    }

    protected function mockPaymentHelper()
    {
        $helperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment', 'saveOpsRefundOperationCodeToPayment'));
        $this->replaceByMock('helper', 'ops/payment', $helperMock);
    }
}
