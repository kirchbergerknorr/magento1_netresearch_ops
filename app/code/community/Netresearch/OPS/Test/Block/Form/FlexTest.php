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
 * FlexTest.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Block_Form_FlexTest extends EcomDev_PHPUnit_Test_Case
{
    public function testGetFlexMethods()
    {
        Mage::app()->setCurrentStore(0);
        Mage::getConfig()->saveConfig('payment/ops_flex/methods', serialize($this->getMethodArray()));

        /** @var Netresearch_OPS_Block_Form_Flex $block */
        $block = Mage::app()->getLayout()->createBlock('ops/form_flex');

        $block->setMethod(Mage::getModel('ops/payment_flex'));

        $this->assertTrue(is_array($block->getFlexMethods()));
        $this->assertEquals(1, count($block->getFlexMethods()));
    }

    private function getMethodArray()
    {
        return array(
            array(
                'title' => 'foo',
                'brand' => 'bar',
                'pm'    => 'zzz'
            )
        );
    }


    public function testGetDefaultOptionTitle()
    {
        Mage::app()->setCurrentStore(0);
        Mage::getConfig()->saveConfig('payment/ops_flex/default_title', 'flex');
        Mage::getConfig()->cleanCache();

        /** @var Netresearch_OPS_Block_Form_Flex $block */
        $block = Mage::app()->getLayout()->createBlock('ops/form_flex');

        $block->setMethod(Mage::getModel('ops/payment_flex'));

        $this->assertEquals('flex', $block->getDefaultOptionTitle());
    }

    public function testIsDefaultOptionActive()
    {
        Mage::app()->setCurrentStore(0);
        Mage::getConfig()->saveConfig('payment/ops_flex/default', true);
        Mage::getConfig()->cleanCache();

        /** @var Netresearch_OPS_Block_Form_Flex $block */
        $block = Mage::app()->getLayout()->createBlock('ops/form_flex');

        $block->setMethod(Mage::getModel('ops/payment_flex'));

        $this->assertTrue($block->isDefaultOptionActive());

    }
}
