<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Model_Status_UpdateTest extends EcomDev_PHPUnit_Test_Case
{

    protected function mockSessions()
    {
        $sessionMock = $this->getModelMock('admin/session', array());
        $sessionMock->disableOriginalConstructor();
        $this->replaceByMock('singleton', 'admin/session', $sessionMock);
    }

    public function testNoUpdateForNonOpsPayments()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('checkmo');
        $order->setPayment($payment);
        $statusUpdateApi = Mage::getModel('ops/status_update');
        $statusUpdateApi->updateStatusFor($order);
        $this->assertNull($statusUpdateApi->getOrder());
    }

    public function testBuildParamsForOpsOrderWithOrderId()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_paypal');
        $order->setPayment($payment);
        $orderHelperMock = $this->getHelperMock('ops/order', array('getOpsOrderId'));
        $orderHelperMock->expects($this->once())
            ->method('getOpsOrderId')
            ->with($order, true)
            ->will($this->returnValue('#1000000'));

        $statusUpdateApiMock = $this->getModelMock('ops/status_update', array('performRequest', 'updatePaymentStatus'));
        $statusUpdateApiMock->setOrderHelper($orderHelperMock);
        $statusUpdateApiMock->updateStatusFor($order);
        $requestParams = $statusUpdateApiMock->getRequestParams();
        $this->assertArrayHasKey('ORDERID', $requestParams);
        $this->assertEquals('#1000000', $requestParams['ORDERID']);
    }

    public function testBuildParamsForOpsOrderWithQuoteId()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_directDebit');
        $order->setPayment($payment);
        $orderHelperMock = $this->getHelperMock('ops/order', array('getOpsOrderId'));
        $orderHelperMock->expects($this->once())
            ->method('getOpsOrderId')
            ->with($order, false)
            ->will($this->returnValue('100'));

        $statusUpdateApiMock = $this->getModelMock('ops/status_update', array('performRequest', 'updatePaymentStatus'));
        $statusUpdateApiMock->setOrderHelper($orderHelperMock);
        $statusUpdateApiMock->updateStatusFor($order);
        $requestParams = $statusUpdateApiMock->getRequestParams();
        $this->assertArrayHasKey('ORDERID', $requestParams);
        $this->assertEquals('100', $requestParams['ORDERID']);
    }

    public function testBuildParamsForOpsOrderWithPayId()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_directDebit');
        $payment->setAdditionalInformation('paymentId', 4711);
        $order->setPayment($payment);

        $statusUpdateApiMock = $this->getModelMock('ops/status_update', array('performRequest', 'updatePaymentStatus'));
        $statusUpdateApiMock->updateStatusFor($order);
        $requestParams = $statusUpdateApiMock->getRequestParams();
        $this->assertArrayHasKey('PAYID', $requestParams);
        $this->assertEquals(4711, $requestParams['PAYID']);
    }

    public function testPerformRequestWithPayId()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_directDebit');
        $payment->setAdditionalInformation('paymentId', 4711);
        $order->setPayment($payment);

        $statusUpdateApiMock = $this->getModelMock('ops/status_update', array('updatePaymentStatus'));
        $directLinkApiMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkApiMock->expects($this->once())
            ->method('performRequest')
            ->with(
                array(
                    'PAYID' => 4711
                ),
                Mage::getModel('ops/config')->getDirectLinkMaintenanceApiPath($order->getStoreId()),
                $order->getStoreId()
            )
            ->will($this->returnValue(array('STATUS' => 5)));
        $statusUpdateApiMock->setDirectLinkApi($directLinkApiMock);
        $statusUpdateApiMock->updateStatusFor($order);
        $opsResponse = $statusUpdateApiMock->getOpsResponse();
        $this->assertArrayHasKey('STATUS', $opsResponse);
        $this->assertEquals(5, $opsResponse['STATUS']);
    }


    public function testPerformRequestWithPayIdAndPayIdSub()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_directDebit');
        $payment->setAdditionalInformation('paymentId', 4711);
        $payment->setAdditionalInformation('status', 91);
        $payment->setLastTransId('4711/1');
        $order->setPayment($payment);

        $statusUpdateApiMock = $this->getModelMock('ops/status_update', array('updatePaymentStatus'));
        $directLinkApiMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkApiMock->expects($this->once())
            ->method('performRequest')
            ->with(
                array(
                    'PAYID' => 4711,
                    'PAYIDSUB' => 1
                ),
                Mage::getModel('ops/config')->getDirectLinkMaintenanceApiPath($order->getStoreId()),
                $order->getStoreId()
            )
            ->will($this->returnValue(array('STATUS' => 5)));
        $statusUpdateApiMock->setDirectLinkApi($directLinkApiMock);
        $statusUpdateApiMock->updateStatusFor($order);
        $opsResponse = $statusUpdateApiMock->getOpsResponse();
        $this->assertArrayHasKey('STATUS', $opsResponse);
        $this->assertEquals(5, $opsResponse['STATUS']);
    }

    public function testPerformRequestWithPayIdAndPayIdSubForRefund()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_directDebit');
        $payment->setAdditionalInformation('paymentId', 4711);
        $payment->setAdditionalInformation('status', 81);
        $payment->setLastTransId('4711/1');
        $order->setPayment($payment);

        $statusUpdateApiMock = $this->getModelMock('ops/status_update', array('updatePaymentStatus'));
        $directLinkApiMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkApiMock->expects($this->once())
            ->method('performRequest')
            ->with(
                array(
                    'PAYID' => 4711,
                    'PAYIDSUB' => 1,
                ),
                Mage::getModel('ops/config')->getDirectLinkMaintenanceApiPath($order->getStoreId()),
                $order->getStoreId()
            )
            ->will($this->returnValue(array('STATUS' => 8, 'AMOUNT' => 1,)));

        $statusUpdateApiMock->setDirectLinkApi($directLinkApiMock);
        $statusUpdateApiMock->updateStatusFor($order);
        $opsResponse = $statusUpdateApiMock->getOpsResponse();
        $this->assertArrayHasKey('STATUS', $opsResponse);
        $this->assertEquals(8, $opsResponse['STATUS']);
        $this->assertArrayHasKey('AMOUNT', $opsResponse);
        $this->assertArrayHasKey('amount', $opsResponse);
        $this->assertEquals(1, $opsResponse['AMOUNT']);
        $this->assertEquals($opsResponse['amount'], $opsResponse['AMOUNT']);
    }

    public function testUpdatePaymentStatusWithoutStatusChange()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_directDebit');
        $payment->setAdditionalInformation('paymentId', 4711);
        $payment->setAdditionalInformation('status', Netresearch_OPS_Model_Status::REFUNDED);
        $order->setPayment($payment);

        $paymentHelperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment'));
        $paymentHelperMock->expects($this->never())
            ->method('saveOpsStatusToPayment')
            ->will($this->returnValue('foo'));
        ;

        // no email on refund response type
        $dataHelperMock = $this->getHelperMock('ops/data', array('sendTransactionalEmail'));
        $dataHelperMock
            ->expects($this->never())
            ->method('sendTransactionalEmail')
            ->with($this->isInstanceOf('Mage_Sales_Model_Order'));
        $this->replaceByMock('helper', 'ops/data', $dataHelperMock);


        $statusUpdateApi = Mage::getModel('ops/status_update');
        $directLinkApiMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkApiMock->expects($this->once())
            ->method('performRequest')
            ->with(
                array(
                    'PAYID' => 4711,
                ),
                Mage::getModel('ops/config')->getDirectLinkMaintenanceApiPath($order->getStoreId()),
                $order->getStoreId()
            )
            ->will($this->returnValue(array('STATUS' => Netresearch_OPS_Model_Status::REFUNDED,)));

        $adminSessionMock = $this->getModelMock('adminhtml/session', array('init', 'save', 'addNotice'));
        $statusUpdateApi->setMessageContainer($adminSessionMock);
        $statusUpdateApi->setPaymentHelper($paymentHelperMock);
        $statusUpdateApi->setDirectLinkApi($directLinkApiMock);
        $statusUpdateApi->updateStatusFor($order);
        $opsResponse = $statusUpdateApi->getOpsResponse();
        $this->assertArrayHasKey('STATUS', $opsResponse);
        $this->assertEquals(Netresearch_OPS_Model_Status::REFUNDED, $opsResponse['STATUS']);
    }

    public function testUpdatePaymentStatusWithStatusChange()
    {
        $order = Mage::getModel('sales/order');
        $payment = $this->getModelMock('sales/order_payment', array('save'));
        $payment->setMethod('ops_directDebit');
        $payment->setAdditionalInformation('paymentId', 4711);
        $payment->setAdditionalInformation('status', 5);
        $order->setPayment($payment);
        $response = array('STATUS' => 91,);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment'));
        $paymentHelperMock->expects($this->any())
            ->method('saveOpsStatusToPayment')
            ->with($payment, $response);

        $dataHelperMock = $this->getHelperMock('ops/data', array('sendTransactionalEmail'));
        $dataHelperMock
            ->expects($this->once())
            ->method('sendTransactionalEmail')
            ->with($this->isInstanceOf('Mage_Sales_Model_Order'));
        $this->replaceByMock('helper', 'ops/data', $dataHelperMock);

        $adminSessionMock = $this->getModelMock('adminhtml/session', array('init', 'save', 'addSuccess'));

        $statusUpdateApi = Mage::getModel('ops/status_update');
        $statusUpdateApi->setMessageContainer($adminSessionMock);
        $directLinkApiMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkApiMock->expects($this->once())
            ->method('performRequest')
            ->with(
                array(
                    'PAYID' => 4711,
                ),
                Mage::getModel('ops/config')->getDirectLinkMaintenanceApiPath($order->getStoreId()),
                $order->getStoreId()
            )
            ->will($this->returnValue($response));

        $statusUpdateApi->setPaymentHelper($paymentHelperMock);
        $statusUpdateApi->setDirectLinkApi($directLinkApiMock);

        $statusUpdateApi->updateStatusFor($order);
        $opsResponse = $statusUpdateApi->getOpsResponse();
        $this->assertArrayHasKey('STATUS', $opsResponse);
        $this->assertEquals(91, $opsResponse['STATUS']);

    }

    public function testUpdatePaymentStatusWithStatusChangeOnInitialRequest()
    {
        $order = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_directDebit');
        $order->setPayment($payment);
        $response = array('STATUS' => 91,);
        $paymentHelperMock = $this->getHelperMock('ops/payment', array('saveOpsStatusToPayment', 'applyStateForOrder'));
        $paymentHelperMock->expects($this->any())
            ->method('saveOpsStatusToPayment')
            ->with($payment, $response);

        $paymentHelperMock->expects($this->any())
            ->method('applyStateForOrder')
            ->with($order, $response);
        $adminSessionMock = $this->getModelMock('adminhtml/session', array('init', 'save', 'addSuccess'));
        $statusUpdateApiMock = Mage::getModel('ops/status_update');
        $statusUpdateApiMock->setMessageContainer($adminSessionMock);
        $directLinkApiMock = $this->getModelMock('ops/api_directlink', array('performRequest'));
        $directLinkApiMock->expects($this->once())
            ->method('performRequest')
            ->with(
                array(
                    'ORDERID' => Mage::getModel('ops/config')->getConfigData('devprefix') . '',
                ),
                Mage::getModel('ops/config')->getDirectLinkMaintenanceApiPath($order->getStoreId()),
                $order->getStoreId()
            )
            ->will($this->returnValue($response));


        $statusUpdateApiMock->setPaymentHelper($paymentHelperMock);
        $statusUpdateApiMock->setDirectLinkApi($directLinkApiMock);

        $statusUpdateApiMock->updateStatusFor($order);
        $opsResponse = $statusUpdateApiMock->getOpsResponse();
        $this->assertArrayHasKey('STATUS', $opsResponse);
        $this->assertEquals(91, $opsResponse['STATUS']);

    }
}