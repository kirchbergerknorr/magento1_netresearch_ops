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
 * Netresearch_OPS_CustomerController
 *
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_CustomerController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     *
     */
    public function aliasesAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        if ($block = $this->getLayout()->getBlock('ops_customer_aliases')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(Mage::helper('ops')->__('My payment information'));
        }
        $this->renderLayout();
    }

    public function deleteAliasAction()
    {
        $aliasId = $this->_request->getParam('id');
        $alias = Mage::getModel('ops/alias')->load($aliasId);
        $customerId = Mage::helper('customer')->getCustomer()->getId();
        if ($alias->getId() && $alias->getCustomerId() == $customerId) {
            $alias->delete();
            Mage::getSingleton('customer/session')->addSuccess(
                Mage::helper('ops')->__('Removed payment information %s.', $alias->getPseudoAccountOrCcNo())
            );
            return $this->_redirectReferer();
        }
        Mage::getSingleton('customer/session')->addError(
            Mage::helper('ops')->__('Could not remove payment information %s.', $alias->getPseudoAccountOrCcNo())
        );
        return $this->_redirectReferer();
    }
}
