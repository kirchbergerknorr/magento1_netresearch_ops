<?php
/**
 * Netresearch_OPS_Model_Payment_InterSolve
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_InterSolve
    extends Netresearch_OPS_Model_Payment_Abstract
{
    protected $pm = 'InterSolve';
    protected $brand = 'InterSolve';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';
    protected $_formBlockType = 'ops/form_interSolve';

    /** payment code */
    protected $_code = 'ops_interSolve';

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
            $brand = $data->getIntersolveBrand();
        } elseif (is_array($data) && isset($data['intersolve_brand'])) {
            $brand = $data['intersolve_brand'];
        }
        if (strlen(trim($brand)) === 0) {
            $brand = 'InterSolve';
        }
        $payment = $this->getInfoInstance();
        $payment->setAdditionalInformation('BRAND', $brand);

        parent::assignData($data);
        return $this;
    }
}

