<?php
/**
 * Netresearch_OPS_Block_Form_DirectDebit
 *
 * @package   OPS
 * @copyright 2012 Netresearch App Factory AG <http://www.netresearch.de>
 * @author    Thomas Birke <thomas.birke@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Block_Form_DirectDebit extends Netresearch_OPS_Block_Form
{

    /**
     * Backend Payment Template
     */
    const TEMPLATE = 'ops/form/directDebit.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * get ids of supported countries
     *
     * @return array
     */
    public function getDirectDebitCountryIds()
    {
        return explode(',', $this->getConfig()->getDirectDebitCountryIds());
    }

    /**
     * @return string
     */
    public function getSelectedCountryId()
    {
        $countryId = $this->getQuote()->getPayment()->getAdditionalInformation('country_id');
        if (Mage::app()->getStore()->isAdmin()) {
            $data = $this->getQuote()->getPayment()->getData('ops_directDebit_data');
            $countryId = $data && array_key_exists('country_id', $data) ? $data['country_id'] : '';
        }
        return $countryId;

    }
}
