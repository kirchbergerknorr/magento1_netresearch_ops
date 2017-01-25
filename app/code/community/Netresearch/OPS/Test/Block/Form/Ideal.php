<?php

class Netresearch_OPS_Test_Block_Form_Ideal extends EcomDev_PHPUnit_Test_Case
{
    public function testGetIssuers()
    {
        $issuers = Mage::getModel('ops/payment_iDeal')->getIDealIssuers();
        $block   = Mage::app()->getLayout()->createBlock('ops/form_Ideal');
        $this->assertEquals($issuers, $block->getIssuers());

    }
}
