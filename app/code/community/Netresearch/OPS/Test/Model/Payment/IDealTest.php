<?php
/**
 * @author      Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Model_Payment_IDealTest extends EcomDev_PHPUnit_Test_Case
{


    public function testGetIdealIssuers()
    {
        $issuers = Mage::getModel('ops/payment_iDeal')->getIDealIssuers();
        $this->assertTrue(is_array($issuers));
        $this->assertTrue(array_key_exists('ABNANL2A', $issuers));
        $this->assertEquals('ABN AMRO', $issuers['ABNANL2A']);

        $this->assertTrue(array_key_exists('RABONL2U', $issuers));
        $this->assertEquals('Rabobank', $issuers['RABONL2U']);

        $this->assertTrue(array_key_exists('INGBNL2A', $issuers));
        $this->assertEquals('ING', $issuers['INGBNL2A']);

        $this->assertTrue(array_key_exists('SNSBNL2A', $issuers));
        $this->assertEquals('SNS Bank', $issuers['SNSBNL2A']);

        $this->assertTrue(array_key_exists('RBRBNL21', $issuers));
        $this->assertEquals('Regio Bank', $issuers['RBRBNL21']);

        $this->assertTrue(array_key_exists('ASNBNL21', $issuers));
        $this->assertEquals('ASN Bank', $issuers['ASNBNL21']);

        $this->assertTrue(array_key_exists('TRIONL2U', $issuers));
        $this->assertEquals('Triodos Bank', $issuers['TRIONL2U']);

        $this->assertTrue(array_key_exists('FVLBNL22', $issuers));
        $this->assertEquals('Van Lanschot Bankiers', $issuers['FVLBNL22']);

        $this->assertTrue(array_key_exists('KNABNL2H', $issuers));
        $this->assertEquals('Knab Bank', $issuers['KNABNL2H']);

    }


    public function testAssignData()
    {
        $payment = Mage::getModel('sales/quote_payment')->setMethod('ops_iDeal');
        $quote = Mage::getModel('sales/quote')->setPayment($payment);

        $data = array('iDeal_issuer_id' => 'RBRBNL21');
        $this->assertEquals('iDEAL', $payment->getMethodInstance()->getOpsCode());

        $method = $payment->getMethodInstance()->assignData($data);
        $this->assertInstanceOf('Netresearch_OPS_Model_Payment_IDeal', $method);

        $this->assertEquals('RBRBNL21', $payment->getAdditionalInformation('iDeal_issuer_id'));
    }

    public function testAssignDataWithVarienObject()
    {
        $payment = Mage::getModel('sales/quote_payment');
        $payment->setMethod('ops_iDeal');

        $quote = Mage::getModel('sales/quote');
        $quote->setPayment($payment);

        $data = new Varien_object(array('iDeal_issuer_id' => 'ABNAMRO'));
        $this->assertEquals('iDEAL', $payment->getMethodInstance()->getOpsCode());

        $method = $payment->getMethodInstance()->assignData($data);
        $this->assertInstanceOf('Netresearch_OPS_Model_Payment_IDeal', $method);

        $this->assertEquals('ABNAMRO', $payment->getAdditionalInformation('iDeal_issuer_id'));
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testGetMethodDependentFormFieldsWithIssuerId()
    {
        $order       = Mage::getModel('sales/order')->load(25);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote', 'init', 'save')
        );

        $order->getPayment()->setAdditionalInformation('iDeal_issuer_id', 'ideal_123');

        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);


        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn', 'init', 'save')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);

        $formFields = Mage::getModel('ops/payment_iDeal')
            ->getMethodDependendFormFields($order, array());

        $this->assertTrue(array_key_exists('ISSUERID', $formFields));
        $this->assertEquals('ideal_123', $formFields['ISSUERID']);
    }

    /**
     * @test
     * @loadFixture orders.yaml
     */
    public function testGetMethodDependentFormFieldsWithoutIssuerId()
    {
        $order       = Mage::getModel('sales/order')->load(25);
        $sessionMock = $this->getModelMock(
            'checkout/session', array('getQuote', 'init', 'save')
        );

        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($order));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);


        $sessionMock = $this->getModelMock(
            'customer/session', array('isLoggedIn', 'init', 'save')
        );
        $sessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(1));
        $this->replaceByMock('model', 'customer/session', $sessionMock);

        $formFields = Mage::getModel('ops/payment_iDeal')
            ->getMethodDependendFormFields($order);

        $this->assertFalse(array_key_exists('ISSUERID', $formFields));
    }

} 