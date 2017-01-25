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
 * Netresearch_OPS_Model_Payment_ChinaUnionPay
 *
 * @category    Ingenico
 * @package     Netresearch_OPS
 * @author      Sebastian Ertner <sebastian.ertner@netresearch.de>
 */
class Netresearch_OPS_Model_Payment_ChinaUnionPay
    extends Netresearch_OPS_Model_Payment_Abstract
{
    protected $pm = 'PAYDOL_UPOP';
    protected $brand = 'UnionPay';
    const CODE = 'ops_chinaUnionPay';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** disable partial refund */
    protected $_canRefundInvoicePartial = false;

    /**  disable partial capture */
    protected $_canCapturePartial = false;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = self::CODE;

    public function getPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

}

