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
 * MethodsTest.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Model_System_Config_Backend_Flex_MethodsTest extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Can not save empty title or PM fields
     */
    public function testSaveWithEmptyException()
    {
        /** @var Netresearch_OPS_Model_System_Config_Backend_Flex_Methods $model */
        $model = Mage::getModel('ops/system_config_backend_flex_methods');
        $model->setValue($this->getEmpty());

        $model->save();
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage PM and Brand combination must be unique
     */
    public function testSaveWithDuplicateException()
    {
        $model = Mage::getModel('ops/system_config_backend_flex_methods');
        $model->setValue($this->getDuplicate());

        $model->save();

    }

    public function testSave()
    {
        $model = Mage::getModel('ops/system_config_backend_flex_methods');
        $model->setValue(array($this->getSimpleData()));
        $model->setScope('default')
            ->setScopeId(0)
            ->setPath('payment/ops_flex/methods');

        $model->save();
        Mage::getConfig()->cleanCache();
        $methods = unserialize(Mage::getStoreConfig('payment/ops_flex/methods'));

        $this->assertTrue(is_array($methods));
        $this->assertEquals(1, count($methods));
    }

    protected function getDuplicate()
    {
        return array(
            $this->getSimpleData(),
            $this->getSimpleData()
        );
    }

    protected function getEmpty()
    {
        return array(
            $this->getSimpleData(),
            array(
                'title' => '',
                'brand' => '',
                'pm'    => ''
            )
        );
    }

    protected function getSimpleData()
    {
        return array(
            'title' => 'foo',
            'brand' => 'bar',
            'pm'    => 'zzz'
        );
    }
}
