<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Model_Payment_Features_ZeroAmountAuthTest extends EcomDev_PHPUnit_Test_Case
{
    protected $featureModel = null;

    public function setUp()
    {
        parent::setUp();
        $this->featureModel = Mage::getModel('ops/payment_features_zeroAmountAuth');
    }

    public function testIsCCAndZeroAmountAuthAllowedNoCC()
    {
        $payment = Mage::getModel('ops/payment_iDeal');
        $quote   = Mage::getModel('sales/quote');
        $this->assertFalse($this->featureModel->isCCAndZeroAmountAuthAllowed($payment, $quote));
    }


    public function testIsCCAndZeroAmountAuthAllowedFalse()
    {
        $ccModelMock = $this->getCCMock();
        $quote       = Mage::getModel('sales/quote');
        $this->assertFalse($this->featureModel->isCCAndZeroAmountAuthAllowed($ccModelMock, $quote));
    }


    public function testIsCCAndZeroAmountAuthAllowedTrue()
    {
        $ccModelMock = $this->getCCMock(true);
        $quote       = $this->getModelMock('sales/quote', array('getItemsCount', 'isNominal'));
        $quote->expects($this->once())
            ->method('getItemsCount')
            ->will($this->returnValue(1));
        $quote->expects($this->once())
            ->method('isNominal')
            ->will($this->returnValue(false));
        $this->assertTrue($this->featureModel->isCCAndZeroAmountAuthAllowed($ccModelMock, $quote));
    }

    protected function getCCMock($returnValue = false)
    {
        $ccModelMock = $this->getModelMock('ops/payment_cc', array('isZeroAmountAuthorizationAllowed'));
        $ccModelMock->expects($this->once())
            ->method('isZeroAmountAuthorizationAllowed')
            ->will($this->returnValue($returnValue));

        return $ccModelMock;
    }
}