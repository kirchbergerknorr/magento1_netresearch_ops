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
 * @copyright   Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license     Open Software License (OSL 3.0)
 * @link        http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Netresearch_OPS_Model_Payment_BankContact
 *
 * @category    Ingenico
 * @package     Netresearch_OPS
 * @author      Sebastian Ertner <sebastian.ertner@netresearch.de>
 */
class Netresearch_OPS_Model_Payment_Bancontact
    extends Netresearch_OPS_Model_Payment_Abstract
{
    protected $pm = 'CreditCard';
    protected $brand = 'BCMC';
    const CODE = 'ops_BCMC';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_bancontact';

    /** payment code */
    protected $_code = self::CODE;

    protected $_mobileDetectHelper = null;

    /**
     * add needed params to dependend formfields
     *
     * @param Mage_Sales_Model_Order $order
     * @param null $requestParams
     * @return string[]
     */
    public function getMethodDependendFormFields($order, $requestParams = null)
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);
        $formFields['DEVICE'] =  $this->getInfoInstance()->getAdditionalInformation('DEVICE');

        return $formFields;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $payment = $this->getInfoInstance();
        $payment->setAdditionalInformation('DEVICE', $this->getMobileDetectHelper()->getDeviceType());

        return $this;
    }

    /**
     * Get Mobile Detect Helper
     *
     * @return Netresearch_OPS_Helper_MobileDetect
     */
    public function getMobileDetectHelper()
    {
        if ($this->_mobileDetectHelper === null) {
            $this->_mobileDetectHelper = Mage::helper('ops/mobileDetect');
        }
        return $this->_mobileDetectHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_MobileDetect $mobileHelper
     *
     * @returns $this
     */
    public function setMobileDetectHelper($mobileHelper)
    {
        $this->_mobileDetectHelper = $mobileHelper;

        return $this;
    }

}

