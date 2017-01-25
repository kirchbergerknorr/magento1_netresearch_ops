<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */



class Netresearch_OPS_Test_Model_Backend_Operation_Capture_Additional_OpenInvoiceNlTest
    extends EcomDev_PHPUnit_Test_Case
{

    protected $openInvoiceNlModel = null;

    public function setUp()
    {
        parent::setUp();
        $this->openInvoiceNlModel = Mage::getModel('ops/backend_operation_capture_additional_openInvoiceNl');
        $this->mockSessions();
    }

    public function testExtractAdditionalParamsWithoutShipping1()
    {
        $itemsContainer = Mage::getModel('sales/order_invoice');
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(1);
        $orderItem->setQtyOrdered(2);
        $item = Mage::getModel('sales/order_invoice_item');
        $item->setOrderItemId(1);
        $item->setOrderItem($orderItem);
        $item->setName('Item');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(19);
        $itemsContainer->addItem($item);
        $payment = Mage::getModel('sales/order_payment')->setMethod('ops_openInvoiceNl');
        $payment->setInvoice($itemsContainer);
        $result = $this->openInvoiceNlModel->extractAdditionalParams($payment);
        $this->assertTrue(is_array($result));
        $this->assertTrue(0 < count($result));
        $this->assertArrayHasKey('ITEMID1', $result);
        $this->assertEquals(1, $result['ITEMID1']);
        $this->assertArrayHasKey('ITEMNAME1', $result);
        $this->assertEquals('Item', $result['ITEMNAME1']);
        $this->assertArrayHasKey('ITEMPRICE1', $result);
        $this->assertEquals(1999, $result['ITEMPRICE1']);
        $this->assertArrayHasKey('ITEMVATCODE1', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE1']);
        $this->assertArrayHasKey('TAXINCLUDED1', $result);
        $this->assertEquals(1, $result['TAXINCLUDED1']);
    }

    public function testExtractAdditionalParamsWithoutShippingButWithConfigurable()
    {
        $itemsContainer = Mage::getModel('sales/order_invoice');
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(1);
        $orderItem->setQtyOrdered(2);
        $orderItem->setProductType(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $item = Mage::getModel('sales/order_invoice_item');
        $item->setOrderItemId(1);
        $item->setOrderItem($orderItem);
        $item->setName('Item');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(19);
        $itemsContainer->addItem($item);
        $item = Mage::getModel('sales/order_invoice_item');
        $simpleOrderItem = Mage::getModel('sales/order_item');
        $simpleOrderItem->setId(1);
        $simpleOrderItem->setQtyOrdered(2);
        $simpleOrderItem->setParentItemId(1);
        $simpleOrderItem->setParentItem($orderItem);
        $item->setOrderItemId(2);
        $item->setOrderItem($simpleOrderItem);
        $item->setName('Item');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(19);
        $itemsContainer->addItem($item);
        $payment = Mage::getModel('sales/order_payment')->setMethod('ops_openInvoiceNl');
        $payment->setInvoice($itemsContainer);
        $result = $this->openInvoiceNlModel->extractAdditionalParams($payment);
        $this->assertTrue(is_array($result));
        $this->assertTrue(0 < count($result));
        $this->assertArrayHasKey('ITEMID1', $result);
        $this->assertEquals(1, $result['ITEMID1']);
        $this->assertArrayHasKey('ITEMNAME1', $result);
        $this->assertEquals('Item', $result['ITEMNAME1']);
        $this->assertArrayHasKey('ITEMPRICE1', $result);
        $this->assertEquals(1999, $result['ITEMPRICE1']);
        $this->assertArrayHasKey('ITEMVATCODE1', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE1']);
        $this->assertArrayHasKey('TAXINCLUDED1', $result);
        $this->assertEquals(1, $result['TAXINCLUDED1']);
    }

    public function testExtractAdditionalParamsWithoutShippingButWithTwoNormalInvoiceItems()
    {
        $itemsContainer = Mage::getModel('sales/order_invoice');
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(1);
        $orderItem->setQtyOrdered(2);
        $item = Mage::getModel('sales/order_invoice_item');
        $item->setOrderItemId(1);
        $item->setOrderItem($orderItem);
        $item->setName('Item');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(19);
        $itemsContainer->addItem($item);
        $item = Mage::getModel('sales/order_invoice_item');
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(1);
        $orderItem->setQtyOrdered(2);
        $item->setOrderItemId(2);
        $item->setOrderItem($orderItem);
        $item->setName('Item');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(19);
        $itemsContainer->addItem($item);
        $payment = Mage::getModel('sales/order_payment')->setMethod('ops_openInvoiceNl');
        $payment->setInvoice($itemsContainer);
        $result = $this->openInvoiceNlModel->extractAdditionalParams($payment);
        $this->assertTrue(is_array($result));
        $this->assertTrue(0 < count($result));
        $this->assertArrayHasKey('ITEMID1', $result);
        $this->assertEquals(1, $result['ITEMID1']);
        $this->assertArrayHasKey('ITEMNAME1', $result);
        $this->assertEquals('Item', $result['ITEMNAME1']);
        $this->assertArrayHasKey('ITEMPRICE1', $result);
        $this->assertEquals(1999, $result['ITEMPRICE1']);
        $this->assertArrayHasKey('ITEMVATCODE1', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE1']);
        $this->assertArrayHasKey('TAXINCLUDED1', $result);
        $this->assertEquals(1, $result['TAXINCLUDED1']);

        $this->assertArrayHasKey('ITEMID2', $result);
        $this->assertEquals(1, $result['ITEMID2']);
        $this->assertArrayHasKey('ITEMNAME2', $result);
        $this->assertEquals('Item', $result['ITEMNAME2']);
        $this->assertArrayHasKey('ITEMPRICE2', $result);
        $this->assertEquals(1999, $result['ITEMPRICE2']);
        $this->assertArrayHasKey('ITEMVATCODE2', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE2']);
        $this->assertArrayHasKey('TAXINCLUDED2', $result);
        $this->assertEquals(1, $result['TAXINCLUDED2']);
    }

    public function testExtractAdditionalParamsWithShipping()
    {
        $itemsContainer = Mage::getModel('sales/order_invoice');
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(1);
        $orderItem->setQtyOrdered(2);
        $item = Mage::getModel('sales/order_invoice_item');
        $item->setOrderItemId(1);
        $item->setOrderItem($orderItem);
        $item->setName('Item');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(19);
        $itemsContainer->addItem($item);
        $itemsContainer->setBaseShippingInclTax(10.00);
        $order = Mage::getModel('sales/order');
        $order->setShippingDescription('foo');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_openInvoiceNl');
        $order->setPayment($payment);
        $itemsContainer->setOrder($order);
        $payment->setInvoice($itemsContainer);
        $result = $this->openInvoiceNlModel->extractAdditionalParams($payment);
        $this->assertTrue(is_array($result));
        $this->assertTrue(0 < count($result));
        $this->assertArrayHasKey('ITEMID1', $result);
        $this->assertEquals(1, $result['ITEMID1']);
        $this->assertArrayHasKey('ITEMNAME1', $result);
        $this->assertEquals('Item', $result['ITEMNAME1']);
        $this->assertArrayHasKey('ITEMPRICE1', $result);
        $this->assertEquals(1999, $result['ITEMPRICE1']);
        $this->assertArrayHasKey('ITEMVATCODE1', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE1']);
        $this->assertArrayHasKey('TAXINCLUDED1', $result);
        $this->assertEquals(1, $result['TAXINCLUDED1']);
        $this->assertArrayHasKey('ITEMID2', $result);
        $this->assertEquals('SHIPPING', $result['ITEMID2']);
        $this->assertArrayHasKey('ITEMNAME2', $result);
        $this->assertEquals(substr($order->getShippingDescription(), 0, 30), $result['ITEMNAME2']);
        $this->assertArrayHasKey('ITEMPRICE2', $result);
        $this->assertEquals(Mage::helper('ops/data')->getAmount(10.00), $result['ITEMPRICE2']);
        $this->assertArrayHasKey('ITEMQUANT2', $result);
        $this->assertEquals(1, $result['ITEMQUANT2']);
        $this->assertArrayHasKey('ITEMVATCODE2', $result);
        $this->assertEquals(floatval($payment->getMethodInstance()->getShippingTaxRate($order)).'%', $result['ITEMVATCODE2']);
        $this->assertArrayHasKey('TAXINCLUDED2', $result);
        $this->assertEquals(1, $result['TAXINCLUDED2']);
    }

    public function testExtractAdditionalParamsWithShippingAndDiscount()
    {
        $itemsContainer = Mage::getModel('sales/order_invoice');
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setId(1);
        $orderItem->setQtyOrdered(2);
        $item = Mage::getModel('sales/order_invoice_item');
        $item->setOrderItemId(1);
        $item->setOrderItem($orderItem);
        $item->setName('Item');
        $item->setBasePriceInclTax(19.99);
        $item->setQty(2);
        $item->setTaxPercent(19);
        $itemsContainer->addItem($item);
        $itemsContainer->setBaseShippingInclTax(10.00);
        $itemsContainer->setBaseDiscountAmount(-10.00);
        $order = Mage::getModel('sales/order');
        $order->setShippingDescription('foo');
        $order->setCouponRuleName('Foo');
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('ops_openInvoiceNl');
        $order->setPayment($payment);
        $itemsContainer->setOrder($order);
        $payment->setInvoice($itemsContainer);
        $result = $this->openInvoiceNlModel->extractAdditionalParams($payment);
        $this->assertTrue(is_array($result));
        $this->assertTrue(0 < count($result));
        $this->assertArrayHasKey('ITEMID1', $result);
        $this->assertEquals(1, $result['ITEMID1']);
        $this->assertArrayHasKey('ITEMNAME1', $result);
        $this->assertEquals('Item', $result['ITEMNAME1']);
        $this->assertArrayHasKey('ITEMPRICE1', $result);
        $this->assertEquals(1999, $result['ITEMPRICE1']);
        $this->assertArrayHasKey('ITEMVATCODE1', $result);
        $this->assertEquals('19%', $result['ITEMVATCODE1']);
        $this->assertArrayHasKey('TAXINCLUDED1', $result);
        $this->assertEquals(1, $result['TAXINCLUDED1']);

        $this->assertArrayHasKey('ITEMID2', $result);
        $this->assertEquals('DISCOUNT', $result['ITEMID2']);
        $this->assertArrayHasKey('ITEMNAME2', $result);
        $this->assertEquals('Foo', $result['ITEMNAME2']);
        $this->assertArrayHasKey('ITEMPRICE2', $result);
        $this->assertEquals(Mage::helper('ops/data')->getAmount(-10.00), $result['ITEMPRICE2']);
        $this->assertArrayHasKey('ITEMQUANT2', $result);
        $this->assertEquals(1, $result['ITEMQUANT2']);
        $this->assertArrayHasKey('ITEMVATCODE2', $result);
        $this->assertEquals(floatval($payment->getMethodInstance()->getShippingTaxRate($order)).'%', $result['ITEMVATCODE2']);
        $this->assertArrayHasKey('TAXINCLUDED2', $result);
        $this->assertEquals(1, $result['TAXINCLUDED2']);

        $this->assertArrayHasKey('ITEMID3', $result);
        $this->assertEquals('SHIPPING', $result['ITEMID3']);
        $this->assertArrayHasKey('ITEMNAME3', $result);
        $this->assertEquals(substr($order->getShippingDescription(), 0, 30), $result['ITEMNAME3']);
        $this->assertArrayHasKey('ITEMPRICE3', $result);
        $this->assertEquals(Mage::helper('ops/data')->getAmount(10.00), $result['ITEMPRICE3']);
        $this->assertArrayHasKey('ITEMQUANT3', $result);
        $this->assertEquals(1, $result['ITEMQUANT3']);
        $this->assertArrayHasKey('ITEMVATCODE3', $result);
        $this->assertEquals(floatval($payment->getMethodInstance()->getShippingTaxRate($order)).'%', $result['ITEMVATCODE3']);
        $this->assertArrayHasKey('TAXINCLUDED3', $result);
        $this->assertEquals(1, $result['TAXINCLUDED3']);
    }

    protected function mockSessions()
    {
        $sessionMock = $this->getModelMockBuilder('checkout/session')
                            ->disableOriginalConstructor()
                            ->setMethods(null)
                            ->getMock();
        $this->replaceByMock('singleton', 'checkout/session', $sessionMock);
        $sessionMock = $this->getModelMockBuilder('adminhtml/session_quote')
                            ->disableOriginalConstructor()
                            ->setMethods(null)
                            ->getMock();
        $this->replaceByMock('singleton', 'adminhtml/session_quote', $sessionMock);
        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->openInvoiceNlModel = null;
    }
} 