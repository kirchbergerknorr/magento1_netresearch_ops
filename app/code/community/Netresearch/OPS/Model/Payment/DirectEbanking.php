<?php
/**
 * Netresearch_OPS_Model_Payment_DirectEbanking
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_DirectEbanking
    extends Netresearch_OPS_Model_Payment_Abstract
{
    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** form block type  */
    protected $_formBlockType = 'ops/form_directEbanking';

    /** payment code */
    protected $_code = 'ops_directEbanking';

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $brand = '';

        if (is_object($data) && $data instanceof Varien_Object) {
            $brand = $data['directEbanking_brand'];
        } elseif (is_array($data) && isset($data['directEbanking_brand'])) {

            $brand = $data['directEbanking_brand'];
        }

        $brand = $this->fixSofortUberweisungBrand($brand);

        $payment = $this->getInfoInstance();
        // brand == pm for all DirectEbanking methods
        $payment->setAdditionalInformation('PM', $brand);
        $payment->setAdditionalInformation('BRAND', $brand);
        parent::assignData($data);
        return $this;
    }


    /**
     * Fixes legacy brand value of Sofort Uberweisung for DirectEbanking
     *
     * @param string $value
     * @return string
     */
    protected function fixSofortUberweisungBrand($value)
    {
        if ($value === 'Sofort Uberweisung') {
            return 'DirectEbanking';
        }
        return $value;
    }
}

