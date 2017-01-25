<?php


class Netresearch_OPS_Test_Model_Source_OrderReferenceTest
    extends EcomDev_PHPUnit_Test_Case
{


    public function testToOptionArray()
    {
        $model = Mage::getModel('ops/source_orderReference');
        $options = $model->toOptionArray();
        $this->assertTrue(is_array($options));
        // check for the existence of the keys for order or quote id
        $this->assertEquals($options[0]['value'], Netresearch_OPS_Model_Payment_Abstract::REFERENCE_QUOTE_ID);
        $this->assertEquals($options[1]['value'], Netresearch_OPS_Model_Payment_Abstract::REFERENCE_ORDER_ID);
    }

}



