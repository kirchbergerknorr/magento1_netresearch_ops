<?php

/**
 * Flex.php
 *
 * @author    paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */
class Netresearch_OPS_Model_Payment_Flex extends Netresearch_OPS_Model_Payment_Abstract
{

    const CODE = 'ops_flex';

    const INFO_KEY_TITLE = 'flex_title';
    const INFO_KEY_PM = 'flex_pm';
    const INFO_KEY_BRAND = 'flex_brand';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_flex';

    protected $_formBlockType = 'ops/form_flex';

    /** payment code */
    protected $_code = self::CODE;

    protected $_needsCartDataForRequest = true;

    public function getOpsCode($payment = null)
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::INFO_KEY_PM);
    }

    public function getOpsBrand($payment = null)
    {
        return $this->getInfoInstance()->getAdditionalInformation(self::INFO_KEY_BRAND);
    }
}