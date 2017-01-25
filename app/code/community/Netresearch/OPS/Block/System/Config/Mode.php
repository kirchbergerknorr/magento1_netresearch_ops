<?php
/**
 * Mode.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */

class Netresearch_OPS_Block_System_Config_Mode extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html =  parent::_getElementHtml($element);

        $javascript = "
        <script type=\"text/javascript\">
            element = $('".$element->getHtmlId()."');
            Event.observe(element, 'change', function(){
                if(element.selectedOptions[0].value != '".$element->getValue()."'){
                    $('ops_mode_comment').style.display = 'block';
                } else {
                    $('ops_mode_comment').style.display = 'none';
                }
            });
        </script>";

        return $html.$javascript;
    }


}
