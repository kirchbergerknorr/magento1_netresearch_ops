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
 * Netresearch_OPS_Block_Alias_List
 *
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Block_Alias_List
    extends Mage_Core_Block_Template
{
    public function getAliases()
    {
        $aliases = array();
        $customer = Mage::helper('customer')->getCustomer();
        if (0 < $customer->getId()) {
            $aliasesCollection = Mage::helper('ops/alias')->getAliasesForCustomer($customer->getId());
            foreach ($aliasesCollection as $alias) {
                $aliases[] = $alias;
            }
        }
        return $aliases;
    }

    /**
     * get human readable name of payment method
     *
     * @param string $methodCode Code of payment method
     * @return string Name of payment method
     */
    public function getMethodName($methodCode)
    {
        $title = '';
        $instance = Mage::helper('payment')->getMethodInstance($methodCode);
        if ($instance) {
            $title = $instance->getTitle();
        }

        return $title;
    }

    /**
     * retrieves the url for deletion the alias
     *
     * @param $aliasId - the id of the alias
     *
     * @return string - the url for deleting the alias with the given id
     */
    public function getAliasDeleteUrl($aliasId)
    {
        return Mage::getUrl(
            'ops/customer/deleteAlias/',
            array(
                 'id'       => $aliasId,
                 '_secure'  => Mage::app()->getFrontController()->getRequest()->isSecure()
            )
        );
    }
}
