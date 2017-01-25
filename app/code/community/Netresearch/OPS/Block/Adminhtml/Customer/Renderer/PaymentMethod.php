<?php

/**
 * PaymentMethod.php
 *
 * @author Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Block_Adminhtml_Customer_Renderer_PaymentMethod
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $title = '';
        $methodCode = $row->getData($this->getColumn()->getIndex());
        $instance = Mage::helper('payment')->getMethodInstance($methodCode);
        if ($instance) {
            $title = $instance->getTitle();
        }

        return $title;
    }
}