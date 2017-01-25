<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Model_Payment_DirectDebitTest
    extends EcomDev_PHPUnit_Test_Case
{

    public function testGetOrderPlaceRedirectUrl()
    {
        $this->assertFalse(
            Mage::getModel('ops/payment_directDebit')->getOrderPlaceRedirectUrl(
            )
        );
    }

} 