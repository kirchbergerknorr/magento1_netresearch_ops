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
 * InterSolveTest.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Block_Form_InterSolveTest extends EcomDev_PHPUnit_Test_Case
{
    public function testGetInterSolveBrands()
    {
        Mage::getConfig()->saveConfig(
            'payment/ops_interSolve/brands', serialize($this->getBrandArray())
        );
        Mage::getConfig()->cleanCache();

        $block = Mage::app()->getLayout()->createBlock('ops/form_interSolve');
        $this->assertEquals($this->getBrandArray(), $block->getInterSolveBrands());
    }

    private function getBrandArray()
    {
        return array(
            array('brand' => 'foo', 'title' => 'bar')
        );
    }
}
