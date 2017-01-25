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
 * DeviceController.php
 *
 * @category controller
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_DeviceController extends Mage_Core_Controller_Front_Action
{

    const CONSENT_PARAMETER_KEY = 'consent';

    protected $config = null;

    /**
     * @return Netresearch_OPS_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->config = Mage::getModel('ops/config');
        }

        return $this->config;
    }

    /**
     * Toggles the customers consent to transmit the generated deviceid to Ingenico ePayments
     * to the value specified in request parameter consent
     *
     */
    public function toggleConsentAction()
    {
        if ($this->getConfig()->getDeviceFingerPrinting()) {
            $consent = (bool)$this->getRequest()->getParam(self::CONSENT_PARAMETER_KEY);
            Mage::getSingleton('customer/session')
                ->setData(Netresearch_OPS_Model_Payment_Abstract::FINGERPRINT_CONSENT_SESSION_KEY, $consent);
        }

        $this->consentAction();
    }

    /**
     * Returns the state of consent of the customer
     */
    public function consentAction()
    {
        $resultArray = array(self::CONSENT_PARAMETER_KEY => false);

        if ($this->getConfig()->getDeviceFingerPrinting()) {
            $resultArray[self::CONSENT_PARAMETER_KEY] =
                (bool)Mage::getSingleton('customer/session')
                          ->getData(Netresearch_OPS_Model_Payment_Abstract::FINGERPRINT_CONSENT_SESSION_KEY);
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($resultArray));
    }
}
