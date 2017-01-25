<?php

/**
 * Netresearch_OPS_AdminController
 *
 * @package   OPS
 * @copyright 2013 Netresearch
 * @author    Michael Lühr <michael.luehr@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Adminhtml_KwixocategoryController
    extends Mage_Adminhtml_Controller_Action
{

    protected $_block = 'ops/adminhtml_kwixocategory_edit';

    /**
     * @param bool $getRootInstead
     * @return bool
     */
    protected function _initCategory($getRootInstead = false)
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $category->load($rootId);
                    } else {
                        $this->_redirect(
                            '*/*/', array('_current' => true, 'id' => null)
                        );

                        return false;
                    }
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }

    public function treeAction()
    {
        $categoryId = (int)$this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($storeId) {
            if (!$categoryId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
                $this->getRequest()->setParam('id', $rootId);
            }
        }

        $category = $this->_initCategory(true);

        $block = $this->getLayout()->createBlock('ops/adminhtml_kwixocategory_categoryTree');
        $root = $block->getRoot();
        $this->getResponse()->setBody(
            Zend_Json::encode(
                array(
                    'data'       => $block->getTree(),
                    'parameters' => array(
                        'text'         => $block->buildNodeName($root),
                        'draggable'    => false,
                        'allowDrop'    => false,
                        'id'           => (int)$root->getId(),
                        'expanded'     => (int)$block->getIsWasExpanded(),
                        'store_id'     => (int)$block->getStore()->getId(),
                        'category_id'  => (int)$category->getId(),
                        'root_visible' => (int)$root->getIsVisible()
                    ))
            )
        );
    }


    public function categoriesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if (($categoryId = (int)$this->getRequest()->getPost('id'))) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock(
                    'ops/adminhtml_kwixocategory_categoryTree'
                )->getTreeJson($category)
            );
        }
    }

    public function indexAction()
    {
        $this->loadLayout();

        $selectedCategory = Mage::getSingleton('admin/session')->getLastEditedCategory(true);
        if ($selectedCategory) {
            $this->getRequest()->setParam('id', $selectedCategory);
        }
        $selectedCategory = (int)$this->getRequest()->getParam('id', 0);
        $this->_initCategory(true);

        if ($selectedCategory > 0) {
            $this->getLayout()->getBlock('tree')->setData(
                'selectedCategory', $selectedCategory
            );
        }

        $this->renderLayout();
    }

    public function editAction()
    {

        $params = array('_current' => true);
        $redirect = false;

        $storeId = (int)$this->getRequest()->getParam('store');
        $parentId = (int)$this->getRequest()->getParam('parent');
        $prevStoreId = Mage::getSingleton('admin/session')->getLastViewedStore(
            true
        );

        if ($prevStoreId != null && !$this->getRequest()->getQuery('isAjax')) {
            $params['store'] = $prevStoreId;
            $redirect = true;
        }

        $prevCategoryId = Mage::getSingleton('admin/session')
                              ->getLastEditedCategory(true);
        if ($prevCategoryId && !$this->getRequest()->getQuery('isAjax')) {
            $this->getRequest()->setParam('id', $prevCategoryId);
        }
        if ($redirect) {
            $this->_redirect('*/*/edit', $params);

            return;
        }

        $categoryId = (int)$this->getRequest()->getParam('id');
        if ($storeId && !$categoryId && !$parentId) {
            $store = Mage::app()->getStore($storeId);
            $prevCategoryId = (int)$store->getRootCategoryId();
            $this->getRequest()->setParam('id', $prevCategoryId);
        }

        if (!($category = $this->_initCategory(true))) {
            return;
        }

        $data = Mage::getSingleton('adminhtml/session')->getCategoryData(true);
        if (isset($data['general'])) {
            $category->addData($data['general']);
        }

        if ($this->getRequest()->getQuery('isAjax')) {
            Mage::getSingleton('admin/session')->setLastViewedStore(
                $this->getRequest()->getParam('store')
            );
            Mage::getSingleton('admin/session')->setLastEditedCategory(
                $category->getId()
            );
            $this->_initLayoutMessages('adminhtml/session');
            $this->getResponse()->setBody(
                $this->getLayout()->getMessagesBlock()->getGroupedHtml()
                . $this->getLayout()->createBlock(
                    'ops/adminhtml_kwixocategory_edit'
                )->setController('kwixocategory')->toHtml()
            );

            return;
        }
        $this->_redirect('*/*/index', $params);
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();
        try {
            Mage::helper('ops/kwixo')->saveKwixoconfigurationMapping($post);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect(
            '*/*/index/',
            array('_current' => true, "id" => $post['category_id'], "store" => $post['storeId'])
        );
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $storeId = $this->getRequest()->getParam('store', 0);
        try {
            Mage::getModel('ops/kwixo_category_mapping')
                ->loadByCategoryId($id)
                ->delete();
            $message = Mage::helper('ops/data')->__('Data succesfully deleted.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/index/', array("id" => $id, "store" => $storeId));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/categories');
    }

}