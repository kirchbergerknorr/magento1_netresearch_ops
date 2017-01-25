<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Add variable to whitelist due to APPSEC-1057/SUPEE-6788
 *
 * @category OPS
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$variableName = 'trans_email/ident_support/email';

$adminVersion = Mage::getConfig()->getModuleConfig('Mage_Admin')->version;
if (version_compare($adminVersion, '1.6.1.1', '>')) {
    /** @var Mage_Admin_Model_Variable $variable */
    $variable = Mage::getModel('admin/variable')->load($variableName, 'variable_name');

    $variable->setData('variable_name', $variableName)
             ->setData('is_allowed', 1)
             ->save();
}