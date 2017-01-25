<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of State
 *
 * @author sebastian
 */
class Netresearch_OPS_Block_Adminhtml_Customer_Renderer_State extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        return Mage::helper('ops')->__($value);
    }
}
