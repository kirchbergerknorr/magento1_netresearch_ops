<?php
/**
 * Netresearch OPS
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
 * PHP version 5
 *
 * @category  Netresearch
 * @package   Netresearch_OPS
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

/**
 * Netresearch_OPS_Test_Block_Checkout_DeviceFingerprintingTest
 *
 * @category Netresearch
 * @package  Netresearch_OPS
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Netresearch_OPS_Test_Block_Checkout_DeviceFingerprintingTest
    extends EcomDev_PHPUnit_Test_Case
{
    protected function setUp()
    {
        $sessionMock = $this->getModelMock('core/session', array('init'));
        $this->replaceByMock('singleton', 'core/session', $sessionMock);
    }

    /**
     * @test
     * @loadFixture
     */
    public function fingerPrintingEnabled()
    {
        /** @var Netresearch_OPS_Block_Checkout_DeviceFingerprinting $block */
        $block = Mage::app()->getLayout()->createBlock('ops/checkout_deviceFingerprinting');

        $html = $block->toHtml();
        $this->assertNotEmpty($html);

        $url = $block->getConsentUrl();
        $this->assertContains('ops/device/', $url);
    }

    /**
     * @test
     * @loadFixture
     */
    public function fingerPrintingDisabled()
    {
        /** @var Netresearch_OPS_Block_Checkout_DeviceFingerprinting $block */
        $block = Mage::app()->getLayout()->createBlock('ops/checkout_deviceFingerprinting');
        $html = $block->toHtml();
        $this->assertEmpty($html);

        $url = $block->getConsentUrl();
        $this->assertContains('ops/device/', $url);
    }
}
