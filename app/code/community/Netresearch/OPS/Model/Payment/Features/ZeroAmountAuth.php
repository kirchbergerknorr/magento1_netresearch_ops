<?php

/**
 * Netresearch_OPS_Model_Payment_Features_ZeroAmountAuth
 *
 * @package
 * @copyright 2014 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_Features_ZeroAmountAuth
{

    /**
     * check if payment method is cc and zero amount authorization is enabled
     *
     * @param Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod
     *
     * @return bool
     */
    public function isCCAndZeroAmountAuthAllowed(
        Netresearch_OPS_Model_Payment_Abstract $opsPaymentMethod,
        Mage_Sales_Model_Quote $quote
    ) 
    {
        $result  = false;
        $storeId = $quote->getStoreId();
        if ($quote->getBaseGrandTotal() < 0.01
            && $opsPaymentMethod instanceof Netresearch_OPS_Model_Payment_Cc
            && $opsPaymentMethod->isZeroAmountAuthorizationAllowed($storeId)
            && 0 < $quote->getItemsCount()
            && !$quote->isNominal()

        ) {
            $result = true;
        }

        return $result;
    }

} 