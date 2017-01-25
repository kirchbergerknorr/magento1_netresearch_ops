<?php

/**
 * @author      Paul Siedler <paul.siedler@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Netresearch_OPS_Model_Backend_Operation_Parameter_Additional_OpenInvoiceNlAbstract
    implements Netresearch_OPS_Model_Backend_Operation_Parameter_Additional_Interface
{
    protected $_additionalParams = array();
    protected $_opsDataHelper = null;
    protected $_itemIdx = 1;


    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return array
     */
    public function extractAdditionalParams(Mage_Sales_Model_Order_Payment $payment)
    {
        $itemContainer = $payment->getInvoice();
        if ($itemContainer instanceof Mage_Sales_Model_Order_Invoice) {
            $requestHelper = Mage::helper('ops/payment_request');
            $this->_additionalParams = $requestHelper->extractOrderItemParameters($itemContainer);
        }

        return $this->_additionalParams;
    }

    /**
     * @deprecated Netresearch_OPS_Helper_Payment_Request should be used instead
     *
     * extracts all necessary data from the invoice items
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return Netresearch_OPS_Model_Backend_Operation_Parameter_Additional_Interface
     */
    protected function extractFromInvoiceItems(Mage_Sales_Model_Order_Invoice $invoice)
    {
        foreach ($invoice->getItemsCollection() as $item) {
            /** @var $item Mage_Sales_Model_Order_Invoice_Item */
            // filter out configurable products
            if (!$this->isDataItem($item)) {
                continue;
            }
            $this->_additionalParams['ITEMID' . $this->_itemIdx] = substr($item->getOrderItemId(), 0, 15);
            $this->_additionalParams['ITEMNAME' . $this->_itemIdx] = substr($item->getName(), 0, 30);
            $this->_additionalParams['ITEMPRICE' . $this->_itemIdx] = $this->getOpsDataHelper()->getAmount(
                $item->getBasePriceInclTax()
            );
            $this->_additionalParams['ITEMQUANT' . $this->_itemIdx] = $item->getQty();
            $this->_additionalParams['ITEMVATCODE' . $this->_itemIdx]
                = str_replace(',', '.', (string)(float)$item->getTaxPercent()) . '%';
            $this->_additionalParams['TAXINCLUDED' . $this->_itemIdx] = 1;
            ++$this->_itemIdx;
        }

        return $this;
    }


    /**
     * extract the necessary data from the shipping data of the invoice
     *
     * @deprecated Netresearch_OPS_Helper_Payment_Request should be used instead
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return $this
     */
    protected function extractFromInvoicedShippingMethod(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $amount = $invoice->getBaseShippingInclTax();
        if (0 < $amount) {
            $this->_additionalParams['ITEMID' . $this->_itemIdx] = 'SHIPPING';
            $this->_additionalParams['ITEMNAME' . $this->_itemIdx]
                = substr($invoice->getOrder()->getShippingDescription(), 0, 30);
            $this->_additionalParams['ITEMPRICE' . $this->_itemIdx] = $this->getOpsDataHelper()->getAmount($amount);
            $this->_additionalParams['ITEMQUANT' . $this->_itemIdx] = 1;
            $this->_additionalParams['ITEMVATCODE' . $this->_itemIdx] = $this->getShippingTaxRate($invoice) . '%';
            $this->_additionalParams['TAXINCLUDED' . $this->_itemIdx] = 1;
            ++$this->_itemIdx;
        }


        return $this;
    }

    /**
     * @deprecated Netresearch_OPS_Helper_Payment_Request should be used instead
     *
     * retrieves used shipping tax rate
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return float
     */
    protected function getShippingTaxRate(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $taxRate = 0.0;
        $order = $invoice->getOrder();

        $taxRate = (floatval(Mage::helper('ops/payment_request')->getShippingTaxRate($order)));

        return $taxRate;
    }


    /**
     * gets the ops data helper
     *
     * @return Netresearch_OPS_Helper_Data
     */
    protected function getOpsDataHelper()
    {
        if (null === $this->_opsDataHelper) {
            $this->_opsDataHelper = Mage::helper('ops/data');
        }

        return $this->_opsDataHelper;
    }


    /**
     * @deprecated Netresearch_OPS_Helper_Payment_Request should be used instead
     *
     * @param $invoice
     */
    protected function extractFromDiscountData($invoice)
    {
        $amount = $invoice->getBaseDiscountAmount();
        if (0 > $amount) {
            $couponRuleName = 'DISCOUNT';
            $order = $invoice->getOrder();
            if ($order->getCouponRuleName() && strlen(trim($order->getCouponRuleName())) > 0) {
                $couponRuleName = substr(trim($order->getCouponRuleName()), 0, 30);
            }
            $this->_additionalParams['ITEMID' . $this->_itemIdx] = 'DISCOUNT';
            $this->_additionalParams['ITEMNAME' . $this->_itemIdx] = $couponRuleName;
            $this->_additionalParams['ITEMPRICE' . $this->_itemIdx] = $this->getOpsDataHelper()->getAmount($amount);
            $this->_additionalParams['ITEMQUANT' . $this->_itemIdx] = 1;
            $this->_additionalParams['ITEMVATCODE' . $this->_itemIdx] = $this->getShippingTaxRate($invoice) . '%';
            $this->_additionalParams['TAXINCLUDED' . $this->_itemIdx] = 1;
            ++$this->_itemIdx;
        }
    }
}