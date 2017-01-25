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
 * Netresearch
 *
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 */

class Netresearch_OPS_Block_System_Config_Form_Field_Brand
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'brand', array(
            'label' => Mage::helper('ops')->__('Brand'),
            'style' => 'width:120px',
            )
        );
        $this->addColumn(
            'value', array(
            'label' => Mage::helper('ops')->__('Title'),
            'style' => 'width:120px',
            )
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('ops')->__('Add Brand');
        parent::__construct();
    }
}
