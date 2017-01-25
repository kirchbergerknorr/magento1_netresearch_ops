<?php

/**
 * Netresearch_OPS_Model_Payment_IDeal
 *
 * @package
 * @copyright 2016 Netresearch
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_Masterpass
    extends Netresearch_OPS_Model_Payment_Abstract
{
    protected $pm = 'MasterPass';
    protected $brand = 'MasterPass';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_Masterpass';

}

