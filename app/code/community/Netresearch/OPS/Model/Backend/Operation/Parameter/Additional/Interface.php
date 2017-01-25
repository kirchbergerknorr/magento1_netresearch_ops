<?php
/**
* @author      Michael Lühr <michael.luehr@netresearch.de>
* @category    Netresearch
* @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
interface Netresearch_OPS_Model_Backend_Operation_Parameter_Additional_Interface
{
    public function extractAdditionalParams(Mage_Sales_Model_Order_Payment $payment);
}