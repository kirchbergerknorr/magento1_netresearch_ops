<?php
/**
 * Netresearch_OPS_Model_Payment_Backend_OpsId
 * 
 * @package   OPS
 * @copyright 2012 Netresearch App Factory AG <http://www.netresearch.de>
 * @author    Thomas Birke <thomas.birke@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_Backend_OpsId
    extends Mage_Payment_Model_Method_Abstract
{
    /* allow usage in Magento backend */
    protected $_canUseInternal = true;

    /* deny usage in Magento frontend */
    protected $_canUseCheckout = false;

    protected $_canBackendDirectCapture = true;

    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;

    /** payment code */
    protected $_code = 'ops_opsid';

    protected $_formBlockType = 'ops/form_opsId';
    
    protected $_infoBlockType = 'ops/info_opsId';
}

