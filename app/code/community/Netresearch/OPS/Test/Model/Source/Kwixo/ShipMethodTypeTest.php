<?php

class Netresearch_OPS_Test_Model_Source_Kwixo_ShipMethodTypeTest extends EcomDev_PHPUnit_Test_Case
{

    public function testToOptionArray()
    {
        $model = Mage::getModel('ops/source_kwixo_shipMethodType');
        $options = $model->toOptionArray();
        $this->assertTrue(is_array($options));
        // check for the existence of the keys for order or quote id
        $this->assertEquals($options[0]['label'], '--Please select--');
        $this->assertEquals($options[1]['label'], 'Pick up at merchant');
        $this->assertEquals($options[2]['label'], 'Collection point (Kiala...)');
        $this->assertEquals($options[3]['label'], 'Collect at airport, train station or travel agency');
        $this->assertEquals($options[4]['label'], 'Transporter (La Poste, UPS...)');
        $this->assertEquals($options[5]['label'], 'Download');
        $this->assertEquals($options[0]['value'], '');
        $this->assertEquals($options[1]['value'], 1);
        $this->assertEquals($options[2]['value'], 2);
        $this->assertEquals($options[3]['value'], 3);
        $this->assertEquals($options[4]['value'], 4);
        $this->assertEquals($options[5]['value'], 5);
    }
}