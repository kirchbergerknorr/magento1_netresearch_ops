<?php

/**
 * PayPerMail.php
 *
 * @author    Sebastian Ertner sebastian.ertner@netresearch.de
 * @copyright Copyright (c) 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */
class Netresearch_OPS_Model_Payment_PayPerMail extends Netresearch_OPS_Model_Payment_Abstract
{

    const CODE           = 'ops_payPerMail';
    const INFO_KEY_TITLE = 'paypermail_title';
    const INFO_KEY_PM    = 'paypermail_pm';
    const INFO_KEY_BRAND = 'paypermail_brand';

    protected $_canUseCheckout = false;
    protected $_canUseInternal = true;


    /** info source path */
    protected $_infoBlockType = 'ops/info_payPerMail';

    protected $_formBlockType = 'ops/form_payPerMail';

    /** payment code */
    protected $_code = self::CODE;


    public function getOpsCode($payment = null)
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::INFO_KEY_PM);
    }

    public function getOpsBrand($payment = null)
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::INFO_KEY_BRAND);
    }

}