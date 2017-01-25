<?php
/**
 * Netresearch_OPS_Block_Form_OpsId
 * 
 * @package   OPS
 * @copyright 2012 Netresearch App Factory AG <http://www.netresearch.de>
 * @author    Thomas Birke <thomas.birke@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Block_Form_OpsId extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ops/form/opsId.phtml');
    }
}
