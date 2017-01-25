<?php

/**
 * Netresearch_OPS_Model_Payment_OpenInvoiceAt
 *
 * @package
 * @copyright 2016 Netresearch
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_OpenInvoiceAt
    extends Netresearch_OPS_Model_Payment_OpenInvoice_Abstract
{
    protected $pm = 'Open Invoice AT';
    protected $brand = 'Open Invoice AT';

    /** if we can capture directly from the backend */
    protected $_canBackendDirectCapture = false;
    protected $_canCapturePartial = false;
    protected $_canRefundInvoicePartial = false;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_openInvoiceAt';

    /**
     * Open Invoice AT is not available if quote has a coupon
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return boolean
     */
    public function isAvailable( $quote = null ) 
    {
        /* availability depends on quote */
        if ( false == $quote instanceof Mage_Sales_Model_Quote ) {
            return false;
        }

        /* not available if quote contains a coupon and allow_discounted_carts is disabled */
        if ( !$this->isAvailableForDiscountedCarts()
            && $quote->getSubtotal() != $quote->getSubtotalWithDiscount()
        ) {
            return false;
        }

        /* not available if there is no gender or no birthday */
        if ($quote->getCustomerGender() == null || $quote->getCustomerDob() == null) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function getPaymentAction() 
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param array|null             $requestParams
     *
     * @return array
     */
    public function getMethodDependendFormFields( $order, $requestParams = null ) 
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);

        $shippingAddress = $order->getShippingAddress();

        $gender = Mage::getSingleton('eav/config')
                      ->getAttribute('customer', 'gender')
                      ->getSource()
                      ->getOptionText($order->getCustomerGender());

        $formFields[ 'CIVILITY' ]               = $gender == 'Male' ? 'Herr' : 'Frau';
        $formFields[ 'ECOM_CONSUMER_GENDER' ]   = $gender == 'Male' ? 'M' : 'F';

        // Change address format to make austrian addresses compatible with platform data transfer to Klarna
        $billToParams = $this->getRequestHelper()->extractBillToParameters($order->getBillingAddress(), $order);
        $formFields['OWNERADDRESS'] = $billToParams['ECOM_BILLTO_POSTAL_STREET_LINE1'] . ' '
            . $billToParams['ECOM_BILLTO_POSTAL_STREET_NUMBER'];
        $formFields['ECOM_BILLTO_POSTAL_STREET_NUMBER'] = ' ';

        if (!$this->getConfig()->canSubmitExtraParameter($order->getStoreId()) ) {
            // add the shipto parameters even if the submitOption is false, because they are required for OpenInvoice
            $shipToParams = $this->getRequestHelper()->extractShipToParameters($shippingAddress, $order);
            $formFields = array_merge($formFields, $shipToParams);
        }

        return $formFields;
    }

    /**
     * getter for the allow_discounted_carts
     *
     * @return bool
     */
    protected function isAvailableForDiscountedCarts() 
    {
        return (bool) $this->getConfigData('allow_discounted_carts');
    }

}