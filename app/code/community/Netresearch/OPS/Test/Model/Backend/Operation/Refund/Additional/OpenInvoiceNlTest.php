<?php

class Netresearch_OPS_Test_Model_Backend_Operation_Refund_Additional_OpenInvoiceNlTest extends EcomDev_PHPUnit_Test_Case
{
    protected $openInvoiceNlModel = null;
    protected $testPayment = null;

    public function setUp()
    {
        parent::setUp();
        $this->openInvoiceNlModel = Mage::getModel('ops/backend_operation_refund_additional_openInvoiceNl');
        $creditMemo = Mage::getModel('sales/order_creditmemo');
        $invoice = Mage::getModel('sales/order_invoice');
        // add first item to invoice


        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(1);
        $orderItem->setQtyOrdered(2);
        $orderItem->setTaxPercent(19);

        $item = Mage::getModel('sales/order_invoice_item');
        $item->setOrderItemId(1);
        $item->setOrderItem($orderItem);
        $item->setName('Item 1');
        $item->setBasePriceInclTax(42.99);
        $item->setQty(2);
        $item->setTaxPercent(19);

        $orderItem->setQtyInvoiced(2);
        $creditMemoItem = Mage::getModel('sales/order_creditmemo_item');
        $creditMemoItem->setOrderItemId(1);
        $creditMemoItem->setOrderItem($orderItem);
        $creditMemoItem->setName('Item 1');
        $creditMemoItem->setBasePriceInclTax(42.99);
        $creditMemoItem->setQty(2);

        $creditMemo->addItem($creditMemoItem);
        $invoice->addItem($item);

        // add second item to invoice
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(2);
        $orderItem->setQtyOrdered(2);
        $orderItem->setTaxPercent(7);

        $item = Mage::getModel('sales/order_invoice_item');
        $item->setOrderItemId(2);
        $item->setOrderItem($orderItem);
        $item->setName('Item 2');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(7);

        $orderItem->setQtyInvoiced(2);
        $creditMemoItem = Mage::getModel('sales/order_creditmemo_item');
        $creditMemoItem->setOrderItemId(2);
        $creditMemoItem->setOrderItem($orderItem);
        $creditMemoItem->setName('Item 2');
        $creditMemoItem->setBasePriceInclTax(19.99);
        $creditMemoItem->setQty(2);

        $creditMemo->addItem($creditMemoItem);
        $invoice->addItem($item);
        // add shipping and discount
        $invoice->setBaseShippingInclTax(10.00);
        $order = Mage::getModel('sales/order');
        $order->setShippingDescription('SuperFunShipping');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_openInvoiceNl');
        $order->setPayment($payment);
        $invoice->setOrder($order);
        $payment->setInvoice($invoice);
        $payment->setCreditmemo($creditMemo);
        $this->testPayment = $payment;

        $sessionMock = $this->getModelMockBuilder('adminhtml/session_quote')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'adminhtml/session_quote', $sessionMock);
        $sessionMock = $this->getModelMockBuilder('checkout/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'checkout/session', $sessionMock);
        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
    }


    public function testExtractWithoutShippingAndAdjustments()
    {
        $params = array(

            'shipping_amount'     => 0,
            'adjustment_positive' => 0,
            'adjustment_negative' => 0


        );
        $this->testPayment->getCreditmemo()->addData($params);
        $this->mockRefundHelper($params);
        $result = $this->openInvoiceNlModel->extractAdditionalParams($this->testPayment);
        // refunded item
        $this->assertTrue(is_array($result));
        $this->assertTrue(0 < count($result));
        $this->assertArrayHasKey('ITEMID1', $result);
        $this->assertEquals(1, $result['ITEMID1']);
        $this->assertArrayHasKey('ITEMNAME1', $result);
        $this->assertEquals('Item 1', $result['ITEMNAME1']);
        $this->assertArrayHasKey('ITEMPRICE1', $result);
        $this->assertEquals(4299, $result['ITEMPRICE1']);
        $this->assertArrayHasKey('ITEMVATCODE1', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE1']);
        $this->assertArrayHasKey('TAXINCLUDED1', $result);
        $this->assertEquals(1, $result['TAXINCLUDED1']);
        $this->assertArrayHasKey('ITEMQUANT1', $result);
        $this->assertEquals(2, $result['ITEMQUANT1']);
        // 'ignored item'
        $this->assertArrayHasKey('ITEMID2', $result);
        $this->assertEquals(2, $result['ITEMID2']);
        $this->assertArrayHasKey('ITEMNAME2', $result);
        $this->assertEquals('Item 2', $result['ITEMNAME2']);
        $this->assertArrayHasKey('ITEMPRICE2', $result);
        $this->assertEquals(1999, $result['ITEMPRICE2']);
        $this->assertArrayHasKey('ITEMVATCODE2', $result);
        $this->assertEquals('7%', $result['ITEMVATCODE2']);
        $this->assertArrayHasKey('TAXINCLUDED2', $result);
        $this->assertEquals(1, $result['TAXINCLUDED2']);
        $this->assertArrayHasKey('ITEMQUANT2', $result);
        $this->assertEquals(2, $result['ITEMQUANT2']);
        // amount
        $this->assertArrayHasKey('AMOUNT', $result);
        $this->assertEquals(12596, $result['AMOUNT']);
    }


    public function testWithShippingAndAllAdjustments()
    {
        $params = array(

            'base_shipping_incl_tax'     => 5,
            'base_adjustment_positive' => 5,
            'base_adjustment_negative' => 10


        );
        $this->mockRefundHelper($params);
        $this->testPayment->getCreditmemo()->addData($params);

        $result = $this->openInvoiceNlModel->extractAdditionalParams($this->testPayment);
        // Test our items
        $this->assertTrue(is_array($result));
        $this->assertTrue(0 < count($result));
        $this->assertArrayHasKey('ITEMID1', $result);
        $this->assertEquals(1, $result['ITEMID1']);
        $this->assertArrayHasKey('ITEMNAME1', $result);
        $this->assertEquals('Item 1', $result['ITEMNAME1']);
        $this->assertArrayHasKey('ITEMPRICE1', $result);
        $this->assertEquals(4299, $result['ITEMPRICE1']);
        $this->assertArrayHasKey('ITEMVATCODE1', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE1']);
        $this->assertArrayHasKey('TAXINCLUDED1', $result);
        $this->assertEquals(1, $result['TAXINCLUDED1']);
        $this->assertArrayHasKey('ITEMQUANT1', $result);
        $this->assertEquals(2, $result['ITEMQUANT1']);
        $this->assertArrayHasKey('ITEMID2', $result);
        $this->assertEquals(2, $result['ITEMID2']);
        $this->assertArrayHasKey('ITEMNAME2', $result);
        $this->assertEquals('Item 2', $result['ITEMNAME2']);
        $this->assertArrayHasKey('ITEMPRICE2', $result);
        $this->assertEquals(1999, $result['ITEMPRICE2']);
        $this->assertArrayHasKey('ITEMVATCODE2', $result);
        $this->assertEquals('7%', $result['ITEMVATCODE2']);
        $this->assertArrayHasKey('TAXINCLUDED2', $result);
        $this->assertEquals(1, $result['TAXINCLUDED2']);
        $this->assertArrayHasKey('ITEMQUANT2', $result);
        $this->assertEquals(2, $result['ITEMQUANT2']);

        // shipping
        $this->assertArrayHasKey('ITEMID3', $result);
        $this->assertEquals('SHIPPING', $result['ITEMID3']);
        $this->assertArrayHasKey('ITEMNAME3', $result);
        $this->assertEquals('SuperFunShipping', $result['ITEMNAME3']);
        $this->assertArrayHasKey('ITEMPRICE3', $result);
        // note, that this is the refunded amount, not the actual shipping cost of the invoice
        $this->assertEquals(500, $result['ITEMPRICE3']);
        $this->assertArrayHasKey('ITEMVATCODE3', $result);
        $this->assertEquals('0%', $result['ITEMVATCODE3']);
        $this->assertArrayHasKey('TAXINCLUDED3', $result);
        $this->assertEquals(1, $result['TAXINCLUDED3']);
        $this->assertArrayHasKey('ITEMQUANT3', $result);
        $this->assertEquals(1, $result['ITEMQUANT3']);
        // adjustment refund
        $this->assertArrayHasKey('ITEMID4', $result);
        $this->assertEquals('ADJUSTREFUND', $result['ITEMID4']);
        $this->assertArrayHasKey('ITEMNAME3', $result);
        $this->assertEquals('Adjustment Refund', $result['ITEMNAME4']);
        $this->assertArrayHasKey('ITEMPRICE4', $result);
        $this->assertEquals(500, $result['ITEMPRICE4']);
        $this->assertArrayHasKey('ITEMVATCODE4', $result);
        $this->assertEquals('0%', $result['ITEMVATCODE4']);
        $this->assertArrayHasKey('TAXINCLUDED4', $result);
        $this->assertEquals(1, $result['TAXINCLUDED4']);
        $this->assertArrayHasKey('ITEMQUANT4', $result);
        $this->assertEquals(1, $result['ITEMQUANT4']);
        // adjustment fee
        $this->assertArrayHasKey('ITEMID5', $result);
        $this->assertEquals('ADJUSTFEE', $result['ITEMID5']);
        $this->assertArrayHasKey('ITEMNAME5', $result);
        $this->assertEquals('Adjustment Fee', $result['ITEMNAME5']);
        $this->assertArrayHasKey('ITEMPRICE5', $result);
        $this->assertEquals(-1000, $result['ITEMPRICE5']);
        $this->assertArrayHasKey('ITEMVATCODE5', $result);
        $this->assertEquals('0%', $result['ITEMVATCODE5']);
        $this->assertArrayHasKey('TAXINCLUDED5', $result);
        $this->assertEquals(1, $result['TAXINCLUDED5']);
        $this->assertArrayHasKey('ITEMQUANT5', $result);
        $this->assertEquals(1, $result['ITEMQUANT5']);
        // amount: 5+5+(-10)
        $this->assertArrayHasKey('AMOUNT', $result);
        $this->assertEquals(12596, $result['AMOUNT']);
    }

    protected function mockRefundHelper($params)
    {
        $helperMock = $this->getHelperMock(
            'ops/order_refund', array('getCreditMemoRequestParams', 'createRefundTransaction')
        );
        $helperMock->expects($this->any())
            ->method('getCreditMemoRequestParams')
            ->will($this->returnValue($params));
        $this->replaceByMock('helper', 'ops/order_refund', $helperMock);
    }


    public function tearDown()
    {
        parent::tearDown();
        $this->openInvoiceNlModel = null;
        $this->testPayment = null;
    }
}
 