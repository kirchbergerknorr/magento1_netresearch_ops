<?php

/**
 * @author      Paul Siedler <paul.siedler@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netresearch_OPS_Model_Backend_Operation_Refund_Additional_OpenInvoiceNl
    extends Netresearch_OPS_Model_Backend_Operation_Parameter_Additional_OpenInvoiceNlAbstract
{
    /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo  */
    protected $creditmemo = null;
    protected $amount = 0;
    protected $refundHelper = null;

    /**
     * @param Mage_Sales_Model_Order_Payment $itemContainer
     * @return array
     */
    public function extractAdditionalParams(Mage_Sales_Model_Order_Payment $payment = null)
    {
        $invoice = $payment->getInvoice();

        if ($invoice == null) {
            // if invoice is not set we load id hard from the request params
            $invoice = $this->getRefundHelper()->getInvoiceFromCreditMemoRequest();
        }
        $this->creditmemo = $payment->getCreditmemo();

        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            $this->extractFromCreditMemoItems($invoice);
            // We dont extract from discount data for the moment, because partial refunds are a problem
            $this->extractFromInvoicedShippingMethod($invoice);
            $this->extractFromAdjustments($invoice);
            // Overwrite amount to fix Magentos rounding problems (eg +1ct)
            $this->_additionalParams['AMOUNT'] = $this->amount;
        }

        return $this->_additionalParams;
    }

    /**
     * extracts all data from the invoice according to the credit memo items
     *
     */
    protected function extractFromCreditMemoItems()
    {
        /** @var Mage_Sales_Model_Order_Creditmemo_Item $item */
        foreach ($this->creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItemId()
                && $item->getOrderItem()->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                || $item->getOrderItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
            ) {
                continue;
            }
            $this->_additionalParams['ITEMID' . $this->_itemIdx] = substr($item->getOrderItemId(), 0, 15);
            $this->_additionalParams['ITEMNAME' . $this->_itemIdx] = substr($item->getName(), 0, 30);
            $this->_additionalParams['ITEMPRICE' . $this->_itemIdx] = $this->getOpsDataHelper()->getAmount(
                $item->getBasePriceInclTax()
            );
            $this->amount += $this->getOpsDataHelper()
                    ->getAmount($item->getBasePriceInclTax()) * $item->getQty();
            $this->_additionalParams['ITEMQUANT' . $this->_itemIdx] = $item->getQty();
            $this->_additionalParams['ITEMVATCODE' . $this->_itemIdx]
                = str_replace(',', '.', (string)(float)$item->getOrderItem()->getTaxPercent()) . '%';
            $this->_additionalParams['TAXINCLUDED' . $this->_itemIdx] = 1;
            ++$this->_itemIdx;
        }

    }


    protected function extractFromInvoicedShippingMethod(Mage_Sales_Model_Order_Invoice $invoice)
    {
        if ($this->creditmemo->getBaseShippingInclTax() > 0) {
            $this->_additionalParams['ITEMID' . $this->_itemIdx]    = 'SHIPPING';
            $this->_additionalParams['ITEMNAME' . $this->_itemIdx]  =
                substr($invoice->getOrder()->getShippingDescription(), 0, 30);
            $this->_additionalParams['ITEMPRICE' . $this->_itemIdx] = $this->getOpsDataHelper()
                ->getAmount($this->creditmemo->getBaseShippingInclTax());
            $this->amount += $this->getOpsDataHelper()->getAmount($this->creditmemo->getBaseShippingInclTax());
            $this->_additionalParams['ITEMQUANT' . $this->_itemIdx]   = 1;
            $this->_additionalParams['ITEMVATCODE' . $this->_itemIdx] = $this->getShippingTaxRate($invoice) . '%';
            $this->_additionalParams['TAXINCLUDED' . $this->_itemIdx] = 1;
            ++$this->_itemIdx;
        }

    }

    /**
     * extracts all data from the adjustment fee/refund
     *
     * @param $invoice
     */
    protected function extractFromAdjustments(Mage_Sales_Model_Order_Invoice $invoice)
    {

        if ($this->creditmemo->getBaseAdjustmentPositive() > 0) {
            $this->_additionalParams['ITEMID' . $this->_itemIdx]    = 'ADJUSTREFUND';
            $this->_additionalParams['ITEMNAME' . $this->_itemIdx]  = 'Adjustment Refund';
            $this->_additionalParams['ITEMPRICE' . $this->_itemIdx] = $this->getOpsDataHelper()
                ->getAmount($this->creditmemo->getBaseAdjustmentPositive());
            $this->amount += $this->getOpsDataHelper()->getAmount($this->creditmemo->getBaseAdjustmentPositive());
            $this->_additionalParams['ITEMQUANT' . $this->_itemIdx]   = 1;
            $this->_additionalParams['ITEMVATCODE' . $this->_itemIdx] = $this->getShippingTaxRate($invoice) . '%';
            $this->_additionalParams['TAXINCLUDED' . $this->_itemIdx] = 1;
            ++$this->_itemIdx;

        }
        if ($this->creditmemo->getBaseAdjustmentNegative() > 0) {
            $this->_additionalParams['ITEMID' . $this->_itemIdx]    = 'ADJUSTFEE';
            $this->_additionalParams['ITEMNAME' . $this->_itemIdx]  = 'Adjustment Fee';
            $this->_additionalParams['ITEMPRICE' . $this->_itemIdx] = $this->getOpsDataHelper()
                ->getAmount(-$this->creditmemo->getBaseAdjustmentNegative());
            $this->amount += $this->getOpsDataHelper()->getAmount(-$this->creditmemo->getBaseAdjustmentNegative());
            $this->_additionalParams['ITEMQUANT' . $this->_itemIdx]   = 1;
            $this->_additionalParams['ITEMVATCODE' . $this->_itemIdx] = $this->getShippingTaxRate($invoice) . '%';
            $this->_additionalParams['TAXINCLUDED' . $this->_itemIdx] = 1;
            ++$this->_itemIdx;
        }
    }

    /**
     * gets the refund helper
     *
     * @return Netresearch_OPS_Helper_Order_Refund|null
     */
    protected function getRefundHelper()
    {
        if (null === $this->refundHelper) {
            $this->refundHelper = Mage::helper('ops/order_refund');
        }

        return $this->refundHelper;
    }
} 