<?php

class Netresearch_OPS_Test_Model_Source_Kwixo_ProductCategoriesTest extends EcomDev_PHPUnit_Test_Case
{

    public function testToOptionArray()
    {
        $model = Mage::getModel('ops/source_kwixo_productCategories');
        $options = $model->toOptionArray();
        $this->assertTrue(is_array($options));
        // check for the existence of the keys for order or quote id
        $this->assertEquals($options[0]['label'], 'Food & gastronomy');
        $this->assertEquals($options[1]['label'], 'Car & Motorbike');
        $this->assertEquals($options[2]['label'], 'Culture & leisure');
        $this->assertEquals($options[3]['label'], 'Home & garden');
        $this->assertEquals($options[4]['label'], 'Appliances');
        $this->assertEquals($options[5]['label'], 'Auctions and bulk purchases');
        $this->assertEquals($options[6]['label'], 'Flowers & gifts');
        $this->assertEquals($options[7]['label'], 'Computer & software');
        $this->assertEquals($options[8]['label'], 'Health & beauty');
        $this->assertEquals($options[9]['label'], 'Services for individuals');
        $this->assertEquals($options[10]['label'], 'Services for professionals');
        $this->assertEquals($options[11]['label'], 'Sports');
        $this->assertEquals($options[12]['label'], 'Clothing & accessories');
        $this->assertEquals($options[13]['label'], 'Travel & tourism');
        $this->assertEquals($options[14]['label'], 'Hifi, photo & video');
        $this->assertEquals($options[15]['label'], 'Telephony & communication');
        $this->assertEquals($options[16]['label'], 'Jewelry & precious metals');
        $this->assertEquals($options[17]['label'], 'Baby articles and accessories');
        $this->assertEquals($options[18]['label'], 'Sound & light');
        $this->assertEquals($options[0]['value'], 1);
        $this->assertEquals($options[1]['value'], 2);
        $this->assertEquals($options[2]['value'], 3);
        $this->assertEquals($options[3]['value'], 4);
        $this->assertEquals($options[4]['value'], 5);
        $this->assertEquals($options[5]['value'], 6);
        $this->assertEquals($options[6]['value'], 7);
        $this->assertEquals($options[7]['value'], 8);
        $this->assertEquals($options[8]['value'], 9);
        $this->assertEquals($options[9]['value'], 10);
        $this->assertEquals($options[10]['value'], 11);
        $this->assertEquals($options[11]['value'], 12);
        $this->assertEquals($options[12]['value'], 13);
        $this->assertEquals($options[13]['value'], 14);
        $this->assertEquals($options[14]['value'], 15);
        $this->assertEquals($options[15]['value'], 16);
        $this->assertEquals($options[16]['value'], 17);
        $this->assertEquals($options[17]['value'], 18);
        $this->assertEquals($options[18]['value'], 19);
    }
}