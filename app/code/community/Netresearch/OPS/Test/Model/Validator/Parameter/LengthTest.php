<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Model_Validator_Parameter_LengthTest extends EcomDev_PHPUnit_Test_Case
{

    protected $validator = null;

    public function setUp()
    {
        parent::setUp();
        $this->validator = Mage::getModel('ops/validator_parameter_length');
    }

    public function testValidationPassed()
    {
        $this->assertTrue($this->validator->isValid(null));
        $this->assertTrue($this->validator->isValid(new Varien_Object()));
        $this->assertTrue($this->validator->isValid(array()));
        $map = array('foo' => 5, 'bar' => 4, 'baz' => 3, 'borg' => 5);
        $this->validator->setFieldLengths($map);
        $data = array('foo' => '12345', 'bar' => '1234', 'baz' => '123', 'borg' => null);
        $this->assertTrue($this->validator->isValid($data));
    }

    public function testValidationFailed()
    {
        $map = array('foo' => 5, 'bar' => 4, 'baz' => 3);
        $this->validator->setFieldLengths($map);
        $data = array('foo' => '123456', 'bar' => '1234', 'baz' => '1238');
        $this->assertFalse($this->validator->isValid($data));
        $this->assertTrue(2 == count($this->validator->getMessages()));

        $this->validator = Mage::getModel('ops/validator_parameter_length');
        $map = array('foo' => 5, 'bar' => 4, 'baz' => 3);
        $this->validator->setFieldLengths($map);
        $data = array('foo' => '123456', 'bar' => '1234', 'baz' => '123');
        $this->assertFalse($this->validator->isValid($data));
        $this->assertTrue(1 == count($this->validator->getMessages()));
    }
} 