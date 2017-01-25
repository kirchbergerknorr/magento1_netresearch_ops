<?php

class Netresearch_OPS_Test_Helper_KwixoTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{

    public function testValidateKwixoconfigurationMappingContainsNoData()
    {

        $this->helperMustThrowException(array());
    }

    public function testValidateKwixoconfigurationMappingContainsInvalidId()
    {
        $this->helperMustThrowException(array('od' => 1));
    }

    public function testValidateKwixoconfigurationMappingContainsEmptyId()
    {
        $this->helperMustThrowException(array('id' => ''));
    }

    public function testValidateKwixoconfigurationMappingContainsNonNumericId()
    {
        $this->helperMustThrowException(array('id' => 'abc'));
    }

    public function testValidateKwixoconfigurationMappingContainsNegativeId()
    {
        $this->helperMustThrowException(array('id' => -1));
    }

    public function testValidateKwixoconfigurationMappingContainsNoKwixoCategory(
    )
    {
        $this->helperMustThrowException(array('id' => 1));
    }

    public function testValidateKwixoconfigurationMappingContainsInvalidKwixoCategory(
    )
    {
        $this->helperMustThrowException(
            array('id' => 1, 'kwixoCategory_id' => 666)
        );
    }

    public function testValidateKwixoconfigurationMappingContainsNoCategory()
    {
        $this->helperMustThrowException(
            array('id' => 1, 'kwixoCategory_id' => 1)
        );
    }

    public function testValidateKwixoconfigurationMappingContainsNonNumericCategory(
    )
    {
        $this->helperMustThrowException(
            array('id' => 1, 'kwixoCategory_id' => 1, 'category_id' => 'abc')
        );
    }

    public function testValidateKwixoconfigurationMappingContainsNegativeCategory(
    )
    {
        $this->helperMustThrowException(
            array('id' => 1, 'kwixoCategory_id' => 1, 'category_id' => -1)
        );
    }

    public function testValidateKwixoconfigurationMappingContainsNegativeCategory2(
    )
    {
        $helper = Mage::helper('ops/kwixo');
        $helper->validateKwixoconfigurationMapping(
            array('id' => 1, 'kwixoCategory_id' => 1, 'category_id' => 1)
        );
    }


    protected function helperMustThrowException($invalidData)
    {
        $helper = Mage::helper('ops/kwixo');
        $this->setExpectedException('Mage_Core_Exception');
        $helper->validateKwixoconfigurationMapping($invalidData);
    }

    /**
     * @loadFixture category_mapping
     */
    public function testSaveKwixoConfigurationMapping()
    {
        $helperMock = $this->getHelperMock(
            'ops/kwixo', array('validateKwixoconfigurationMapping')
        );
        $helperMock->saveKwixoconfigurationMapping(
            array('id' => 1, 'category_id' => 666, 'kwixoCategory_id' => 777)
        );
        $model = Mage::getModel('ops/kwixo_category_mapping')->load(1);
        $this->assertEquals(666, $model->getCategoryId());
        $this->assertEquals(777, $model->getKwixoCategoryId());
    }

    /**
     * @loadFixture category_mapping
     */
    public function testSaveKwixoConfigurationMappingForSubCategories()
    {
        $helperMock = $this->getHelperMock(
            'ops/kwixo', array('validateKwixoconfigurationMapping')
        );

        $categoryMock = $this->getModelMock('catalog/category', array('load', 'getAllChildren'));
        $categoryMock->expects($this->any())
            ->method('getAllChildren')
            ->will($this->returnValue(array(11)));
        $categoryMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($categoryMock));
        $this->replaceByMock('model', 'catalog/category', $categoryMock);

        $helperMock->saveKwixoconfigurationMapping(
            array('id'               => 1, 'category_id' => 666,
                  'kwixoCategory_id' => 777, 'applysubcat' => true)
        );

        $model = Mage::getModel('ops/kwixo_category_mapping')->load(1);
        $this->assertEquals(666, $model->getCategoryId());
        $this->assertEquals(777, $model->getKwixoCategoryId());
        $model = Mage::getModel('ops/kwixo_category_mapping')->load(2);
        $this->assertEquals(777, $model->getKwixoCategoryId());
    }
}