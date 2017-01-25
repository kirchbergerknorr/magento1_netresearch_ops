<?php
/**
 * Netresearch OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

class Netresearch_OPS_Block_Adminhtml_Kwixocategory_CategoryTree
    extends Mage_Adminhtml_Block_Catalog_Category_Tree
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ops/categoriestree.phtml');
        $this->setUseAjax(true);
        $this->_withProductCount = false;
    }

    public function getSwitchTreeUrl()
    {
        return $this->getUrl(
            "*/*/tree",
            array(
                '_current' => true,
                'store' => null,
                '_query' => false,
                'id' => null,
                'parent' => null
            )
        );
    }

    public function getNodesUrl()
    {
        return $this->getUrl('adminhtml/catalog_category/jsonTree');
    }

    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit', array('_current' => true, '_query' => false, 'id' => null, 'parent' => null));
    }

    protected function _getNodeJson($node, $level = 0)
    {
        $item = array();
        $item['text'] = $this->buildNodeName($node);
        $item['id'] = $node->getId();
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        $item['store'] = (int) $this->getStore()->getId();
        $item['path'] = $node->getData('path');
        $item['allowDrop'] = false;
        $item['allowDrag'] = false;
        if ((int) $node->getChildrenCount() > 0) {
            $item['children'] = array();
        }
        $isParent = $this->_isParentSelectedCategory($node);
        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level + 1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }
        return $item;
    }

    protected function _getProductTypeLabel($productTypeId)
    {
        $res = '';
        $types = Mage::getModel('ops/source_kwixo_productCategories')->toOptionArray();
        foreach ($types as $data) {
            if ($data['value'] == $productTypeId) {
                $res = $data['label'];
                break;
            }
        }
        return $res;
    }

    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());
        return $result;
    }

}