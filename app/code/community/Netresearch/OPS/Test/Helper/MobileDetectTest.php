<?php

require_once Mage::getBaseDir('lib')  .DS.  'MobileDetect' .DS. 'Mobile_Detect.php';

class Netresearch_OPS_Test_Helper_MobileDetectTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Netresearch_OPS_Helper_MobileDetect */
    protected $helper;

    protected $detectorMock;

    public function setUp()
    {
        parent::setup();
        $this->detectorMock = $this->getMockBuilder('Mobile_Detect')
            ->setMethods(array('isMobile', 'isTablet'))
            ->getMock();
        $this->helper = Mage::helper('ops/mobileDetect');
        $this->helper->setDetector($this->detectorMock);
    }

    /**
     * @test
     */
    public function testGetDeviceTypeMobile()
    {
        $this->detectorMock
            ->expects($this->once())
            ->method('isMobile')
            ->willReturn(true);

        $this->detectorMock
            ->expects($this->once())
            ->method('isTablet')
            ->willReturn(false);

        $this->assertEquals(Netresearch_OPS_Helper_MobileDetect::DEVICE_TYPE_MOBILE, $this->helper->getDeviceType());
    }

    /**
     * @test
     */
    public function testGetDeviceTypeTablet()
    {
        $this->detectorMock
            ->expects($this->once())
            ->method('isMobile')
            ->willReturn(false);

        $this->detectorMock
            ->expects($this->once())
            ->method('isTablet')
            ->willReturn(true);

        $this->assertEquals(Netresearch_OPS_Helper_MobileDetect::DEVICE_TYPE_TABLET, $this->helper->getDeviceType());
    }

    /**
     * @test
     */
    public function testGetDeviceTypeComputer()
    {
        $this->detectorMock
            ->expects($this->once())
            ->method('isMobile')
            ->willReturn(false);

        $this->detectorMock
            ->expects($this->once())
            ->method('isTablet')
            ->willReturn(false);

        $this->assertEquals(Netresearch_OPS_Helper_MobileDetect::DEVICE_TYPE_COMPUTER, $this->helper->getDeviceType());
    }
}


