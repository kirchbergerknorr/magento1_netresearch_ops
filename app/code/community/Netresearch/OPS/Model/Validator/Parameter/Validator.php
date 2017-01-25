<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Model_Validator_Parameter_Validator
    implements Netresearch_OPS_Model_Validator_CompositeInterface, Zend_Validate_Interface
{

    protected $validators = array();

    protected $messages = array();

    /**
     * adds a validator
     *
     * @param Zend_Validate_Interface $validator
     */
    public function addValidator(Zend_Validate_Interface $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * @param $dataToValidate
     *
     * @return bool - indicates whether the data are valid or not
     */
    public function isValid($dataToValidate)
    {
        $isValid = true;
        foreach ($this->validators as $validator) {
            /** @var Zend_Validate_Interface $validator  */
            $isValid = $isValid && $validator->isValid($dataToValidate);
            if (false === $validator->isValid($dataToValidate)) {
                $this->messages = array_merge($this->messages, $validator->getMessages());
            }
        }

        return $isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getValidators()
    {
        return $this->validators;
    }
} 