<?php
/**
 * Mode.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */

class Netresearch_OPS_Model_System_Config_Mode extends Mage_Core_Model_Config_Data
{

    public function _afterSave()
    {

        if ($this->getValue() != Netresearch_OPS_Model_Source_Mode::CUSTOM && $this->isValueChanged()) {
            $xmlConfig = Mage::getConfig()->loadModulesConfiguration('config.xml');
            foreach ($this->getUrlPaths() as $path) {
                $default = $xmlConfig->getNode('default/'.$path);
                $newValue = preg_replace('/\/ncol\/\w+/', '/ncol/'.$this->getValue(), $default);
                Mage::getConfig()->saveConfig($path, $newValue, $this->getScope(), $this->getScopeId());

            }
        }

        return parent::_afterSave();
    }

    protected function getUrlPaths()
    {
        return array(
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'ops_gateway',
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'ops_alias_gateway',
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'frontend_gateway',
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'directlink_gateway',
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'directlink_gateway_order',
            Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH.'directlink_maintenance_api'
        );
    }

}
