<?php
/**
 * Netresearch_OPS_Model_Payment_CashU
 *
 * @package   OPS
 * @copyright 2012 Netresearch App Factory AG <http://www.netresearch.de>
 * @author    Thomas Kappel <thomas.kappel@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_CashU
    extends Netresearch_OPS_Model_Payment_Abstract
{
    protected $pm = 'cashU';
    protected $brand = 'cashU';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_cashU';
}

