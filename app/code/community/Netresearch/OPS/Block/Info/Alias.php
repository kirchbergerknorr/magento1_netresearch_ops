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
 * Netresearch_OPS_Block_Info_Alias
 * 
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Block_Info_Alias extends Netresearch_OPS_Block_Info_Redirect
{
    /**
     * Init ops payment information block
     *
     */
    protected function _construct()
    {
        parent::_construct();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->setTemplate('ops/info/cc.phtml');
    }
}

