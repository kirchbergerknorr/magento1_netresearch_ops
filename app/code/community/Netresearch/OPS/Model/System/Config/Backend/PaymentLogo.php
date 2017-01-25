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
 * @copyright   Copyright (c) 2016 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch
 *
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @author      Benjamin Heuer <benjamin.heuer@netresearch.de>
 */
class Netresearch_Ops_Model_System_Config_Backend_PaymentLogo
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'left', 'label' => Mage::helper('ops')->__('Left')),
            array('value' => 'right', 'label' => Mage::helper('ops')->__('Right')),
            array('value' => 'hidden', 'label' => Mage::helper('ops')->__('Non Visible')),
        );
    }
}
