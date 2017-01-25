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
 * Netresearch_OPS_Test_Model_Response_Type_CaptureTest
 *
 * @category OPS
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Test_Model_Response_Type_CaptureTest
    extends Netresearch_OPS_Test_Model_Response_TestCase
{
    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testHandleResponseWithPaymentReviewAndIntermediate()
    {
        $this->mockEmailHelper($this->once());

        $order = Mage::getModel('sales/order')->load(26);

        $response = array(
            'status'   => 91,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $order->getPayment()->getMethodInstance());

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, $order->getState());
        $this->assertNotEmpty($order->getAllStatusHistory());
        $this->assertEquals($response['status'], $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testHandleResponseWithPendingPaymentAndIntermediate()
    {
        $this->mockEmailHelper($this->once());

        $order = Mage::getModel('sales/order')->load(25);

        $response = array(
            'status'   => 91,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $order->getPayment()->getMethodInstance());

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, $order->getState());
        $this->assertNotEmpty($order->getAllStatusHistory());
        $this->assertEquals($response['status'], $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testHandleResponseWithPaymentReviewAndFinal()
    {
        $this->mockEmailHelper($this->once());
        $this->mockOrderConfig();

        $order = Mage::getModel('sales/order')->load(26);

        $response = array(
            'status'   => 9,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $order->getPayment()->getMethodInstance());

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PROCESSING, $order->getState());
        $this->assertNotEmpty($order->getAllStatusHistory());
        $this->assertEquals($response['status'], $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testHandleResponseWithPendingPaymentAndFinal()
    {
        $this->mockEmailHelper($this->once());
        $this->mockOrderConfig();

        $order = Mage::getModel('sales/order')->load(25);
        $order->setBaseGrandTotal(33.33);

        $response = array(
            'status'   => 9,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var Netresearch_OPS_Model_Response_Handler $handler */
        $handler = Mage::getModel('ops/response_handler');
        $handler->processResponse($response, $order->getPayment()->getMethodInstance());

        $this->assertEquals(Mage_Sales_Model_Order::STATE_PROCESSING, $order->getState());
        $this->assertNotEmpty($order->getAllStatusHistory());
        $this->assertNotEmpty($order->getInvoiceCollection());
        $this->assertEquals($response['status'], $order->getPayment()->getAdditionalInformation('status'));
    }

    /**
     * @test
     * @loadFixture orders.yaml
     * @expectedException Mage_Core_Exception
     */
    public function testExceptionThrown()
    {
        $this->mockEmailHelper($this->never());

        $order = Mage::getModel('sales/order')->load(25);

        $response = array(
            'status'   => 43,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        /** @var Netresearch_OPS_Model_Response_Type_Capture $handler */
        $handler = Mage::getModel('ops/response_type_capture');
        $handler->handleResponse($response, $order->getPayment()->getMethodInstance());
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testAbortBecauseSameStatus()
    {
        $this->mockEmailHelper($this->never());

        $order = Mage::getModel('sales/order')->load(27);
        $order->getPayment()->setAdditionalInformation('status', 9);

        $response = array(
            'status'   => 9,
            'payid'    => 1234567,
            'payidsub' => 3,
            'amount'   => 33.33
        );

        $order->getPayment()->setLastTransId($response['payid'].'/'.$response['payidsub']);

        /** @var Netresearch_OPS_Model_Response_Type_Capture $handler */
        $handler = Mage::getModel('ops/response_type_capture');
        $handler->handleResponse($response, $order->getPayment()->getMethodInstance());
        $this->assertEquals(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $order->getState());
    }
}
