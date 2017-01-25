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
 * Method.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */

class Netresearch_OPS_Block_System_Config_Form_Field_Method extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'title', array(
            'label' => Mage::helper('ops')->__('Title'),
            'style' => 'width:80px',
            'class' => 'required-entry'
            )
        );
        $this->addColumn(
            'pm', array(
            'label' => 'PM',
            'style' => 'width:80px',
            'class' => 'required-entry'
            )
        );
        $this->addColumn(
            'brand', array(
            'label' => 'BRAND',
            'style' => 'width:80px',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('ops')->__('Add Method');
        parent::__construct();
    }
}