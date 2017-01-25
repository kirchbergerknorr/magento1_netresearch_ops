<?php
/**
 * Created by JetBrains PhpStorm.
 * User: michael
 * Date: 23.07.13
 * Time: 09:04
 * To change this template use File | Settings | File Templates.
 */

class Netresearch_OPS_Block_System_Config_Kwixoconfiguration
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_template = 'ops/system/config/kwixoconfiglinks.phtml';

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $fieldset
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $fieldset)
    {
        $this->addData(
            array(
                 'fieldset_label' => $fieldset->getLegend(),
            )
        );

        return $this->toHtml();
    }

}