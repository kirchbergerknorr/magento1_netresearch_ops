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
 * RecurringActive.php
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Block_System_Config_Form_Field_RecurringActive
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::_getElementHtml($element);

        $javascript
            = "
        <script type=\"text/javascript\">
            element = $('" . $element->getHtmlId() . "');
            Event.observe(element, 'change', function(){
                if(element.value == 1){
                    $('ops_recurring_cc_active_comment').style.display = 'block';
                } else {
                    $('ops_recurring_cc_active_comment').style.display = 'none';
                }
            });
        </script>";

        return $html . $javascript;
    }
}