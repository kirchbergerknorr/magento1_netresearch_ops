<?php
/**
 * data-install-14.02.05.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
// for new installations set the default mode to test instead of custom
$installer->setConfigData(Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'mode', Netresearch_OPS_Model_Source_Mode::TEST);
