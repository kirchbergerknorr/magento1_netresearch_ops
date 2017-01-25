<?php
/**
 * @category   OPS
 * @package    Netresearch_OPS
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_OPS_Model_Mysql4_Alias
 * Netresearch_OPS_Model_Mysql4_Alias
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netresearch_OPS_Model_Mysql4_Alias 
    extends Mage_Core_Model_Mysql4_Abstract
{
   /**
    * Constructor
    * 
    * @see lib/Varien/Varien_Object#_construct()
    * @return Netresearch_OPS_Model_Mysql4_Alias
    */
    public function _construct()
    {
       $this->_init('ops/alias', 'id');
    }
}