<?php
/**
 * Netresearch_OPS_AdminController
 * 
 * @package   OPS
 * @copyright 2012 Netresearch
 * @author    Thomas Birke <thomas.birke@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Adminhtml_AliasController extends Mage_Adminhtml_Controller_Action
{
    use Netresearch_OPS_Trait_AliasController;

    protected $_publicActions = array('accept', 'exception');

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }

    public function deleteAction()
    {
        $aliasId = $this->_request->getParam('id');
        $alias = Mage::getModel('ops/alias')->load($aliasId);
        if ($alias->getId()) {
            $alias->delete();
            $this->_getSession()->addSuccess(
                Mage::helper('ops')->__('Removed alias %s.', $alias->getAlias())
            );
            return $this->_redirectReferer();
        }
        $this->_getSession()->addError(
            Mage::helper('ops')->__('Could not remove alias %s.', $alias->getAlias())
        );
        return $this->_redirectReferer();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}
