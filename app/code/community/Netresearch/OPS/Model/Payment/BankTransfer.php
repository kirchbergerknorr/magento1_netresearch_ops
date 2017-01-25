<?php
/**
 * Netresearch_OPS_Model_Payment_BankTransfer
 * 
 * @package   
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_BankTransfer
    extends Netresearch_OPS_Model_Payment_Abstract
{
    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** form block type  */
    protected $_formBlockType = 'ops/form_bankTransfer';

    /** payment code */
    protected $_code = 'ops_bankTransfer';


    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $countryId = '';
        if (is_object($data) && $data instanceof Varien_Object) {
            $countryId = $data->getCountryId();
        } elseif (is_array($data) && isset($data['country_id'])) {
            $countryId = $data['country_id'];
        }
        $pm = $brand = trim('Bank transfer' . (('*' == $countryId) ? '' : ' ' . $countryId));

        $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
        $payment->setAdditionalInformation('PM', $pm);
        $payment->setAdditionalInformation('BRAND', $brand);

        parent::assignData($data);
        return $this;
    }
}
