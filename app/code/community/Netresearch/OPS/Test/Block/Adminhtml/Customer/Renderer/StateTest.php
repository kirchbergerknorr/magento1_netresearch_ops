<?php

class Netresearch_OPS_Test_Block_Adminhtml_Customer_Renderer_StateTest
    extends EcomDev_PHPUnit_Test_Case
{

    public function testRender()
    {
        $column = new Varien_Object();
        $column->setIndex('state');
        $row = new Varien_Object();
        $row->setData(array('state' => Netresearch_OPS_Model_Alias_State::ACTIVE));
        $block = Mage::app()->getLayout()->getBlockSingleton(
            'ops/adminhtml_customer_renderer_state'
        );
        $block->setColumn($column);
        $this->assertEquals(
            Mage::helper('ops/data')->__(
                Netresearch_OPS_Model_Alias_State::ACTIVE
            ), $block->render($row)
        );
    }
}