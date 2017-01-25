<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2016 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * MethodTest.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Block_Form_Field_MethodTest extends EcomDev_PHPUnit_Test_Case
{
    public function testBlockConstruction()
    {
        /** @var Netresearch_OPS_Block_System_Config_Form_Field_Method $block */
        $block = Mage::app()->getLayout()->createBlock('ops/system_config_form_field_method');
        $this->assertTrue($block instanceof Netresearch_OPS_Block_System_Config_Form_Field_Method);

    }
}
