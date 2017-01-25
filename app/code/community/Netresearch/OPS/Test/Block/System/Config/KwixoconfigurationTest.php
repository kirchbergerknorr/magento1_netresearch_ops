<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @package     ${MODULENAME}
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Block_System_Config_KwixoconfigurationTest
    extends EcomDev_PHPUnit_Test_Case
{

    public function testRender()
    {
        $element = new Varien_Data_Form_Element_Text();
        $element->setLegend('I am legend');
        $block = new Netresearch_OPS_Block_System_Config_Kwixoconfiguration();
        $block->render($element);
        $this->assertEquals('I am legend', $block->getData('fieldset_label'));
    }

} 