<?php
/**
 * Netresearch_OPS_Model_Payment_OpenInvoiceDe
 * 
 * @package   
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_OpenInvoiceDe
    extends Netresearch_OPS_Model_Payment_OpenInvoice_Abstract
{
    protected $pm = 'Open Invoice DE';
    protected $brand = 'Open Invoice DE';

    /** if we can capture directly from the backend */
    protected $_canBackendDirectCapture = false;

    protected $_canCapturePartial = false;
    protected $_canRefundInvoicePartial = false;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_openInvoiceDe';

    /**
     * Open Invoice DE is not available if quote has a coupon
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote=null)
    {
        /* availability depends on quote */
        if (false == $quote instanceof Mage_Sales_Model_Quote) {
            return false;
        }

        /* not available if quote contains a coupon and allow_discounted_carts is disabled */
        if (!$this->isAvailableForDiscountedCarts()
            && $quote->getSubtotal() != $quote->getSubtotalWithDiscount()
        ) {
            return false;
        }

        /* not available if there is no gender or no birthday */
        if (null === $quote->getCustomerGender() || is_null($quote->getCustomerDob())) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function getPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
    }

    public function getMethodDependendFormFields($order, $requestParams=null)
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);

        $shippingAddress = $order->getShippingAddress();

        $gender = Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'gender')
            ->getSource()
            ->getOptionText($order->getCustomerGender());

        $formFields[ 'CIVILITY' ]               = $gender == 'Male' ? 'Herr' : 'Frau';
        $formFields[ 'ECOM_CONSUMER_GENDER' ]   = $gender == 'Male' ? 'M' : 'F';

        if (!$this->getConfig()->canSubmitExtraParameter($order->getStoreId())) {
            // add the shipto parameters even if the submitOption is false, because they are required for OpenInvoice
            $shipToParams = $this->getRequestHelper()->extractShipToParameters($shippingAddress, $order);
            $formFields   = array_merge($formFields, $shipToParams);
        }

        return $formFields;
    }

    /**
     * getter for the allow_discounted_carts
     *
     * @return array
     */
    protected function isAvailableForDiscountedCarts()
    {
        return (bool) $this->getConfigData('allow_discounted_carts');
    }

}

