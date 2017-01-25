<?php
class Netresearch_OPS_Test_Model_Mysql4_AliasTest extends EcomDev_PHPUnit_Test_Case
{
    public function testType()
    {
       $this->assertInstanceOf(
           'Netresearch_OPS_Model_Mysql4_Alias',
           Mage::getModel('ops/mysql4_alias')
       );
    }
}
