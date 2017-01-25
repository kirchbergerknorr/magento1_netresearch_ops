<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Helper_Validation_ResultTest extends EcomDev_PHPUnit_Test_Case
{

    protected $validationResultHelper = null;

    public function setUp()
    {
        parent::setUp();
        Mage::unregister('_helper/ops/validation_result');
        $this->validationResultHelper = Mage::helper('ops/validation_result');
    }

    public function testBaseErroneousFields()
    {
        $quote    = Mage::getModel('sales/quote');
        $messages = array('foo', 'bar');
        $result   = $this->validationResultHelper->getValidationFailedResult($messages, $quote);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('goto_section', $result);
        $this->assertArrayHasKey('opsError', $result);
        $this->assertArrayHasKey('fields', $result);
    }

    public function testGetValidationFailedResultWithFieldMapping()
    {
        $quote      = Mage::getModel('sales/quote');
        $configMock = $this->getModelMock('ops/config', array('getFrontendFieldMapping'));
        $configMock->expects($this->once())
            ->method('getFrontendFieldMapping')
            ->will($this->returnValue(array('foo' => 'bar')));
        $this->validationResultHelper->setConfig($configMock);
        $this->validationResultHelper->setFormBlock(Mage::app()->getLayout()->createBlock('ops/form'));
        $messages = array('foo' => 'bar');
        $result   = $this->validationResultHelper->getValidationFailedResult($messages, $quote);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('bar', $result['fields']);
    }

    /**
     * @loadFixture quotes
     */
    public function testGetValidationFailedResultWithExistingAddress()
    {
        $quote      = Mage::getModel('sales/quote')->load(1);
        $checkoutStepHelperMock = $this->getHelperMock('ops/validation_checkout_step', array('getStep'));
        $checkoutStepHelperMock->expects($this->exactly(2))
            ->method('getStep')
            ->will($this->onConsecutiveCalls('billing', 'shipping'));

        $configMock = $this->getModelMock('ops/config', array('getFrontendFieldMapping'));
        $configMock->expects($this->exactly(2))
            ->method('getFrontendFieldMapping')
            ->will($this->returnValue(array('foo' => 'bar')));
        $this->validationResultHelper->setConfig($configMock);

        $messages = array('foo' => 'bar');
        $this->validationResultHelper->setCheckoutStepHelper($checkoutStepHelperMock);
        $this->validationResultHelper->setFormBlock(Mage::app()->getLayout()->createBlock('ops/form'));
        $result   = $this->validationResultHelper->getValidationFailedResult($messages, $quote);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('billing-address-select', $result['fields']);

        $result   = $this->validationResultHelper->getValidationFailedResult($messages, $quote);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('shipping-address-select', $result['fields']);
    }

    public function testCleanResult()
    {
        $quote      = Mage::getModel('sales/quote');
        $messages = array('foo' => 'bar');
        $prevResult = array('update_section' => 'foo');

        $configMock = $this->getModelMock('ops/config', array('getFrontendFieldMapping'));
        $configMock->expects($this->once())
            ->method('getFrontendFieldMapping')
            ->will($this->returnValue(array('foo' => 'bar')));
        $this->validationResultHelper->setConfig($configMock);
        $this->validationResultHelper->setFormBlock(Mage::app()->getLayout()->createBlock('ops/form'));

        $this->validationResultHelper->setResult($prevResult);
        $result = $this->validationResultHelper->getValidationFailedResult($messages, $quote);
        $this->assertArrayNotHasKey('update_section', $result);
    }

} 