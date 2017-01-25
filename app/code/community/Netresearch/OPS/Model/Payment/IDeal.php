<?php

/**
 * Netresearch_OPS_Model_Payment_IDeal
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_IDeal
    extends Netresearch_OPS_Model_Payment_Abstract
{
    protected $pm = 'iDEAL';
    protected $brand = 'iDEAL';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    protected $_formBlockType = 'ops/form_ideal';

    /** payment code */
    protected $_code = 'ops_iDeal';

    /**
     * adds payment specific information to the payment
     *
     * @param mixed $data - data containing the issuer id which should be used
     *
     * @return Netresearch_OPS_Model_Payment_IDeal
     */
    public function assignData($data)
    {
        if ($data instanceof Varien_Object) {
            $data = $data->getData();
        }
        if (array_key_exists('iDeal_issuer_id', $data)) {
            $this->getInfoInstance()->setAdditionalInformation('iDeal_issuer_id', $data['iDeal_issuer_id']);
        }
        parent::assignData($data);

        return $this;
    }

    /**
     * getter for the iDeal issuers
     *
     * @return array
     */
    public function getIDealIssuers()
    {
        return Mage::getStoreConfig('payment/ops_iDeal/issuer');
    }

    /**
     * add iDeal issuer id to form fields
     *
     * @override Netresearch_OPS_Model_Payment_Abstract
     *
     * @param      $order
     * @param null $requestParams
     *
     * @return array
     */
    public function getMethodDependendFormFields($order, $requestParams = null)
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);
        if ($order->getPayment()->getAdditionalInformation('iDeal_issuer_id')) {
            $formFields['ISSUERID'] = $order->getPayment()->getAdditionalInformation('iDeal_issuer_id');
        }

        return $formFields;
    }
}

