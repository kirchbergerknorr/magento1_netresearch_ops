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
 * @category  OPS
 * @package   Netresearch_OPS
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

/**
 * Netresearch_OPS_Test_Block_System_Config_EncodingTest
 *
 * @category OPS
 * @package  Netresearch_OPS
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Netresearch_OPS_Test_Model_System_Config_Backend_EncodingTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     */
    public function testToOptionArray()
    {
        /** @var Netresearch_Ops_Model_System_Config_Backend_PaymentLogo $optionModel */
        $optionModel = Mage::getModel('ops/system_config_backend_encoding');

        $result = $optionModel->toOptionArray();

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertArrayHasKey('label', $result[0]);

        $this->assertEquals('utf-8', $result[0]['value']);
        $this->assertEquals('UTF-8', $result[0]['label']);

        $this->assertEquals('other', $result[1]['value']);
        $this->assertEquals('Other', $result[1]['label']);
    }

}
