<?php
/**
 * @author      Paul Siedler <paul.siedler@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Helper_Debitcard extends Netresearch_OPS_Helper_Creditcard
{
    protected function getPaymentSpecificParams(Mage_Sales_Model_Quote $quote)
    {
        $params = parent::getPaymentSpecificParams($quote);
        if ($this->getConfig()->getCreditDebitSplit($quote->getStoreId())) {
            $params['CREDITDEBIT'] = 'D';
        }
        return $params;
    }
} 