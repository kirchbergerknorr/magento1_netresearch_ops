<?php

class Netresearch_OPS_Test_Block_FrauddetectionTest
    extends EcomDev_PHPUnit_Test_Case
{

    private $store;

    public function setUp()
    {
        @session_start();
        parent::setUp();
        $this->store = Mage::app()->getStore(0)->load(0);
        $this->store->resetConfig();
    }

    public function testToHtml()
    {
        $block = Mage::app()->getLayout()->getBlockSingleton('ops/frauddetection');
        $this->assertEquals(null, $block->toHtml());

        $configMock = $this->getModelMock('ops/config', array('getDeviceFingerPrinting'));
        $configMock->expects($this->once())
                   ->method('getDeviceFingerPrinting')
                   ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/config', $configMock);

        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->setMethods(array('getData'))
            ->getMock();
        $sessionMock->expects($this->once())
                     ->method('getData')
                     ->with(Netresearch_OPS_Model_Payment_Abstract::FINGERPRINT_CONSENT_SESSION_KEY)
                     ->will($this->returnValue(true));
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
        // for some reason the html is not rendered in the tests
        $this->assertNotNull($block->toHtml());
    }


    public function testGetTrackingCodeAid()
    {
        $block = Mage::app()->getLayout()->getBlockSingleton('ops/frauddetection');
        $this->assertEquals('10376', $block->getTrackingCodeAid());
    }


    public function testGetTrackingSid()
    {
        $quote = Mage::getModel('sales/quote');
        $quote->setReservedOrderId('123456');
        $quote->getStoreId(0);
        Mage::app(0)->getStore(0)->setConfig(Netresearch_OPS_Model_Config::OPS_PAYMENT_PATH . 'PSPID', 'abc');

        $block = Mage::app()->getLayout()->getBlockSingleton('ops/frauddetection');
        $modelMock = $this->getModelMock('checkout/type_onepage', array('getQuote'));
        $modelMock->expects($this->once())
                  ->method('getQuote')
                  ->will($this->returnValue($quote));
        $this->replaceByMock('singleton', 'checkout/type_onepage', $modelMock);
        $this->assertEquals(md5(Mage::getModel('ops/config')->getPSPID() . '#123456'), $block->getTrackingSid());
    }

}
