<?php
class Netresearch_OPS_Test_Model_Source_DirectEbanking_BrandsTest extends EcomDev_PHPUnit_Test_Case
{

    public function testToOptionArray()
    {
        $model = Mage::getModel('ops/source_directEbanking_brands');
        $options = $model->toOptionArray();
        $this->assertTrue(is_array($options));
        $this->assertEquals($options[0]['value'], 'DirectEbanking');
        $this->assertEquals($options[1]['value'], 'DirectEbankingAT');
        $this->assertEquals($options[2]['value'], 'DirectEbankingBE');
        $this->assertEquals($options[3]['value'], 'DirectEbankingCH');
        $this->assertEquals($options[4]['value'], 'DirectEbankingDE');
        $this->assertEquals($options[5]['value'], 'DirectEbankingFR');
        $this->assertEquals($options[6]['value'], 'DirectEbankingGB');
    }

}