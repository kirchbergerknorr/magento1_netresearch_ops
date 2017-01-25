<?php

/**
 * Netresearch_OPS_Helper_Kwixo
 *
 * @package
 * @copyright 2013 Netresearch
 * @author    Michael Lühr <michael.luehr@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Kwixo extends Mage_Core_Helper_Abstract
{

    protected $helper = null;

    protected function getHelper()
    {
        if (null === $this->helper) {
            $this->helper = Mage::helper('ops/data');
        }

        return $this->helper;
    }

    /**
     * validates the kwixoConfiguration data
     *
     * @param array $postData the data to validate
     *
     * @throws Mage_Core_Exception on errors
     *
     */
    public function validateKwixoconfigurationMapping(array $postData)
    {
        $this->validateKwixoConfigurationData($postData);
        $this->validateKwixoMappingExist($postData);
        $this->validateCategoryExist($postData);
    }

    /**
     * saves the KwixoConfigurationMapping
     *
     * @param array $postData
     */
    public function saveKwixoconfigurationMapping(array $postData)
    {
        $this->validateKwixoconfigurationMapping($postData);
        $kwixoCatMapModel = Mage::getModel(
            'ops/kwixo_category_mapping'
        )->load($postData['id']);
        $kwixoCatMapModel->setCategoryId($postData['category_id']);
        $kwixoCatMapModel->setKwixoCategoryId(
            $postData['kwixoCategory_id']
        );
        $kwixoCatMapModel->save();
        if (array_key_exists('applysubcat', $postData)) {
            $category = Mage::getModel('catalog/category')->load(
                $postData['category_id']
            );
            $subcategories = $category->getAllChildren(true);
            foreach ($subcategories as $subcategory) {
                $kwixoCatMapModel = Mage::getModel('ops/kwixo_category_mapping')->loadByCategoryId($subcategory);
                $kwixoCatMapModel->setCategoryId($subcategory);
                $kwixoCatMapModel->setKwixoCategoryId($postData['kwixoCategory_id']);
                $kwixoCatMapModel->save();
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('ops/data')->__(
                'Successfully added Kwixo category mapping'
            )
        );
    }


    /**
     * validates if the structure of a given array does match the expected kwixo
     * setting configuration
     *
     * @param array $postData - the array to inspect
     *
     * @throws Mage_Core_Exception - if the structure does not match
     */
    protected function validateKwixoConfigurationData(array $postData)
    {
        $helper = $this->getHelper();
        $isValid = true;
        $message = '';
        if (0 === count($postData)) {
            $message = $helper->__('Invalid form data provided');
            $isValid = false;
        }

        if ($isValid && !array_key_exists('id', $postData)) {
            $message = $helper->__('Invalid form data provided');
            $isValid = false;
        }

        if ($isValid && 0 < strlen($postData['id'])
            && (!is_numeric($postData['id'])
                || $postData['id'] < 0)
        ) {
            $message = $helper->__('Invalid id provided');
            $isValid = false;
        }
        if (false === $isValid) {
            Mage::throwException($message);
        }
    }

    /**
     * validates if the given array contains the neccessary information for
     * a proper kwixo category setting
     *
     * @param array $postData - the array to inspect
     *
     * @throws Mage_Core_Exception - if the array does not contain the needed
     *                             information
     *
     */
    protected function validateKwixoMappingExist(array $postData)
    {
        $helper = $this->getHelper();
        $kwixoCategories = Mage::getModel('ops/source_kwixo_productCategories')
            ->getValidKwixoCategoryIds();
        if (!array_key_exists('kwixoCategory_id', $postData)
            || !in_array(
                $postData['kwixoCategory_id'], $kwixoCategories
            )
        ) {
            Mage::throwException(
                $helper->__('Invalid kwixo category provided')
            );
        }
    }

    /**
     * validates if the given array contains a proper category setting
     *
     * @param array $postData - the array to inspect
     *
     * @throws Mage_Core_Exception - if an invalid setting is given
     */
    protected function validateCategoryExist(array $postData)
    {
        $helper = $this->getHelper();
        $isValid = true;
        $message = '';
        if (!array_key_exists('category_id', $postData)) {
            $isValid = false;
            $message = $helper->__('Invalid category provided');

        }
        if ($isValid
            && (!is_numeric($postData['category_id'])
            || null === Mage::getModel('catalog/category')->load($postData['category_id'])->getId())
        ) {
            $isValid = false;
            $message = $helper->__('Invalid category provided');
        }
        if (false === $isValid) {
            Mage::throwException($message);
        }
    }
}