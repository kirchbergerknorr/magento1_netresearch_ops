<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @package     ${MODULENAME}
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Model_Mysql4_Kwixo_Shipping_Setting_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('ops/kwixo_shipping_setting');
    }

} 