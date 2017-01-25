<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Model_Validator_Parameter_Factory extends Mage_Core_Model_Abstract
{

    const TYPE_REQUEST_PARAMS_VALIDATION = 'request_validation';

    /**
     * @var Netresearch_OPS_Model_Validator_Parameter_Validator
     */
    protected $validator = null;

    /**
     * @var Netresearch_OPS_Model_Config
     */
    protected $config = null;

    /**
     * sets the necessary dependencies for this class
     */
    public function _construct()
    {
        parent::_construct();
        $this->validator = Mage::getModel('ops/validator_parameter_validator');
        $this->config = Mage::getModel('ops/config');
    }

    /**
     * creates validator for given type
     *
     * @param $type - the requested type
     *
     * @return Netresearch_OPS_Model_Validator_Parameter_Validator
     */
    public function getValidatorFor($type)
    {
        if ($type == self::TYPE_REQUEST_PARAMS_VALIDATION) {
            $this->createRequestParamsValidator();
        }

        return $this->validator;
    }

    /**
     * configures the validator for validation of the request parameter
     *
     * @return $this
     */
    protected function createRequestParamsValidator()
    {
        $validator = Mage::getModel('ops/validator_parameter_length');
        $validator->setFieldLengths($this->config->getParameterLengths());
        $this->validator->addValidator($validator);

        return $this;
    }
} 