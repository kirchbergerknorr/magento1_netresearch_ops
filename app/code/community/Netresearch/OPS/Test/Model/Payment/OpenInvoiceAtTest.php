<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch/OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Model_Payment_OpenInvoiceAtTest extends EcomDev_PHPUnit_Test_Case
{

    protected $model = null;

    public function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('ops/payment_openInvoiceAt');
    }

    /**
     * assure that openInvoiceAT can not capture partial, because invoice is always created on feedback in this case
     */
    public function testCanCapturePartial()
    {
        $this->assertFalse($this->model->canCapturePartial());
    }

    public function testIsAvailableNoQuoteGiven()
    {
        $quote = new Varien_Object();
        $this->assertFalse($this->model->isAvailable($quote));
    }

    public function testIsAvailableNoDiscountAllowed()
    {

        $quote = Mage::getModel('sales/quote');
        $quote->setSubtotal(5);
        $quote->setSubtotalWithDiscount(10);
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig('payment/ops_openInvoiceAt/allow_discounted_carts', 0);

        $this->assertFalse($this->model->isAvailable($quote));
    }

    public function testIsAvailableNoGender()
    {

        $quote = Mage::getModel('sales/quote');
        $quote->setSubtotal(10);
        $quote->setSubtotalWithDiscount(10);
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig('payment/ops_openInvoiceAt/allow_discounted_carts', 1);

        $this->assertFalse($this->model->isAvailable($quote));
    }

    public function testGetMethodDependendFormFields()
    {
        $customerHelper = $this->getHelperMock('customer/data', array('isLoggedIn'));
        $customerHelper->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'customer/data', $customerHelper);

        $order = Mage::getModel('sales/order');
        $order->setCustomerDob('01/10/1970')
            ->setCustomerGender(1);
        $billingAddress = Mage::getModel('sales/order_address');
        $billingAddress->setAddressType(Mage_Sales_Model_Order_Address::TYPE_BILLING)
            ->setStreet('Klarna-Straße 1/2/3');
        $order->setBillingAddress($billingAddress);
        $payment = Mage::getModel('sales/order_payment');
        $model = Mage::getModel('ops/payment_openInvoiceAt');
        $payment->setMethod($model->getCode());
        $model->setInfoInstance($payment);
        $payment->setMethodInstance($model);
        $order->setPayment($payment);
        $params = $model->getMethodDependendFormFields($order);

        $this->assertEquals(' ', $params['ECOM_BILLTO_POSTAL_STREET_NUMBER']);
        $this->assertEquals('Klarna-Straße 1/2/3', $params['OWNERADDRESS']);
    }
} 