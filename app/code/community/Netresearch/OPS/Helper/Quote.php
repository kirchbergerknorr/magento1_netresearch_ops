<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Helper_Quote extends Mage_Core_Helper_Abstract
{

    const PAGE_SIZE = 100;

    const MINUTES_IN_PAST = 15;

    protected $dataHelper = null;

    /**
     * cleans up old payment information (deletes cvc etc. from additional data)
     */
    public function cleanUpOldPaymentInformation()
    {
        $allowedTimestamp = new Zend_Db_Expr(
            sprintf(
                'NOW() - INTERVAL %d MINUTE', self::MINUTES_IN_PAST
            )
        );
        /*
         * fetching possible affected information from the sales_quote_payment table
         * criteria are:
         *  - ops_cc was used
         *  - the last update is more than 15 minutes ago
         *  - and CVC is included in the additional information
         */
        $paymentInformation = Mage::getModel('sales/quote_payment')
            ->getCollection()
            ->addFieldToFilter('method', array('eq' => 'ops_cc'))
            ->addFieldToFilter('updated_at', array('lt' => $allowedTimestamp))
            ->addFieldToFilter(
                'additional_information', array('like' => '%"cvc"%')
            )
            ->setOrder('created_at', 'DESC')
            ->setPageSize(self::PAGE_SIZE);
        foreach ($paymentInformation as $payment) {
            if (null != $payment->getAdditionalInformation('cvc')) {
                // quote needs to be loaded, because saving the payment information would fail otherwise
                $payment->setQuote(
                    Mage::getModel('sales/quote')->load($payment->getQuoteId())
                );
                Mage::helper('ops/alias')->cleanUpAdditionalInformation(
                    $payment, true
                );
                $payment->save();
            }

        }


    }

    /**
     * returns the quote currency
     *
     * @param $quote
     *
     * @return string - the quotes currency
     */
    public function getQuoteCurrency(Mage_Sales_Model_Quote $quote)
    {
        if ($quote->hasForcedCurrency()) {
            return $quote->getForcedCurrency()->getCode();
        } else {
            return Mage::app()->getStore($quote->getStoreId())
                ->getBaseCurrencyCode();
        }
    }

    /**
     * get payment operation code
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return string
     */
    public function getPaymentAction($order)
    {
        $operation
            = Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION;

        // different capture operation name for direct debits
        if ('Direct Debits DE' == $order->getPayment()
                ->getAdditionalInformation('PM')
            || 'Direct Debits AT' == $order->getPayment()
                ->getAdditionalInformation('PM')
        ) {
            if ('authorize_capture' == Mage::getModel('ops/config')
                    ->getPaymentAction($order->getStoreId())
            ) {
                return Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION;
            }

            return Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION;
        }
        // no RES for Direct Debits NL, so we'll do the final sale
        if ('Direct Debits NL' == $order->getPayment()
                ->getAdditionalInformation('PM')
        ) {
            if ('authorize_capture' == Mage::getModel('ops/config')
                    ->getPaymentAction($order->getStoreId())
            ) {
                return Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_DIRECTDEBIT_NL;
            }

            return Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION;
        }

        if ('authorize_capture' == Mage::getModel('ops/config')
                ->getPaymentAction($order->getStoreId())
        ) {
            $operation
                = Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION;
        }

        return $operation;
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }

        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * gets the data helper
     *
     * @return Netresearch_OPS_Helper_Data
     */
    public function getDataHelper()
    {
        if (null === $this->dataHelper) {
            $this->dataHelper = Mage::helper('ops/data');
        }

        return $this->dataHelper;
    }

    /**
     * sets the data helper
     *
     * @param Netresearch_OPS_Helper_Data $dataHelper
     */
    public function setDataHelper(Netresearch_OPS_Helper_Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }
}