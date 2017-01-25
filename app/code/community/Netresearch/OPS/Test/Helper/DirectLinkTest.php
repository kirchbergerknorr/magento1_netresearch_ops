<?php
class Netresearch_OPS_Test_Helper_DirectLinkTest
    extends Netresearch_OPS_Test_Model_Response_TestCase
{
    public function setUp()
    {
        parent::setup();
        $this->_helper = Mage::helper('ops/directlink');
        $transaction = Mage::getModel('sales/order_payment_transaction');
        $transaction->setAdditionalInformation(
            'arrInfo', serialize(
                array(
                'amount' => '184.90'
                )
            )
        );
        $transaction->setIsClosed(0);
        $this->_transaction = $transaction;
        $this->_order = Mage::getModel('sales/order');
        $this->_order->setGrandTotal('184.90');
        $this->_order->setBaseGrandTotal('184.90');
    }

    public function testDeleteActions()
    {
        $this->assertFalse(
            $this->_helper->isValidOpsRequest(
                $this->_transaction,
                $this->_order,
                array('STATUS'=> Netresearch_OPS_Model_Status::PAYMENT_DELETED)
            )
        );
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, array('STATUS'=> Netresearch_OPS_Model_Status::PAYMENT_DELETION_PENDING)));
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, array('STATUS'=> Netresearch_OPS_Model_Status::PAYMENT_DELETION_UNCERTAIN)));
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, array('STATUS'=> Netresearch_OPS_Model_Status::PAYMENT_DELETION_REFUSED)));
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, array('STATUS'=> Netresearch_OPS_Model_Status::PAYMENT_DELETION_OK)));
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, array('STATUS'=> Netresearch_OPS_Model_Status::DELETION_HANDLED_BY_MERCHANT)));
    }

    public function testRefundActions()
    {

        $opsRequest = array(
            'STATUS' => Netresearch_OPS_Model_Status::REFUNDED,
            'amount' => '184.90'
        );
        $this->assertFalse($this->_helper->isValidOpsRequest(null, $this->_order, $opsRequest), 'Refund should not be possible without open transactions');
        $this->assertTrue($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, $opsRequest), 'Refund should be possible with open transactions');
        $opsRequest['amount'] = '14.90';
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, $opsRequest), 'Refund should NOT be possible because of differing amount');
    }

    public function testCancelActions()
    {
        $opsRequest = array(
            'STATUS' => Netresearch_OPS_Model_Status::AUTHORIZED_AND_CANCELLED,
            'amount' => '184.90'
        );
        $this->assertFalse($this->_helper->isValidOpsRequest(null, $this->_order, $opsRequest), 'Cancel should not be possible without open transactions');
        $this->assertTrue($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, $opsRequest), 'Cancel should be possible with open transactions');
        $opsRequest['amount'] = '14.90';
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, $opsRequest), 'Cancel should NOT be possible because of differing amount');
    }

    public function testCaptureActions()
    {
        $opsRequest = array(
            'STATUS' => Netresearch_OPS_Model_Status::PAYMENT_REQUESTED,
            'amount' => '184.90'
        );
        $this->assertTrue($this->_helper->isValidOpsRequest(null, $this->_order, $opsRequest), 'Capture should be possible because of no open transactions and matching amount');
        $opsRequest['amount'] = '14.90';
        $this->assertFalse($this->_helper->isValidOpsRequest($this->_transaction, $this->_order, $opsRequest), 'Capture should NOT be possible because of differing amount');
    }

    public function testCleanupParameters()
    {
        $expected = 123.45;
        $result = $this->_helper->formatAmount('123.45');
        $this->assertEquals($expected, $result);

        $result = $this->_helper->formatAmount('\'123.45\'');
        $this->assertEquals($expected, $result);

        $result = $this->_helper->formatAmount('"123.45"');
        $this->assertEquals($expected, $result);

        $expected = $this->_helper->formatAmount(0.3);
        $result = $this->_helper->formatAmount(0.1 + 0.2);
        $this->assertEquals($expected . '', $result . '');
        $this->assertEquals((float) $expected, (float) $result);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackCaptureSuccess()
    {
        $rssSession = $this->mockSession('rss/session')->disableOriginalConstructor();
        $this->replaceByMock('model', 'rss/session', $rssSession);
        $adminSession = $this->mockSession('admin/session')->disableOriginalConstructor();
        $this->replaceByMock('model', 'admin/session', $adminSession);
        $this->mockEmailHelper($this->once());

        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation('status', 9);
            return $order->getPayment();
        };

        $captureHelper = $this->getHelperMock('ops/order_capture', array('acceptCapture'));
        $captureHelper->expects($this->any())
            ->method('acceptCapture')
            ->will($this->returnCallback($closure));
        $this->replaceByMock('helper', 'ops/order_capture', $captureHelper);
        $params = array('STATUS' => Netresearch_OPS_Model_Status::PAYMENT_REQUESTED);
        $directlinkHelperMock->processFeedback($order, $params);
        $this->assertEquals(9, $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackRefundSuccess()
    {
        $this->mockEmailHelper($this->never());

        $mock = $this->getModelMock('sales/order', array('getBillingAddress'));
        $mock->expects($this->any())
            ->method('getBillingAddress')
            ->will($this->returnValue(new Varien_Object()));
        $this->replaceByMock('model', 'sales/order', $mock);

        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $params = array('STATUS' => Netresearch_OPS_Model_Status::REFUNDED, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $this->assertEquals($params['STATUS'], $order->getPayment()->getAdditionalInformation('status'));
    }


    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackRefundWithStatusEightyFiveSuccess()
    {
        $this->mockEmailHelper($this->never());

        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));



        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation(
                'status',
                Netresearch_OPS_Model_Status::REFUND_PROCESSED_BY_MERCHANT
            );
            return $order->getPayment();
        };

        $refundHelper = $this->getHelperMock('ops/order_refund', array('createRefund'));
        $refundHelper->expects($this->any())
            ->method('createRefund')
            ->will($this->returnCallback($closure));
        $this->replaceByMock('helper', 'ops/order_refund', $refundHelper);
        $params = array('STATUS' => Netresearch_OPS_Model_Status::REFUND_PROCESSED_BY_MERCHANT, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $this->assertEquals($params['STATUS'], $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackPaymentWaiting()
    {
        $this->mockEmailHelper($this->once());

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $cntBefore = $order->getStatusHistoryCollection()->count();


        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation(
                'status',
                Netresearch_OPS_Model_Status::PAYMENT_PROCESSING
            );
            return $order->getPayment();
        };

        $params = array('STATUS' => Netresearch_OPS_Model_Status::PAYMENT_PROCESSING, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $cntAfter = $order->getStatusHistoryCollection()->count();
        $this->assertTrue($cntBefore < $cntAfter);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackPaymentRefused()
    {
        // mail sending is triggered but getEmailSent takes effect
        $this->mockEmailHelper($this->once());

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock(
            'ops/directlink',
            array('isValidOpsRequest', 'closePaymentTransaction')
        );
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation(
                'status',
                Netresearch_OPS_Model_Status::PAYMENT_REFUSED
            );
            return $order->getPayment();
        };

        $params = array('STATUS' => Netresearch_OPS_Model_Status::PAYMENT_REFUSED, 'PAYID' => '4711');

        $directlinkHelperMock->processFeedback($order, $params);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackRefundWaiting()
    {
        $this->mockEmailHelper($this->never());

        $paymentMock = $this->getModelMock('core/resource_transaction', array('save'));
        $this->replaceByMock('model', 'core/resource_transaction', $paymentMock);

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);

        $creditMemo = $this->getModelMock('sales/order_creditmemo', array('_beforeSave'));
        $order->getPayment()->setCreatedCreditMemo($creditMemo);
        /** @var Netresearch_OPS_Helper_Directlink $directlinkHelperMock */
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $cntBefore = $order->getStatusHistoryCollection()->count();


        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation(
                'status',
                Netresearch_OPS_Model_Status::REFUND_PENDING
            );
            return $order->getPayment();
        };

        $refundHelper = $this->getHelperMock('ops/order_refund', array('createRefund'));
        $refundHelper->expects($this->any())
            ->method('createRefund')
            ->will($this->returnCallback($closure));
        $this->replaceByMock('helper', 'ops/order_refund', $refundHelper);
        $params = array('STATUS' => Netresearch_OPS_Model_Status::REFUND_PENDING, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $cntAfter = $order->getStatusHistoryCollection()->count();
        $this->assertTrue($cntBefore < $cntAfter);

    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackRefundRefused()
    {
        $this->mockEmailHelper($this->never());

        $transMock = $this->getModelMock('core/resource_transaction', array('save'));
        $this->replaceByMock('model', 'core/resource_transaction', $transMock);

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock(
            'ops/directlink',
            array('isValidOpsRequest', 'closePaymentTransaction')
        );
        $creditMemo = $this->getModelMock('sales/order_creditmemo', array('_beforeSave'));
        $order->getPayment()->setCreatedCreditMemo($creditMemo);
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation(
                'status',
                Netresearch_OPS_Model_Status::REFUND_PENDING
            );
            return $order->getPayment();
        };

        $refundHelper = $this->getHelperMock('ops/order_refund', array('createRefund'));
        $refundHelper->expects($this->any())
            ->method('createRefund')
            ->will($this->returnCallback($closure));
        $this->replaceByMock('helper', 'ops/order_refund', $refundHelper);
        $params = array('STATUS' => Netresearch_OPS_Model_Status::REFUND_REFUSED, 'PAYID' => '4711');

        $directlinkHelperMock->processFeedback($order, $params);

        $this->assertEquals(Netresearch_OPS_Model_Status::REFUND_REFUSED, $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackVoidSuccess()
    {
        $this->mockEmailHelper($this->never());
        $this->mockOrderConfig();

        $transMock = $this->getModelMock('core/resource_transaction', array('save'));
        $this->replaceByMock('model', 'core/resource_transaction', $transMock);

        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));



        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation('status', 6);
            return $order->getPayment();
        };

        $voidHelper = $this->getHelperMock('ops/order_void', array('acceptVoid'));
        $voidHelper->expects($this->any())
            ->method('acceptVoid')
            ->will($this->returnCallback($closure));
        $this->replaceByMock('helper', 'ops/order_void', $voidHelper);
        $params = array('STATUS' => Netresearch_OPS_Model_Status::AUTHORIZED_AND_CANCELLED, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $this->assertEquals($params['STATUS'], $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackVoidWaiting()
    {
        $this->mockEmailHelper($this->never());

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        /** @var Netresearch_OPS_Helper_Directlink $directlinkHelperMock */
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $params = array('STATUS' => Netresearch_OPS_Model_Status::DELETION_WAITING, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $this->assertNotEmpty($order->getPayment()->getMessage());

    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackVoidRefused()
    {
        $this->mockEmailHelper($this->never());

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock(
            'ops/directlink',
            array('isValidOpsRequest')
        );
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation(
                'status',
                Netresearch_OPS_Model_Status::DELETION_REFUSED
            );
            return $order->getPayment();
        };

        $refundHelper = $this->getHelperMock('ops/order_void', array('acceptVoid'));
        $refundHelper->expects($this->any())
            ->method('acceptVoid')
            ->will($this->returnCallback($closure));
        $this->replaceByMock('helper', 'ops/order_void', $refundHelper);
        $params = array('STATUS' => Netresearch_OPS_Model_Status::DELETION_REFUSED, 'PAYID' => '4711');


        $directlinkHelperMock->processFeedback($order, $params);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackAuthorizeChanged()
    {
        // mail sending is triggered but getEmailSent takes effect
        $this->mockEmailHelper($this->once());
        $this->mockOrderConfig();

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $cntBefore = $order->getStatusHistoryCollection()->count();

        $params = array('STATUS' => Netresearch_OPS_Model_Status::AUTHORIZED, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $cntAfter = $order->getStatusHistoryCollection()->count();
        $this->assertTrue($cntBefore < $cntAfter);

    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testProcessFeedbackAuthorizeKwixoAccepted()
    {
        $this->mockEmailHelper($this->once());
        $this->mockOrderConfig();

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(27);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $cntBefore = $order->getStatusHistoryCollection()->count();


        $closure = function ($order, $params = array()) {
            $order->getPayment()->setAdditionalInformation(
                'status', Netresearch_OPS_Model_Status::AUTHORIZED
            );
            return $order->getPayment();
        };

        $paymentHelper = $this->getHelperMock('ops/payment', array('acceptOrder'));
        $paymentHelper->expects($this->any())
            ->method('acceptOrder')
            ->will($this->returnCallback($closure));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelper);

        $params = array('STATUS' => Netresearch_OPS_Model_Status::AUTHORIZED, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);
        $this->assertEquals($params['STATUS'], $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Can not handle status 4711.
     */
    public function testProcessFeedbackUnknownStatus()
    {
        $this->mockEmailHelper($this->never());

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));


        $params = array('STATUS' => 4711, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);

    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage  Ingenico ePayments status 0, the action failed.
     */
    public function testProcessFeedbackInvalidStatus()
    {
        $helperMock = $this->getHelperMock('ops', array('isAdminSession', 'sendTransactionalEmail'));
        $helperMock->expects($this->once())
            ->method('isAdminSession')
            ->will($this->returnValue(false));
        $helperMock->expects($this->never())
            ->method('sendTransactionalEmail');
        $this->replaceByMock('helper', 'ops', $helperMock);

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $order->getPayment()->setAdditionalInformation('status', 500);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $params = array('STATUS' => Netresearch_OPS_Model_Status::INVALID_INCOMPLETE, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);

    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Ingenico ePayments status 0, the action failed.
     */
    public function testProcessFeedbackInvalidStatusAsAdmin()
    {
        $helperMock = $this->getHelperMock('ops', array('isAdminSession', 'sendTransactionalEmail'));
        $helperMock->expects($this->once())
            ->method('isAdminSession')
            ->will($this->returnValue(true));
        $helperMock->expects($this->never())
            ->method('sendTransactionalEmail');
        $this->replaceByMock('helper', 'ops', $helperMock);

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(11);
        $order->getPayment()->setAdditionalInformation('status', 500);
        $directlinkHelperMock = $this->getHelperMock('ops/directlink', array('isValidOpsRequest'));
        $directlinkHelperMock->expects($this->any())
            ->method('isValidOpsRequest')
            ->will($this->returnValue(true));

        $params = array('STATUS' => Netresearch_OPS_Model_Status::INVALID_INCOMPLETE, 'PAYID' => '4711');
        $directlinkHelperMock->processFeedback($order, $params);

    }
}

