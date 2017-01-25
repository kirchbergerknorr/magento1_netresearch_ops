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
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * DeviceControllerTest.php
 *
 * @category ${CATEGORY}
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Controller_DeviceControllerTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    public function testToggleConsentAction()
    {

        $params = array('consent' => true, '_store' => 1);
        $this->dispatch('ops/device/toggleConsent', $params);
        $this->assertResponseBodyJson();
        $this->assertResponseBodyJsonMatch(array('consent' => false));

        Mage::app()->getStore(1)->setConfig(Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'device_fingerprinting', 1);
        $this->dispatch('ops/device/toggleConsent', $params);
        $this->assertResponseBodyJson();
        $this->assertResponseBodyJsonMatch(array('consent' => true));
    }

    public function testConsentAction()
    {
        $this->dispatch('ops/device/consent');
        $this->assertResponseBodyJson();
        $this->assertResponseBodyJsonMatch(array('consent' => false));

        $params = array('consent' => true, '_store' => 1);
        Mage::app()->getStore(1)->setConfig(Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'device_fingerprinting', 1);
        $this->dispatch('ops/device/toggleConsent', $params);
        $this->assertResponseBodyJson();
        $this->assertResponseBodyJsonMatch(array('consent' => true));

        $this->dispatch('ops/device/consent');
        $this->assertResponseBodyJson();
        $this->assertResponseBodyJsonMatch(array('consent' => true));
    }

}
