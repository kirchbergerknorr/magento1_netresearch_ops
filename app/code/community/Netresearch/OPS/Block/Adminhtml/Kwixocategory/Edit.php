<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netresearch_OPS_Block_Adminhtml_Kwixocategory_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_kwixocategory';
        $this->_blockGroup = 'ops';
        $this->_mode = 'edit';
        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('save', 'url', $this->getUrl('*/*/save'));

    }

    public function getHeaderText()
    {
        $categoryId = (int) $this->getRequest()->getParam('id');

        if ($categoryId <= 0) {
            return Mage::helper('ops/data')->__('Categories configuration');
        }
        $category = Mage::getModel('catalog/category')->load($categoryId);
        return Mage::helper('ops/data')->__("Categorie's %s configuration", $category->getName());
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
    }
}