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
 * RecurringTypes.php
 *
 * @category payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Model_Source_Cc_RecurringTypes
{

    public function toOptionArray()
    {
        $options = array();
        $types = Mage::getModel('ops/config')->getAllRecurringCcTypes();
        $translatedTypes = array();
        foreach ($types as $type) {
            $translatedTypes[Mage::helper('ops')->__($type)] = $type;
        }
        ksort($translatedTypes);
        foreach ($translatedTypes as $label => $value) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }

}