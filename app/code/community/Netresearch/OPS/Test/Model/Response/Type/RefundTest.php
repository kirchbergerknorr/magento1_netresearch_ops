<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * CaptureTest.php
 *
 * @category OPS
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Model_Response_Type_RefundTest
    extends Netresearch_OPS_Test_Model_Response_TestCase
{
    /**
     * @test
     * @loadFixture orders.yaml
     * @expectedException Mage_Core_Exception
     */
    public function testExceptionThrown()
    {
        /** @var Netresearch_OPS_Model_Payment_IDeal $instance */

        $order = Mage::getModel('sales/order')->load(25);
        $response = array(
            'status'   => 43,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var Netresearch_OPS_Model_Response_Type_Capture $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $order->getPayment()->getMethodInstance());
    }

    /**
     * @test
     * @loadFixture orders.yaml
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage 2 is not a refund status!
     */
    public function testExceptionThrownDueToNoRefundStatus()
    {
        /** @var Netresearch_OPS_Model_Payment_IDeal $instance */

        $order = Mage::getModel('sales/order')->load(25);
        $response = array(
            'status'   => Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var Netresearch_OPS_Model_Response_Type_Capture $handler */
        $handler = Mage::getModel('ops/response_type_refund');
        $handler->handleResponse($response, $order->getPayment()->getMethodInstance());
    }


    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testAbortBecauseSameStatus()
    {
        $order = Mage::getModel('sales/order')->load(27);
        $order->getPayment()->setAdditionalInformation('status', 8);
        $response = array(
            'status'   => Netresearch_OPS_Model_Status::REFUNDED,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );
        $order->getPayment()->setLastTransId($response['payid'].'/'.$response['payidsub']);
        /** @var Netresearch_OPS_Model_Response_Type_Capture $handler */
        $handler = Mage::getModel('ops/response_type_refund');
        $handler->handleResponse($response, $order->getPayment()->getMethodInstance());
        $this->assertEquals(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testCreditMemoStateOpenRefundSuccess()
    {
        $this->mockOrderConfig();

        $order = Mage::getModel('sales/order')->load(27);
        $response = array(
            'status'   => Netresearch_OPS_Model_Status::REFUNDED,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );
        $order->setBillingAddress(Mage::getModel('sales/order_address'));
        $invoice = Mage::getModel('sales/order_invoice');
        $invoice->setOrder($order);
        $creditMemo = Mage::getModel('sales/order_creditmemo');
        $creditMemo->setInvoice($invoice);
        $creditMemo->setId('1234567/3');
        $creditMemo->setOrder($order);
        $creditMemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN);
        $creditMemoMock = $this->getModelMock('sales/order_creditmemo', array('load'));
        $creditMemoMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($creditMemo));
        $order->getPayment()->setOrder($order);

        $this->replaceByMock('model', 'sales/order_creditmemo', $creditMemoMock);

        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $order->getPayment()->getMethodInstance());

        $this->assertEquals(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED, $creditMemo->getState());

    }


    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testCreditMemoStateOpenRefundRefused()
    {
        $this->mockOrderConfig();

        $creditMemo = Mage::getModel('sales/order_creditmemo')->load(122);
        $response = array(
            'status'   => Netresearch_OPS_Model_Status::REFUND_REFUSED,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        $creditMemoMock = $this->getModelMock('sales/order_creditmemo', array('save', 'cancel', 'load'));
        $creditMemoMock->expects($this->any())
            ->method('save')
            ->will($this->returnSelf());
        $creditMemoMock->expects($this->any())
            ->method('cancel')
            ->will($this->returnSelf());
        $creditMemoMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($creditMemo));
        $this->replaceByMock('model', 'sales/order_creditmemo', $creditMemoMock);

        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $creditMemo->getOrder()->getPayment()->getMethodInstance());

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PROCESSING, $creditMemo->getOrder()->getState());
        $this->assertEquals(Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED, $creditMemo->getState());
    }


    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testCreditMemoRefundFinalState()
    {
        $this->mockOrderConfig();

        $order = Mage::getModel('sales/order')->load(25);

        $response = array(
            'status'   => Netresearch_OPS_Model_Status::REFUNDED,
            'payid'    => 12345679,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var  Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $creditMemoMock = $this->getModelMock('sales/order_creditmemo', array('getInvoice'));
        $creditMemoMock->expects($this->any())
            ->method('getInvoice')
            ->will($this->returnValue($invoice));

        $this->replaceByMock('model', 'sales/order_creditmemo', $creditMemoMock);


        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $order->getPayment()->getMethodInstance());

        $this->assertNotEmpty($order->getAllStatusHistory());
        $this->assertNotEmpty($order->getInvoiceCollection());
        $this->assertEquals($response['status'], $order->getPayment()->getAdditionalInformation('status'));

    }


    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testCreditMemoRefundPendingState()
    {
        $this->mockOrderConfig();

        $creditMemo = Mage::getModel('sales/order_creditmemo');

        $paymentMock = $this->getModelMock('sales/order_payment', array('getCreatedCreditMemo'));
        $paymentMock->expects($this->any())
            ->method('getCreatedCreditMemo')
            ->will($this->returnValue($creditMemo));
        $this->replaceByMock('model', 'sales/order_payment', $paymentMock);


        $payment = Mage::getModel('sales/order_payment')->load(25);
        $order   =  Mage::getModel('sales/order')->load(25);
        $payment->setOrder($order);

        $response = array(
            'status'   => Netresearch_OPS_Model_Status::REFUND_PENDING,
            'payid'    => 1234567534,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var  Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = Mage::getModel('sales/order_invoice');
        $creditMemoMock = $this->getModelMock('sales/order_creditmemo', array('getInvoice'));
        $creditMemoMock->expects($this->any())
            ->method('getInvoice')
            ->will($this->returnValue($invoice));

        $this->replaceByMock('model', 'sales/order_creditmemo', $creditMemoMock);


        $transactionMock = $this->getModelMock('core/resource_transaction', array('save'));
        $transactionMock->expects($this->any())
            ->method('save')
            ->will($this->returnSelf());
        $this->replaceByMock('model', 'core/resource_transaction', $transactionMock);



        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $payment->getMethodInstance());


        $this->assertNotEmpty($order->getAllStatusHistory());
        $this->assertNotEmpty($order->getInvoiceCollection());
        $this->assertEquals($response['status'], $payment->getAdditionalInformation('status'));
        $this->assertEquals(Mage_Sales_Model_Order_Creditmemo::STATE_OPEN, $creditMemo->getState());
    }
}
