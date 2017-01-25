<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG
 *              (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Adminhtml_KwixoshippingController
    extends Mage_Adminhtml_Controller_Action
{

    /**
     * displays the form
     */
    public function indexAction()
    {
        $this->loadLayout();

        $storeId = $this->getRequest()->getParam('store', 0);
        $this->getLayout()->getBlock('kwixoshipping')->setData(
            'store', $storeId
        );
        $this->getLayout()->getBlock('kwixoshipping')->setData(
            'postData',
            Mage::getModel('adminhtml/session')->getData('errorneousData')
        );
        Mage::getModel('adminhtml/session')->unsetData('errorneousData');
        $this->renderLayout();
    }

    /**
     * save submitted form data
     */
    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getCleanPostData();
            $validator = Mage::getModel('ops/validator_kwixo_shipping_setting');
            if (true === $validator->isValid($postData)) {
                $collection = Mage::getModel('ops/kwixo_shipping_setting')->getCollection()
                    ->addFieldToFilter('shipping_code', array('in' => array_keys($postData)));
                /** @var Netresearch_OPS_Model_Kwixo_Shipping_Setting $kwixoShippingModel */
                foreach ($collection->getItems() as $kwixoShippingModel) {
                    if (!array_key_exists($kwixoShippingModel->getShippingCode(), $postData)) {
                        continue;
                    }
                    $kwixoData = $postData[$kwixoShippingModel->getShippingCode()];
                    $kwixoShippingModel
                        ->setKwixoShippingType($kwixoData['kwixo_shipping_type'])
                        ->setKwixoShippingSpeed($kwixoData['kwixo_shipping_speed'])
                        ->setKwixoShippingDetails($kwixoData['kwixo_shipping_details']);
                }
                $collection->save();
            } else {
                $postData = array_merge_recursive($postData, $validator->getMessages());
                Mage::getModel('adminhtml/session')->setData('errorneousData', $postData);
            }

        }
        $this->_redirect('adminhtml/kwixoshipping/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shipment');
    }

    /**
     * removes any methods not in the actual systems carrier list from the post parameters
     *
     * @return array
     */
    protected function getCleanPostData()
    {
        $postData = $this->getRequest()->getPost();
        $methodCodes = array_keys(
            Mage::getSingleton('shipping/config')->getAllCarriers()
        );
        $invalidateShippingCodes = function ($key) use ($methodCodes) {
            return !in_array($key, $methodCodes);
        };
        $validKeys = array_filter(array_keys($postData), $invalidateShippingCodes);
        $cleanPostData = array_diff_key($postData, $validKeys);

        return $cleanPostData;
    }


}