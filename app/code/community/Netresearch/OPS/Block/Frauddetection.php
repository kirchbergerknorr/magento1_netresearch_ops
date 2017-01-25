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
class Netresearch_OPS_Block_Frauddetection
    extends Mage_Core_Block_Template
{
    protected $_template = "ops/frauddetection.phtml";
    
    const TRACKING_CODE_APPLICATION_ID = "10376";

    /**
     * renders the additional fraud detection js
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = null;
        $storeId = Mage::helper('core/data')->getStoreId();
        if (true == Mage::getModel('ops/config')->getDeviceFingerPrinting($storeId)
        && Mage::getSingleton('customer/session')->getData(Netresearch_OPS_Model_Payment_Abstract::FINGERPRINT_CONSENT_SESSION_KEY)) {
            $html = parent::_toHtml();
        }

        return $html;
    }

    /**
     * get the tracking code application id from config
     *
     * @return string
     */
    public function getTrackingCodeAid()
    {
        return self::TRACKING_CODE_APPLICATION_ID;
    }


    /**
     * build md5 hash from customer session ID
     *
     * @return string
     */
    public function getTrackingSid()
    {
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();

        return md5(Mage::getModel('ops/config')->getPSPID($quote->getStoreId()) . Mage::helper('ops/order')->getOpsOrderId($quote));
    }

}