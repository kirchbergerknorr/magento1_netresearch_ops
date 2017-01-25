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
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2012 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * OPS System Config Backend Design Brands
 *
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 */
class  Netresearch_OPS_Test_Model_System_Config_Backend_Intersolve_BrandsTest
    extends EcomDev_PHPUnit_Test_Case
{

    public function testSave()
    {
        $model = Mage::getModel('ops/system_config_backend_intersolve_brands');
        $invalidData = array(
                            array('brand' => '123', 'value' => '1234'),
                            array('brand' => '123', 'value' => '1234')
                        );
        $model->setValue($invalidData);
        $this->setExpectedException('Mage_Core_Exception', 'Brands must be unique');
        $model->save();
        $validData = array(
                            array('brand' => '123', 'value' => '1234'),
                            array('brand' => '1234', 'value' => '1234')
                        );
        $model->setValue($validData);
        $this->assertTrue(($model->save() instanceof Netresearch_Ops_Model_System_Config_Backend_Intersolve_Brands));
    }

}
