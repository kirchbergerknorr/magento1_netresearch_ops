<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Model_Validator_Parameter_ValidatorTest extends EcomDev_PHPUnit_Test_Case
{
    protected $validator = null;

    public function setUp()
    {
        parent::setUp();
        $this->validator = Mage::getModel('ops/validator_parameter_validator');
    }

    public function testIsValid()
    {
        $this->assertTrue($this->validator->isValid(null));
        $this->validator->addValidator(new Zend_Validate_Alnum());
        $this->assertFalse($this->validator->isValid(null));
        $this->assertTrue(0 < count($this->validator->getMessages()));
        $this->validator->addValidator(new Zend_Validate_EmailAddress());
    }

    public function testMultipleValidators()
    {
        $this->validator->addValidator(new Zend_Validate_Alnum());
        $this->validator->addValidator(new Zend_Validate_EmailAddress());
        $this->assertFalse($this->validator->isValid(null));
        $this->assertTrue(1 < count($this->validator->getMessages()));
    }
} 