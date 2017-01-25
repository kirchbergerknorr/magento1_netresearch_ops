<?php

class Netresearch_OPS_Test_Helper_Order_RefundTest extends EcomDev_PHPUnit_Test_Case
{

    public function setUp()
    {
        Mage::unregister('_helper/ops/order_refund');
        parent::setUp();
    }
    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testDetermineOperationCode()
    {
        /* @var $helper Netresearch_OPS_Helper_Order_Refund */
        $helper = Mage::helper('ops/order_refund');
        $payment = new Varien_Object();

        // complete refund should lead to RFS
        $order = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        $payment->setBaseAmountRefundedOnline(0.00);
        $amount = 119.00;
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL, $helper->determineOperationCode($payment, $amount));

        // complete refund should lead to RFS
        $order = Mage::getModel('sales/order')->load(16);
        $payment->setOrder($order);
        $payment->setBaseAmountRefundedOnline(0.00);
        $amount = 19.99;
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL, $helper->determineOperationCode($payment, $amount));

        // partial refund should lead to RFD
        $order = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        $payment->setBaseAmountRefundedOnline(0.00);
        $amount = 100.00;
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL, $helper->determineOperationCode($payment, $amount));

        // partial refund + new amount to refund should lead to RFS
        $order = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        $payment->setBaseAmountRefundedOnline(19.00);
        $amount = 100.00;
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL, $helper->determineOperationCode($payment, $amount));

        // partial refund + new amount to refund should lead to RFS
        $order = Mage::getModel('sales/order')->load(16);
        $payment->setOrder($order);
        $payment->setBaseAmountRefundedOnline(17.98);
        $amount = 2.01;
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL, $helper->determineOperationCode($payment, $amount));
        
        // partial refund + new amount to refund should lead to RFS
        $order = Mage::getModel('sales/order')->load(16);
        $payment->setOrder($order);
        $payment->setBaseAmountRefundedOnline(17.98);
        $amount = 2.00;
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL, $helper->determineOperationCode($payment, $amount));

    }

    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testOperationPartialAndTypePartial()
    {
        $helper  = Mage::helper('ops/order_refund');
        $creditmemo = array("creditmemo" => array("items" => "foo"));
        $helper->setCreditMemoRequestParams($creditmemo);
        $payment = new Varien_Object();
        $order   = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        // order base_grand_total == grand_total == 119.00

        $expected = array(
            "items"     => $creditmemo["creditmemo"]["items"],
            "operation" => Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_PARTIAL,
            "type"      => "partial",
            "amount"    => 100.00
        );

        $payment->setBaseAmountRefundedOnline(0.00);
        $amount = 100.00;
        $this->assertEquals($expected, $helper->prepareOperation($payment, $amount));

    }


    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testOperationFullAndTypePartial()
    {
        $helper  = Mage::helper('ops/order_refund');
        $creditmemo = array("creditmemo" => array("items" => "foo"));
        $helper->setCreditMemoRequestParams($creditmemo);
        $payment = new Varien_Object();
        $order   = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        // order base_grand_total == grand_total == 119.00

        $expected = array(
            "items"     => $creditmemo["creditmemo"]["items"],
            "operation" => Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL,
            "type"      => "partial",
            "amount"    => 100.00
        );

        $payment->setBaseAmountRefundedOnline(19.00);
        $amount = 100.00;
        $this->assertEquals($expected, $helper->prepareOperation($payment, $amount));
    }

    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testOperationFullAndTypeFull()
    {
        $helper  = Mage::helper('ops/order_refund');
        $creditmemo = array("creditmemo" => array("items" => "foo"));
        $helper->setCreditMemoRequestParams($creditmemo);
        $payment = new Varien_Object();
        $order   = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        // order base_grand_total == grand_total == 119.00

        $expected = array(
            "items"     => $creditmemo["creditmemo"]["items"],
            "operation" => Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_FULL,
            "type"      => "full",
            "amount"    => 119.00
        );

        $payment->setBaseAmountRefundedOnline(0.00);
        $amount = 119.00;
        $this->assertEquals($expected, $helper->prepareOperation($payment, $amount));
    }

}
