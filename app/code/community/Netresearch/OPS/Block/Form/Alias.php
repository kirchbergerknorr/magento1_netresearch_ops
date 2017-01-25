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
 * Netresearch_OPS_Block_Form_Alias
 * 
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Block_Form_Alias extends Netresearch_OPS_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ops/form/alias.phtml');
    }

    /**
     * get available aliases for current customer
     * will return empty array if there is no current user
     *
     * @return array|Netresearch_OPS_Model_Mysql4_Alias_Collection
     */
    public function getAvailableAliases()
    {
        $customer = Mage::helper('customer')->getCustomer();
        if (0 < $customer->getId()) {
            $quote = Mage::helper('ops/payment')->getQuote();
            return Mage::helper('ops/payment')->getAliasesForCustomer($customer->getId(), $quote);
        }
        return array();
    }

    /**
     * @param $alias
     * @return string
     */
    protected function getHumanReadableAlias($alias)
    {
        $helper = Mage::helper('ops');
        $aliasString = $helper->__('Credit Card Type') . ' ' . $helper->__($alias->getBrand());
        $aliasString .= ' ' . $helper->__('AccountNo') . ' ' . $helper->__($alias->getPseudoAccountOrCCNo());
        $aliasString .= ' ' . $helper->__('Expiration Date') . ' ' . $alias->getExpirationDate();
        return $aliasString;
    }
}
