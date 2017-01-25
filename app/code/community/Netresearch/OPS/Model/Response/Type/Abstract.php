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
 * Abstract.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

/**
 * Class Netresearch_OPS_Model_Response_Type_Abstract
 *
 * @method Netresearch_OPS_Model_Payment_Abstract getMethodInstance()
 * @method Netresearch_OPS_Model_Response_Type_Abstract setMethodInstance(Netresearch_OPS_Model_Payment_Abstract $instance)
 * @method int getStatus()
 * @method int getPayid()
 * @method bool hasPayidsub()
 * @method int getPayidsub()
 * @method float getAmount()
 * @method string getCurrency()
 * @method string getOrderid()
 * @method string getAavaddress()
 * @method string getAavcheck()
 * @method string get Aavzip()
 * @method string getAcceptance()
 * @method string getBin()
 * @method string getBrand()
 * @method string getCardno()
 * @method string getCccty()
 * @method string getCn()
 * @method string getCvccheck()
 * @method string getScoring()
 * @method bool hasScoring()
 * @method string getScoCategory()
 * @method bool hasScoCategory()
 * @method string getShasign()
 * @method string getSubbrand()
 * @method string getTrxdate()
 * @method string getVc()
 * @method int getNcstatus()
 * @method string getNcerror()
 * @method string getNcerrorplus()
 * @method string getHtmlAnswer()
 * @method string getIpcty()
 * @method bool hasAcceptance()
 * @method bool hasBrand()
 * @method bool hasAlias()
 * @method bool hasMobilemode()
 * @method string getAlias()
 * @method bool getShouldRegisterFeedback() if feedback should get registered on payment object
 * @method Netresearch_OPS_Model_Response_Type_Abstract setShouldRegisterFeedback($shouldRegisterFeedback)
 *
 */
abstract class Netresearch_OPS_Model_Response_Type_Abstract extends Varien_Object
    implements Netresearch_OPS_Model_Response_TypeInterface
{

    /**
     * @return Netresearch_OPS_Model_Config
     */
    public function getConfig()
    {
        if ($this->getData('config') === null) {
            $this->setData('config', Mage::getModel('ops/config'));
        }

        return $this->getData('config');
    }

    /**
     * Performs the necessary actions for Magento to progress the order state correctly and automatically build the
     * create sales objects
     *
     * @param array                                  $responseArray
     * @param Netresearch_OPS_Model_Payment_Abstract $paymentMethod
     * @param bool                                   $shouldRegisterFeedback
     *                                               determines if the Mage_Sales_Model_Order_Payments register*Feedback
     *                                               functions get called, defaults to true
     *
     * @return Netresearch_OPS_Model_Response_TypeInterface
     */
    public function handleResponse($responseArray, Netresearch_OPS_Model_Payment_Abstract $paymentMethod,
        $shouldRegisterFeedback = true
    ) {
        $this->setData(array_change_key_case($responseArray, CASE_LOWER));
        $this->setMethodInstance($paymentMethod);
        $this->setShouldRegisterFeedback($shouldRegisterFeedback);

        if ($this->getStatus() == $this->getMethodInstance()->getInfoInstance()->getAdditionalInformation('status')
            && $this->getTransactionId() == $paymentMethod->getInfoInstance()->getLastTransId()
        ) {
            return $this;
        }

        $this->setGeneralTransactionInfo();
        $this->_handleResponse();
        $this->updateAdditionalInformation();

        if ($this->getShouldRegisterFeedback() && $this->hasAlias()) {
            Mage::helper('ops/alias')->saveAlias($responseArray);
        }

        return $this;
    }

    /**
     * Handles the specific actions for the concrete payment status
     */
    protected abstract function _handleResponse();


    /**
     * Updates the additional information of the payment info object
     *
     * @see \Netresearch_OPS_Model_Response_Type_Abstract::updateDefaultInformation
     * @see \Netresearch_OPS_Model_Response_Type_Abstract::setFraudDetectionParameters
     */
    protected function updateAdditionalInformation()
    {
        $this->getMethodInstance()->getInfoInstance()->setLastTransId($this->getTransactionId());
        $this->updateDefaultInformation();
        $this->setFraudDetectionParameters();
        $this->setDeviceInformationParameters();
    }

    /**
     * Updates default information in additional information of the payment info object
     */
    protected function updateDefaultInformation()
    {
        $payment = $this->getMethodInstance()->getInfoInstance();

        $payment->setAdditionalInformation('paymentId', $this->getPayid())
            ->setAdditionalInformation('status', $this->getStatus());

        if ($this->hasAlias()) {
            $payment->setAdditionalInformation('alias', $this->getAlias());
        }

        if ($this->hasAcceptance()) {
            $payment->setAdditionalInformation('acceptence', $this->getAcceptance());
        }

        if ($this->hasBrand() && $this->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Cc) {
            $payment->setAdditionalInformation('CC_BRAND', $this->getBrand());
        }
    }

    protected function setDeviceInformationParameters()
    {
        if (!$this->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Bancontact) {
            return;
        }

        $payment = $this->getMethodInstance()->getInfoInstance();
        if ($this->hasMobilemode()) {
            $payment->setAdditionalInformation('MOBILEMODE', $this->getMobilemode());
        }
    }

    /**
     * Sets Transaction details (TransactionId etc.)
     */
    protected function setGeneralTransactionInfo()
    {
        $payment = $this->getMethodInstance()->getInfoInstance();

        $payment->setTransactionParentId($this->getPayid());

        if (!$this->hasPayidsub()) {
            $transId = $payment->getLastTransId();
        } else {
            $transId = $this->getTransactionId();
        }

        $payment->setTransactionId($transId);
        $payment->setIsTransactionClosed(false);
    }

    /**
     * Updates fraud detection information on additional information of the payment info object
     */
    protected function setFraudDetectionParameters()
    {
        $payment = $this->getMethodInstance()->getInfoInstance();
        if ($this->hasScoring()) {
            $payment->setAdditionalInformation('scoring', $this->getScoring());
        }

        if ($this->hasScoCategory()) {
            $payment->setAdditionalInformation('scoringCategory', $this->getScoCategory());
        }

        $additionalScoringData = array();
        foreach ($this->getConfig()->getAdditionalScoringKeys() as $key) {
            if ($this->hasData(strtolower($key))) {
                if (false === mb_detect_encoding($this->getData(strtolower($key)), 'UTF-8', true)) {
                    $additionalScoringData[$key] = utf8_encode($this->getData(strtolower($key)));
                } else {
                    $additionalScoringData[$key] = $this->getData(strtolower($key));
                }

            }
        }

        $payment->setAdditionalInformation('additionalScoringData', $additionalScoringData);
    }

    /**
     * @param string $orderComment
     * @param string $additionalInfo
     *
     */
    protected function addOrderComment($orderComment, $additionalInfo = '')
    {
        $orderComment = $this->getOrderComment($orderComment, $additionalInfo);
        $this->getMethodInstance()->getInfoInstance()->getOrder()->addStatusHistoryComment($orderComment);
    }

    /**
     * Add order comment about final status
     *
     * @param string $additionalInfo
     */
    protected function addFinalStatusComment($additionalInfo = '')
    {
        $this->addOrderComment($this->getFinalStatusComment($additionalInfo));
    }

    /**
     * Add order comment about intermediate status
     *
     * @param string $additionalInfo
     */
    protected function addIntermediateStatusComment($additionalInfo = '')
    {
        $this->addOrderComment($this->getIntermediateStatusComment($additionalInfo));
    }

    /**
     * Add order comment about refused status
     *
     * @param string $additionalInfo
     */
    protected function addRefusedStatusComment($additionalInfo = '')
    {
        $this->addOrderComment($this->getRefusedStatusComment($additionalInfo));
    }

    /**
     * Add order comment about fraud status
     *
     * @param string $additionalInfo
     */
    protected function addFraudStatusComment($additionalInfo = '')
    {
        $this->addOrderComment($this->getFraudStatusComment($additionalInfo));
    }


    /**
     * @param string $additionalInfo
     *
     * @return string
     */
    protected function getFinalStatusComment($additionalInfo = '')
    {
        $orderComment = Mage::helper('ops')->__(
            'Received Ingenico ePayments feedback status update with final status %s.',
            $this->getStatus()
        );

        return $this->getOrderComment($orderComment, $additionalInfo);

    }

    /**
     * @param string $additionalInfo
     *
     * @return string
     */
    protected function getIntermediateStatusComment($additionalInfo = '')
    {
        $orderComment = Mage::helper('ops')->__(
            'Received Ingenico ePayments feedback status update with intermediate status %s.',
            $this->getStatus()
        );

        return $this->getOrderComment($orderComment, $additionalInfo);
    }

    /**
     * @param string $additionalInfo
     *
     * @return string
     */
    protected function getRefusedStatusComment($additionalInfo = '')
    {
        $orderComment = Mage::helper('ops')->__(
            'Received Ingenico ePayments feedback status update with refused status %s.',
            $this->getStatus()
        );

        return $this->getOrderComment($orderComment, $additionalInfo);
    }

    /**
     * @param string $additionalInfo
     *
     * @return string
     */
    protected function getFraudStatusComment($additionalInfo = '')
    {
        $orderComment = Mage::helper('ops')->__(
            'Received Ingenico ePayments feedback status update with suspected fraud status %s.',
            $this->getStatus()
        );

        return $this->getOrderComment($orderComment, $additionalInfo);
    }

    /**
     * @param string $additionalInfo
     * @param string $orderComment
     *
     * @return string
     */
    protected function getOrderComment($orderComment, $additionalInfo = '')
    {
        if ($additionalInfo) {
            $orderComment .= ' ' . $additionalInfo;
        }

        return $orderComment;
    }

    /**
     * Merges the PAYID with the PAYIDSUB, if the latter is present, otherwise just returns the PAYID
     *
     * @return string
     */
    public function getTransactionId()
    {
        $transId = $this->getPayid();
        if ($this->hasPayidsub()) {
            $transId .= '/' . $this->getPayidsub();
        }

        return $transId;
    }
}