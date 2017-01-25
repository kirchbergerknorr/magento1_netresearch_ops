<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Model_Validator_Parameter_Length implements Zend_Validate_Interface
{

    protected $messages = array();

    protected $fieldLengths = array();

    /**
     * @param array $fieldLengths - the fieldLengths which are assumed as valid
     */
    public function setFieldLengths(array $fieldLengths)
    {
        $this->fieldLengths = $fieldLengths;
    }

    /**
     * gets the current configuration of the field lengths
     *
     * @return array
     */
    public function getFieldLengths()
    {
        return $this->fieldLengths;
    }

    /**
     * checks if the given data matching the given string lengths
     *
     * @param mixed $dataToValidate
     *
     * @return bool
     */
    public function isValid($dataToValidate)
    {
        $validationResult = true;
        if (is_array($dataToValidate) && 0 < count($dataToValidate)) {
            foreach ($dataToValidate as $key => $value) {
                $maxLength = $this->getFieldLengthFor($key);
                if ($value === null) {
                    $value = '';
                }
                if (0 < $maxLength) {
                    if (
                        false == Zend_Validate::is(
                            utf8_encode($value),
                            'StringLength',
                            array('max' => $maxLength, 'encoding' => 'utf-8')
                        )
                    ) {
                        $this->messages[$key] = Mage::helper('ops/data')->__('Value exceeds %d characters', $maxLength);
                        $validationResult = false;
                    }
                }
            }
        }

        return $validationResult;
    }

    /**
     * gets the messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * gets the valid string length for a given value
     *
     * @param $paramName
     *
     * @return int -1 if not found, the corresponding value otherwise
     */
    protected function getFieldLengthFor($paramName)
    {
        $value = -1;
        if (array_key_exists($paramName, $this->fieldLengths)) {
            $value = $this->fieldLengths[$paramName];
        }

        return $value;
    }

} 