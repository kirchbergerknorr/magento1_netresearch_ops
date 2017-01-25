<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */




class Netresearch_OPS_Test_Helper_Validation_Checkout_StepTest extends EcomDev_PHPUnit_Test_Case
{

    protected $stepHelper = null;

    public function setUp()
    {
        parent::setUp();
        $this->stepHelper = Mage::helper('ops/validation_checkout_step');
    }

    public function testHelperReturnsNoStep()
    {
        $this->assertEquals('', $this->stepHelper->getStep(array()));
        $this->assertEquals('', $this->stepHelper->getStep(array('SOME_OTHER_FIELD')));
    }

    public function testHelperReturnsBillingStep()
    {
        $expectedStep = Netresearch_OPS_Helper_Validation_Checkout_Step::BILLING_STEP;
        $this->assertEquals($expectedStep, $this->stepHelper->getStep(array('OWNERADDRESS')));
        $this->assertEquals($expectedStep, $this->stepHelper->getStep(array('OWNERADDRESS', 'SOME_OTHER_FIELD')));
        $this->assertEquals($expectedStep, $this->stepHelper->getStep(array('ECOM_SHIPTO_POSTAL_STATE', 'SOME_OTHER_FIELD', 'CN')));
    }

    public function testHelperReturnsShippingStep()
    {
        $expectedStep = Netresearch_OPS_Helper_Validation_Checkout_Step::SHIPPING_STEP;
        $this->assertEquals($expectedStep, $this->stepHelper->getStep(array('ECOM_SHIPTO_POSTAL_STATE')));
        $this->assertEquals($expectedStep, $this->stepHelper->getStep(array('ECOM_SHIPTO_POSTAL_STATE', 'SOME_OTHER_FIELD')));
    }

} 