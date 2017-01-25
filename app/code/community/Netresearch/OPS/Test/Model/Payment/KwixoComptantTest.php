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
class Netresearch_OPS_Test_Model_Payment_KwixoComptantTest extends EcomDev_PHPUnit_Test_Case_Config
{
    /** @var Netresearch_OPS_Model_Payment_KwixoComptant kwixoComptantModel */
    private $kwixoComptantModel;
    
    private $store;


    public function setUp()
    {
        parent::setup();
        $payment = Mage::getModel('payment/info');
        $this->kwixoComptantModel = Mage::getModel('ops/payment_kwixoComptant');
        $this->kwixoComptantModel->setInfoInstance($payment);
        $this->store = Mage::app()->getStore(0)->load(0);
    }

    public function testGetOpsCode()
    {
        $this->assertEquals('KWIXO_STANDARD', $this->kwixoComptantModel->getOpsCode());
    }
    
    public function testGetCode()
    {
        $this->assertEquals('ops_kwixoComptant', $this->kwixoComptantModel->getCode());
    }

    public function testGetDeliveryDate()
    {
       $this->setUp();
       $dateNow = date("Y-m-d");
       $path = 'payment/ops_kwixoComptant/delivery_date';
       $this->store->setConfig($path, "0");
       $this->assertEquals($dateNow, $this->kwixoComptantModel->getEstimatedDeliveryDate('ops_kwixoComptant'));
       $dateNowPlusFiveDays = strtotime($dateNow ."+ 5 days");
       $this->store->setConfig($path, "5");
       $this->assertEquals(date("Y-m-d", $dateNowPlusFiveDays), $this->kwixoComptantModel->getEstimatedDeliveryDate('ops_kwixoComptant'));
    }

    public function testGetFormBlockType()
    {
        $this->assertEquals('ops/form_kwixo_comptant', $this->kwixoComptantModel->getFormBlockType());
    }
    
}