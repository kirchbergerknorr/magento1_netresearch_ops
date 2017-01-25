<?php
/**
 * @category   OPS
 * @package    Netresearch_OPS
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_OPS_Test_Model_Payment_InterSolveTest
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Model_Payment_InterSolveTest extends EcomDev_PHPUnit_Test_Case_Config
{
    public function testClassExists()
    {
        $this->assertModelAlias('ops/payment_interSolve', 'Netresearch_OPS_Model_Payment_InterSolve');
        $this->assertTrue(Mage::getModel('ops/payment_interSolve') instanceof Netresearch_OPS_Model_Payment_InterSolve);
        $this->assertTrue(Mage::getModel('ops/payment_interSolve') instanceof Netresearch_OPS_Model_Payment_Abstract);
    }

    public function testMethodConfig()
    {
        $this->assertConfigNodeValue('default/payment/ops_interSolve/model', 'ops/payment_interSolve');
    }

    public function testPm()
    {
        $payment = Mage::getModel('payment/info');
        $this->assertEquals('InterSolve', Mage::getModel('ops/payment_interSolve')->getOpsCode($payment));
    }

    public function testBrand()
    {
        $payment = Mage::getModel('sales/quote_payment');
        $payment->setAdditionalInformation('BRAND', 'InterSolve');
        $this->assertEquals('InterSolve', Mage::getModel('ops/payment_interSolve')->getOpsBrand($payment));
    }

    public function testAssignDataWithBrand()
    {
        $payment = Mage::getModel('sales/quote_payment');
        $payment->setMethod('ops_interSolve');

        $quote = Mage::getModel('sales/quote');
        $quote->setPayment($payment);

        $data = array('intersolve_brand' => 'FooBar');
        $this->assertEquals('InterSolve', $payment->getMethodInstance()->getOpsCode());

        $method = $payment->getMethodInstance()->assignData($data);
        $this->assertInstanceOf('Netresearch_OPS_Model_Payment_InterSolve', $method);

        $this->assertEquals('FooBar', $payment->getAdditionalInformation('BRAND'));
    }

    public function testAssignDataWithoutBrand()
    {
        $payment = Mage::getModel('sales/quote_payment');
        $payment->setMethod('ops_interSolve');

        $quote = Mage::getModel('sales/quote');
        $quote->setPayment($payment);

        $this->assertEquals('InterSolve', $payment->getMethodInstance()->getOpsCode());

        /** @var Netresearch_OPS_Model_Payment_InterSolve $method */
        $method = $payment->getMethodInstance();
        $this->assertInstanceOf('Netresearch_OPS_Model_Payment_InterSolve', $method);

        $method->assignData(array());
        $this->assertEquals('InterSolve', $payment->getAdditionalInformation('BRAND'));
    }
}

