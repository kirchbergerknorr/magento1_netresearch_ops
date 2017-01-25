<?php
/**
 * Netresearch_OPS_Model_Payment_ApresReception
 * 
 * @package   
 * @copyright 2013 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_KwixoApresReception extends Netresearch_OPS_Model_Payment_Kwixo_Abstract
{
    protected $pm = 'KWIXO_RNP';
    
    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    protected $_formBlockType = 'ops/form_kwixo_apresReception';

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_kwixoApresReception';

}