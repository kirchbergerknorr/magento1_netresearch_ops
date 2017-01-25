<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Adminhtml_OpsstatusController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/invoice');
    }

    /**
     * performs the status update call to Ingenico ePayments
     */
    public function updateAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (0 < $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            Mage::getModel('ops/status_update')->updateStatusFor($order);
        }
        $this->_redirect('adminhtml/sales_order/view/', array("order_id" => $orderId));
    }

} 