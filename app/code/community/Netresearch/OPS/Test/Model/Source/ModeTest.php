<?php

/**
 * ModeTest.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */
class Netresearch_OPS_Test_Model_Source_ModeTest extends EcomDev_PHPUnit_Test_Case
{

    public function testToOptionArray()
    {
        $model = Mage::getModel('ops/source_mode');
        $options = $model->toOptionArray();
        $this->assertTrue(is_array($options));
        $this->assertEquals(Netresearch_OPS_Model_Source_Mode::TEST, $options[0]['value']);
        $this->assertEquals(Netresearch_OPS_Model_Source_Mode::PROD, $options[1]['value']);
        $this->assertEquals(Netresearch_OPS_Model_Source_Mode::CUSTOM, $options[2]['value']);
    }
}
