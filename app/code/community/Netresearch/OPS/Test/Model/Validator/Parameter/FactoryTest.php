<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Model_Validator_Parameter_FactoryTest extends EcomDev_PHPUnit_Test_Case
{

    protected $validatorFactory = null;

    public function setUp()
    {
        parent::setUp();
        $this->validatorFactory = Mage::getModel('ops/validator_parameter_factory');
    }

    public function testGetValidatorFor()
    {
        $validator = $this->validatorFactory->getValidatorFor(null);
        $this->assertTrue($validator instanceof Netresearch_OPS_Model_Validator_Parameter_Validator);
        $this->assertEquals(0, count($validator->getValidators()));

        $validator = $this->validatorFactory->getValidatorFor(Netresearch_OPS_Model_Validator_Parameter_Factory::TYPE_REQUEST_PARAMS_VALIDATION);
        $this->assertTrue($validator instanceof Netresearch_OPS_Model_Validator_Parameter_Validator);
        $this->assertEquals(1, count($validator->getValidators()));
        $this->assertTrue(current($validator->getValidators()) instanceof Netresearch_OPS_Model_Validator_Parameter_Length);
        $this->assertTrue(0 < count(current($validator->getValidators())->getFieldLengths()));
    }

} 