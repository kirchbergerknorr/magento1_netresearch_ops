<?php

class Netresearch_OPS_Test_Helper_Order_CaptureTest extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testOperationPartialAndTypePartial()
    {
        $helper  = Mage::helper('ops/order_capture');
        $invoice = array("items" => "foo");
        Mage::app()->getRequest()->setParam('invoice', $invoice);
        $payment = new Varien_Object();
        $order   = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        // order base_grand_total == grand_total == 119.00

        $expected = array(
            "items"     => $invoice["items"],
            "operation" => Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_PARTIAL,
            "type"      => "partial",
            "amount"    => 100.00
        );

        $payment->setBaseAmountPaidOnline(0.00);
        $amount = 100.00;
        $this->assertEquals($expected, $helper->prepareOperation($payment, $amount));

    }


    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testOperationFullAndTypePartial()
    {
        $helper  = Mage::helper('ops/order_capture');
        $invoice = array("items" => "foo");
        Mage::app()->getRequest()->setParam('invoice', $invoice);
        $payment = new Varien_Object();
        $order   = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        // order base_grand_total == grand_total == 119.00

        $expected = array(
            "items"     => $invoice["items"],
            "operation" => Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_FULL,
            "type"      => "partial",
            "amount"    => 100.00
        );

        $payment->setBaseAmountPaidOnline(19.00);
        $amount = 100.00;
        $this->assertEquals($expected, $helper->prepareOperation($payment, $amount));
    }

    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testOperationFullAndTypeFull()
    {
        $helper  = Mage::helper('ops/order_capture');
        $invoice = array("items" => "foo");
        Mage::app()->getRequest()->setParam('invoice', $invoice);
        $payment = new Varien_Object();
        $order   = Mage::getModel('sales/order')->load(11);
        $payment->setOrder($order);
        // order base_grand_total == grand_total == 119.00

        $expected = array(
            "items"     => $invoice["items"],
            "operation" => Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_FULL,
            "type"      => "full",
            "amount"    => 119.00
        );

        $payment->setBaseAmountPaidOnline(0.00);
        $amount = 119.00;
        $this->assertEquals($expected, $helper->prepareOperation($payment, $amount));
    }
}