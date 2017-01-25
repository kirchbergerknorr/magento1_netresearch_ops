<?php
class Netresearch_OPS_Test_Model_Mysql4_Alias_CollectionTest extends EcomDev_PHPUnit_Test_Case
{
   
    public function testType()
    {
        $this->assertTypeOf('Netresearch_OPS_Model_Mysql4_Alias_Collection', Mage::getModel('ops/mysql4_alias')->getCollection());
    }
}
