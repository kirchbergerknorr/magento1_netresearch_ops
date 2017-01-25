<?php
class Netresearch_OPS_Test_Model_Payment_DirectEbankingTest extends EcomDev_PHPUnit_Test_Case
{
    public function testAssignData()
    {
        $data = array(
            'directEbanking_brand' => 'Sofort Uberweisung',
        );
        $payment = Mage::getModel('sales/order_payment');
        $infoInstance = new Varien_Object();

        $method = Mage::getModel('ops/payment_directEbanking');
        $method->setInfoInstance(Mage::getModel('sales/quote_payment'));
        $method = $method->assignData($data);
        $this->assertEquals($method->getOpsBrand(), 'DirectEbanking');
        $this->assertEquals($method->getOpsCode(), 'DirectEbanking');
    }

}
