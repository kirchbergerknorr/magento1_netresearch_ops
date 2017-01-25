<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch/OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Model_Payment_OpenInvoiceDeTest extends EcomDev_PHPUnit_Test_Case
{

    protected $model = null;

    public function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('ops/payment_openInvoiceDe');
    }

    /**
     * assure that openInvoiceNL can not capture partial, because invoice is always created on feedback in this case
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
        $store->setConfig('payment/ops_openInvoiceDe/allow_discounted_carts', 0);

        $this->assertFalse($this->model->isAvailable($quote));
    }

    public function testIsAvailableNoGender()
    {

        $quote = Mage::getModel('sales/quote');
        $quote->setSubtotal(10);
        $quote->setSubtotalWithDiscount(10);
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig('payment/ops_openInvoiceDe/allow_discounted_carts', 1);

        $this->assertFalse($this->model->isAvailable($quote));
    }
} 