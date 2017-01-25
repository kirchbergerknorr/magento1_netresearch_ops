<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of KwixoCreditTest
 *
 * @author Sebastian Ertner
 */
class Netresearch_OPS_Test_Model_Payment_KwixoCreditTest extends EcomDev_PHPUnit_Test_Case_Config
{
    /** @var  Netresearch_OPS_Model_Payment_KwixoCredit $kwixoCreditModel */
    private $kwixoCreditModel;
    
    private $store;


    public function setUp()
    {
        parent::setup();
        $this->kwixoCreditModel = Mage::getModel('ops/payment_KwixoCredit');
        $this->kwixoCreditModel->setInfoInstance(Mage::getModel('payment/info'));
        $this->store = Mage::app()->getStore(0)->load(0);
    }


    public function testGetOpsCode()
    {
        $this->assertEquals('KWIXO_CREDIT', $this->kwixoCreditModel->getOpsCode());
    }
    
    public function testGetCode()
    {
        $this->assertEquals('ops_kwixoCredit', $this->kwixoCreditModel->getCode());
    }
    
    
    public function testGetDeliveryDate()
    {
        $this->setUp();
        $dateNow = date("Y-m-d");
        $path = 'payment/ops_kwixoCredit/delivery_date';
        $this->store->setConfig($path, "0");
        $this->assertEquals($dateNow, $this->kwixoCreditModel->getEstimatedDeliveryDate('ops_kwixoCredit'));
        $dateNowPlusFiveDays = strtotime($dateNow . "+ 5 days");
        $this->store->setConfig($path, "5");
        $this->assertEquals(
            date("Y-m-d", $dateNowPlusFiveDays),
            $this->kwixoCreditModel->getEstimatedDeliveryDate('ops_kwixoCredit')
        );
    }

    public function testGetFormBlockType()
    {
        $this->assertEquals('ops/form_kwixo_credit', $this->kwixoCreditModel->getFormBlockType());
    }
}