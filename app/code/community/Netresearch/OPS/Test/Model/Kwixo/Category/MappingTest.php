<?php

class Netresearch_OPS_Test_Model_Kwixo_Category_MappingTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @loadFixture category_mapping
     */
    public function testLoadByCategoryId()
    {
        $this->assertEquals(
            10,
            Mage::getModel('ops/kwixo_category_mapping')->loadByCategoryId(10)
                ->getKwixoCategoryId()
        );
        $this->assertEquals(
            1,
            Mage::getModel('ops/kwixo_category_mapping')->loadByCategoryId(11)
                ->getKwixoCategoryId()
        );
        $this->assertEquals(
            null,
            Mage::getModel('ops/kwixo_category_mapping')->loadByCategoryId(12)
                ->getKwixoCategoryId()
        );
    }

}