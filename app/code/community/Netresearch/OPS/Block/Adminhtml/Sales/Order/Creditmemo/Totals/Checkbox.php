<?php
/**
 * @category   OPS
 * @package    Netresearch_OPS
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2013 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_OPS_Block_Adminhtml_Sales_Order_Creditmemo_Create_Adjustments_Checkbox
 *
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netresearch_OPS_Block_Adminhtml_Sales_Order_Creditmemo_Totals_Checkbox
    extends Mage_Core_Block_Template
{

    /**
     * Internal constructor, that is called from real constructor.
     *
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ops/sales/order/creditmemo/totals/checkbox.phtml');
    }

}
