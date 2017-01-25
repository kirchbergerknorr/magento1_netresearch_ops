<?php
/**
 * Netresearch_OPS_Model_Payment_OpenInvoiceNl
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_OpenInvoiceNl
    extends Netresearch_OPS_Model_Payment_OpenInvoice_Abstract
{
    protected $pm = 'Open Invoice NL';
    protected $brand = 'Open Invoice NL';

    /** if we can capture directly from the backend */
    protected $_canBackendDirectCapture = false;


    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_openInvoiceNl';


    /**
     * Open Invoice NL is not available if quote has a coupon
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

        /* not available if there is no gender or no birthday */
        if (null === $quote->getCustomerGender() || is_null($quote->getCustomerDob())) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * get some method dependend form fields
     *
     * @param Mage_Sales_Model_Quote $order
     * @param array $requestParams
     * @return array
     */
    public function getMethodDependendFormFields($order, $requestParams=null)
    {
        $billingAddress  = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $billingStreet   = str_replace("\n", ' ', $billingAddress->getStreet(-1));

        $splittedBillingStreet = Mage::helper('ops/address')->splitStreet($billingStreet);
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);

        $gender = Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'gender')
            ->getSource()
            ->getOptionText($order->getCustomerGender());

        $formFields['CIVILITY']                         = $gender == 'Male' ? 'M' : 'V';
        $formFields['ECOM_CONSUMER_GENDER']             = $gender == 'Male' ? 'M' : 'V';
        $formFields['OWNERADDRESS']                     = $splittedBillingStreet['street_name'];
        $formFields['ECOM_BILLTO_POSTAL_STREET_NUMBER'] = $splittedBillingStreet['street_number'];
        $formFields['OWNERZIP']                         = $billingAddress->getPostcode();
        $formFields['OWNERTOWN']                        = $billingAddress->getCity();
        $formFields['OWNERCTY']                         = $billingAddress->getCountry();
        $formFields['OWNERTELNO']                       = $billingAddress->getTelephone();

        $shippingStreet = str_replace("\n", ' ', $shippingAddress->getStreet(-1));

        $splittedShippingStreet = Mage::Helper('ops/address')->splitStreet($shippingStreet);

        $formFields['ECOM_SHIPTO_POSTAL_NAME_PREFIX']   = $shippingAddress->getPrefix();
        $formFields['ECOM_SHIPTO_POSTAL_NAME_FIRST']    = $shippingAddress->getFirstname();
        $formFields['ECOM_SHIPTO_POSTAL_NAME_LAST']     = $shippingAddress->getLastname();
        $formFields['ECOM_SHIPTO_POSTAL_STREET_LINE1']  = $splittedShippingStreet['street_name'];
        $formFields['ECOM_SHIPTO_POSTAL_STREET_NUMBER'] = $splittedShippingStreet['street_number'];
        $formFields['ECOM_SHIPTO_POSTAL_POSTALCODE']    = $shippingAddress->getPostcode();
        $formFields['ECOM_SHIPTO_POSTAL_CITY']          = $shippingAddress->getCity();
        $formFields['ECOM_SHIPTO_POSTAL_COUNTRYCODE']   = $shippingAddress->getCountry();

        // copy some already known values
        $formFields['ECOM_SHIPTO_ONLINE_EMAIL']         = $order->getCustomerEmail();

        if (is_array($requestParams)) {
            if (array_key_exists('OWNERADDRESS', $requestParams)) {
                $formFields['OWNERADDRESS'] = $requestParams['OWNERADDRESS'];
            }
            if (array_key_exists('ECOM_BILLTO_POSTAL_STREET_NUMBER', $requestParams)) {
                $formFields['ECOM_BILLTO_POSTAL_STREET_NUMBER'] = $requestParams['ECOM_BILLTO_POSTAL_STREET_NUMBER'];
            }
            if (array_key_exists('ECOM_SHIPTO_POSTAL_STREET_LINE1', $requestParams)) {
                $formFields['ECOM_SHIPTO_POSTAL_STREET_LINE1'] = $requestParams['ECOM_SHIPTO_POSTAL_STREET_LINE1'];
            }
            if (array_key_exists('ECOM_SHIPTO_POSTAL_STREET_NUMBER', $requestParams)) {
                $formFields['ECOM_SHIPTO_POSTAL_STREET_NUMBER'] = $requestParams['ECOM_SHIPTO_POSTAL_STREET_NUMBER'];
            }
        }

        return $formFields;
    }

    /**
     * get question for fields with disputable value
     * users are asked to correct the values before redirect to Ingenico ePayments
     *
     * @return string
     */
    public function getQuestion()
    {
        return Mage::helper('ops')->__('Please make sure that your street and house number are correct.');
    }

    /**
     * get an array of fields with disputable value
     * users are asked to correct the values before redirect to Ingenico ePayments
     *
     *
     * @return array
     */
    public function getQuestionedFormFields($order)
    {
        return array(
            'OWNERADDRESS',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER',
            'ECOM_SHIPTO_POSTAL_STREET_LINE1',
            'ECOM_SHIPTO_POSTAL_STREET_NUMBER',
        );
    }

}
