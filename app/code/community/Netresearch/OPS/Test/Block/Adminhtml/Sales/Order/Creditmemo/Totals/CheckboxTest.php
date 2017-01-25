<?php
class Netresearch_OPS_Test_Block_Adminhtml_Sales_Order_Creditmemo_Totals_CheckboxTest extends EcomDev_PHPUnit_Test_Case
{

    public function testGetTemplate()
    {
        $block = Mage::app()->getLayout()->getBlockSingleton('ops/adminhtml_sales_order_creditmemo_totals_checkbox');
        $this->assertEquals('ops/sales/order/creditmemo/totals/checkbox.phtml', $block->getTemplate());
    }

}