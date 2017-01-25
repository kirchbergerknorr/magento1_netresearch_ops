<?php
class Netresearch_OPS_Block_Adminhtml_Kwixocategory_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                                          'id' => 'edit_form',
                                          'action' => $this->getUrl('*/*/save'),
                                          'method' => 'post',
                                          'enctype' => 'multipart/form-data'
            )
        );

        $categoryId = (int) $this->getRequest()->getParam('id');
        if ($categoryId <= 0) {
            return parent::_prepareForm();
        }
        $kwixoCategoryMapping = Mage::getModel('ops/kwixo_category_mapping')->loadByCategoryId($categoryId);
        $storeId = (int) $this->getRequest()->getParam('store');

        $fieldset = $form->addFieldset(
            'ops_form',
            array(
            'legend' => Mage::helper('ops/data')->__('Categories configuration')
            )
        );

        $fieldset->addField(
            'storeId', 'hidden', array(
              'required' => true,
              'name' => 'storeId',
              'value' => $storeId,
            )
        );

        $fieldset->addField(
            'id', 'hidden', array(
             'required' => false,
             'name' => 'id',
             'value' => $kwixoCategoryMapping->getId(),
            )
        );
        $fieldset->addField(
            'category_id', 'hidden', array(
             'required' => true,
             'name' => 'category_id',
             'value' => $categoryId,
            )
        );

        $fieldset->addField(
            'kwixoCategory_id', 'select', array(
              'label' => Mage::Helper('ops/data')->__('Kwixo category'),
              'class' => 'required-entry',
              'required' => true,
              'name' => 'kwixoCategory_id',
              'value' => $kwixoCategoryMapping->getKwixoCategoryId(),
              'values' => Mage::getModel('ops/source_kwixo_productCategories')->toOptionArray()
            )
        );

        $fieldset->addField(
            'applysubcat', 'checkbox', array(
            'label' => Mage::Helper('ops/data')->__('Apply to sub-categories'),
            'name' => 'applysubcat'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}