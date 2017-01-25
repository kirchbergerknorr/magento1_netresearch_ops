<?php
/**
 * Netresearch_OPS_Model_Payment_IngHomePay
 * 
 * @package   
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_IngHomePay
    extends Netresearch_OPS_Model_Payment_Abstract
{
    protected $pm = 'ING HomePay';
    protected $brand = 'ING HomePay';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_ingHomePay';

}

