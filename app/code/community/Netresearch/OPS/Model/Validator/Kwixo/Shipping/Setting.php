<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     ${MODULENAME}
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Model_Validator_Kwixo_Shipping_Setting
{

    protected $messages = array();

    public function isValid(array $data)
    {
        $result = false;
        $methodCodes = array_keys(Mage::getSingleton('shipping/config')->getAllCarriers());
        if (0 < count($data)) {
            $result = true;
            foreach ($data as $code => $row) {
                if (!in_array($code, $methodCodes)) continue;
                $result = $this->validateRow($code, $row) && $result;
            }
        }
        return $result;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function validateRow($code, $row)
    {
        $shippingTypeResult = $this->validateShippingType($code, $row);
        $shippingSpeedResult = $this->validateShippingSpeed($code, $row);
        $shippingDetailsResult = $this->validateShippingDetails($code, $row);
        return $shippingTypeResult && $shippingSpeedResult && $shippingDetailsResult;
    }

    private function validateShippingType($code, $row)
    {
        $validValues = Mage::getModel('ops/source_kwixo_shipMethodType')
            ->getValidValues();

        if (array_key_exists('kwixo_shipping_type', $row)
            && Zend_Validate::is(
                $row['kwixo_shipping_type'], 'Digits'
            )
            && 0 < $row['kwixo_shipping_type']
            && in_array((int)$row['kwixo_shipping_type'], $validValues)

        ) {
            return true;
        }
        $this->messages[$code]['kwixo_shipping_type_error']
            = 'invalid shipping type provided';

        return false;
    }

    protected function validateShippingSpeed($code, $row)
    {
        if (array_key_exists('kwixo_shipping_speed', $row)
            && Zend_Validate::is(
                $row['kwixo_shipping_speed'], 'Digits'
            )
            && 0 < $row['kwixo_shipping_speed']
        ) {
            return true;
        }
        $this->messages[$code]['kwixo_shipping_speed_error']
            = 'invalid shipping speed provided';

        return false;
    }

    protected function validateShippingDetails($code, $row)
    {
        if (array_key_exists('kwixo_shipping_details', $row)
            && Zend_Validate::is(
                $row['kwixo_shipping_details'], 'StringLength',
                array('min' => 0, 'max' => 50)
            )
        ) {
            return true;
        }
        $this->messages[$code]['kwixo_shipping_details_error']
            = 'invalid shipping details provided';

        return false;
    }
} 