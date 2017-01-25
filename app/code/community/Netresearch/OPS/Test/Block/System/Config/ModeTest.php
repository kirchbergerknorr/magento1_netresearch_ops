<?php
/**
 * ModeTest.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */

class Netresearch_OPS_Test_Block_System_Config_ModeTest extends EcomDev_PHPUnit_Test_Case
{

    public function testGetElementHtmlContainsScriptTag()
    {
        $element = new Varien_Data_Form_Element_Select();
        $element->setForm(new Varien_Object());
        $block = new Netresearch_OPS_Block_System_Config_Mode();
        $html = $block->render($element);
        $this->assertTrue(preg_match('/<script/', $html) > 0);
    }

}
