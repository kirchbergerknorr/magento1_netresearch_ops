<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Helper_Validation_Checkout_Step
{

    const BILLING_STEP = 'billing';

    const SHIPPING_STEP = 'shipping';

    const BILLING_PARAMETER_STRING_VALUE  = 'billing';

    const SHIPPING_PARAMETER_STRING_VALUE = 'shipping';

    /**
     * retrieves the params for pushing back to the billing step
     *
     * @return array
     */
    protected function determineStep()
    {
        $result       = array();
        $mappedParams = $this->getMappedParams();

        foreach ($mappedParams as $paramName => $value) {
            if (strpos($value, self::BILLING_PARAMETER_STRING_VALUE) === 0) {
                $result[self::BILLING_PARAMETER_STRING_VALUE][]  = $paramName;
            }
            if (strpos($value, self::SHIPPING_PARAMETER_STRING_VALUE) === 0) {
                $result[self::SHIPPING_STEP][] = $paramName;
            }
        }

        return $result;
    }


    protected function getMappedParams()
    {
        $result = array();
        $paramLengthFields      = $this->getConfig()->getParameterLengths();
        $frontendFieldMapping   = $this->getConfig()->getFrontendFieldMapping();

        foreach (array_keys($paramLengthFields) as $key) {
            if (isset($frontendFieldMapping[$key])) {
                $frontendField = !is_array($frontendFieldMapping[$key])
                    ? $frontendFieldMapping[$key]
                    : implode(
                        ',', $frontendFieldMapping[$key]
                    );
                $result[$key] = $frontendField;
            }
        }

        return $result;
    }

    /**
     * gets the corresponding checkout step for the erroneous fields
     *
     * @param array $erroneousFields
     *
     * @return string - the checkout step
     */
    public function getStep(array $erroneousFields)
    {
        $checkoutStep = '';
        $stepParams   = $this->determineStep();
        foreach ($erroneousFields as $erroneousField) {
            if (isset($stepParams[self::BILLING_STEP])
                && in_array($erroneousField, $stepParams[self::BILLING_STEP])
            ) {
                $checkoutStep = self::BILLING_STEP;
                break;
            }
            if (isset($stepParams[self::SHIPPING_STEP])
                && in_array($erroneousField, $stepParams[self::SHIPPING_STEP])
            ) {
                $checkoutStep = self::SHIPPING_STEP;
            }
        }

        return $checkoutStep;
    }

    /**
     * @return Netresearch_OPS_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getModel('ops/config');
    }
} 