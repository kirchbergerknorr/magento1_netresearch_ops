<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Block_Adminhtml_Kwixo_Shipping_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{


    protected $kwixoShippingModel = null;

    /**
     * gets the form action url
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('adminhtml/kwixoshipping/save');
    }

    /**
     * gets the shipping types
     *
     * @return array
     */
    public function getShippingMethods()
    {
        $methods = Mage::getSingleton('shipping/config')->getAllCarriers();
        $options = array();

        foreach ($methods as $carrierCode => $carrier) {
            if (!$title = Mage::getStoreConfig("carriers/$carrierCode/title")) {
                $title = $carrierCode;
            }
            $values = $this->getValues($carrierCode);
            $options[] = array('code' => $carrierCode, 'label' => $title, 'values' => $values);
        }
        
        return $options;
    }

    /**
     * returns the corresponding shipping method types
     *
     * @return array - the kwxixo Shipping method types
     */
    public function getKwixoShippingTypes()
    {
        return Mage::getModel('ops/source_kwixo_shipMethodType')->toOptionArray();
    }

    public function getKwixoShippingSettingModel()
    {
        if (null === $this->kwxioShippingModel) {
            $this->kwixoShippingModel = Mage::getModel('ops/kwixo_shipping_setting');
        }
        return $this->kwixoShippingModel;
    }

    /**
     * @param $carrierCode
     * @return array
     */
    protected function getValues($carrierCode)
    {
        $values = array(
            'kwixo_shipping_type' => '',
            'kwixo_shipping_speed' => '',
            'kwixo_shipping_details' => ''
        );
        if (null != ($this->getData('postData')) && array_key_exists($carrierCode, $this->getData('postData'))) {
            $errorData = $this->getData('postData');
            $values =  $errorData[$carrierCode];
        } else {
            $values = $this->getKwixoShippingSettingModel()->load($carrierCode, 'shipping_code')->getData();
        }
        return $values;
    }
}