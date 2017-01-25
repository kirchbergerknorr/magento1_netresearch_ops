<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * OPS direct ebanking countries
 */
class Netresearch_OPS_Model_Source_DirectEbanking_Brands
{
protected $options = array(
    array(
        'value' => 'DirectEbanking',
    ),
    array(
        'value' => 'DirectEbankingAT',
    ),
    array(
        'value' => 'DirectEbankingBE',
    ),
    array(
        'value' => 'DirectEbankingCH',
    ),
    array(
        'value' => 'DirectEbankingDE',
    ),
    array(
        'value' => 'DirectEbankingFR',
    ),
    array(
        'value' => 'DirectEbankingGB',
    )
);

    /**
    * @return array
    */
    public function toOptionArray()
    {
        foreach ($this->options as $key => $value) {
            $this->options[$key]['label'] = Mage::helper('ops')->__($value['value']);
        }
        return $this->options;
    }

}
