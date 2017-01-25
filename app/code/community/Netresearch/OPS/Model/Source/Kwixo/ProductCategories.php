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
 * Source Model for ProductCategories
 */
class Netresearch_OPS_Model_Source_Kwixo_ProductCategories
{
    /**
     * return the product categories as array
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 1,
                'label' => Mage::helper('ops/data')->__('Food & gastronomy')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('ops/data')->__('Car & Motorbike')
            ),
            array(
                'value' => 3,
                'label' => Mage::helper('ops/data')->__('Culture & leisure')
            ),
            array(
                'value' => 4,
                'label' => Mage::helper('ops/data')->__('Home & garden')
            ),
            array(
                'value' => 5,
                'label' => Mage::helper('ops/data')->__('Appliances')
            ),
            array(
                'value' => 6,
                'label' => Mage::helper('ops/data')->__('Auctions and bulk purchases')
            ),
            array(
                'value' => 7,
                'label' => Mage::helper('ops/data')->__('Flowers & gifts')
            ),
            array(
                'value' => 8,
                'label' => Mage::helper('ops/data')->__('Computer & software')
            ),
            array(
                'value' => 9,
                'label' => Mage::helper('ops/data')->__('Health & beauty')
            ),
            array(
                'value' => 10,
                'label' => Mage::helper('ops/data')->__('Services for individuals')
            ),
            array(
                'value' => 11,
                'label' => Mage::helper('ops/data')->__('Services for professionals')
            ),
            array(
                'value' => 12,
                'label' => Mage::helper('ops/data')->__('Sports')
            ),
            array(
                'value' => 13,
                'label' => Mage::helper('ops/data')->__('Clothing & accessories')
            ),
            array(
                'value' => 14,
                'label' => Mage::helper('ops/data')->__('Travel & tourism')
            ),
            array(
                'value' => 15,
                'label' => Mage::helper('ops/data')->__('Hifi, photo & video')
            ),
            array(
                'value' => 16,
                'label' => Mage::helper('ops/data')->__('Telephony & communication')
            ),
            array(
                'value' => 17,
                'label' => Mage::helper('ops/data')->__('Jewelry & precious metals')
            ),
            array(
                'value' => 18,
                'label' => Mage::helper('ops/data')->__('Baby articles and accessories')
            ),
            array(
                'value' => 19,
                'label' => Mage::helper('ops/data')->__('Sound & light')
            )
        );
    }

    public function getValidKwixoCategoryIds()
    {
        $kwixoValues = array();
        foreach ($this->toOptionArray() as $option) {
            $kwixoValues[] = $option['value'];
        }
        return $kwixoValues;
    }
}