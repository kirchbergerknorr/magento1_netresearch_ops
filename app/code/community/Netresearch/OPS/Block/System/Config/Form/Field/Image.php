<?php

/**
 * Netresearch OPS
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
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2016 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch
 *
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @author      Benjamin Heuer <benjamin.heuer@netresearch.de>
 */
class Netresearch_OPS_Block_System_Config_Form_Field_Image
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Image
{

    /**
     * Return element html code
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $url  = $this->_getUrl();

        if (!empty($url)) {
            $value = $this->getData('value');
            $html  = '<a href="' . $url . '"'
                . ' onclick="imagePreview(\'' . $this->getHtmlId() . '_image\'); return false;">'
                . '<img src="' . $url . '" id="' . $this->getHtmlId() . '_image" title="' . $value . '"'
                . ' alt="' . $value . '" height="22" width="22" class="small-image-preview v-middle" />'
                . '</a> ';
        }
        $this->setData('class', 'input-file');
        $html .= Varien_Data_Form_Element_Abstract::getElementHtml();
        $html .= $this->_getDeleteCheckbox();

        return $html;
    }

    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        $url    = $this->getData('value');
        $config = $this->getData('field_config');

        if (!empty($url)) {
            /* @var $config Varien_Simplexml_Element */
            if (!empty($config->base_url)) {
                $el      = $config->descend('base_url');
                $urlType = empty($el['type']) ? 'link' : (string)$el['type'];
                $url     = Mage::getBaseUrl($urlType) . (string)$config->base_url . '/' . $url;
            }
        } else {
            preg_match("/groups\[([a-zA-Z_]*)/", $this->getData('name'), $imageName);

            if (isset($imageName[1])) {
                $url = Mage::helper('ops/payment')->getPaymentDefaultLogo($imageName[1]);
            }
        }

        return $url;
    }
}
