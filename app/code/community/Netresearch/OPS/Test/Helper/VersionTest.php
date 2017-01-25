<?php

class Netresearch_OPS_Test_Helper_VersionTest extends EcomDev_PHPUnit_Test_Case
{
    protected $helper;


    public function setUp()
    {
        parent::setup();
        $this->helper = Mage::helper('ops/version');
    }

    public function testGetVersionForEditionEE()
    {
        $helperMethod = $this->getProtectedMethod($this->helper, 'getVersionForEdition');
        $this->assertEquals(
            Netresearch_OPS_Helper_Version::CAN_USE_APPLICABLE_FOR_QUOTE_EE_MINOR,
            $helperMethod->invoke($this->helper, 'Enterprise')
        );
    }

    public function testGetVersionForEditionCE()
    {
        $helperMethod = $this->getProtectedMethod($this->helper, 'getVersionForEdition');
        $this->assertEquals(
            Netresearch_OPS_Helper_Version::CAN_USE_APPLICABLE_FOR_QUOTE_CE_MINOR,
            $helperMethod->invoke($this->helper, 'Community')
        );
    }

    public function testGetVersionForEditionDefaultCE()
    {
        $helperMethod = $this->getProtectedMethod($this->helper, 'getVersionForEdition');
        $this->assertEquals(
            Netresearch_OPS_Helper_Version::CAN_USE_APPLICABLE_FOR_QUOTE_CE_MINOR,
            $helperMethod->invoke($this->helper, null)
        );
    }

    public function testCanUseApplicableForQuoteForEE()
    {
        $helperMock = $this->getHelperMock('ops/version', array('getVersionInfo'));
        $helperMock->expects($this->any())
            ->method('getVersionInfo')
            ->will($this->returnValue(array('minor' => '14')));

        $this->assertTrue($helperMock->canUseApplicableForQuote('Enterprise'));

        $helperMock = $this->getHelperMock('ops/version', array('getVersionInfo'));
        $helperMock->expects($this->any())
            ->method('getVersionInfo')
            ->will($this->returnValue(array('minor' => '13')));

        $this->assertFalse($helperMock->canUseApplicableForQuote('Enterprise'));
    }

    public function testCanUseApplicableForQuoteForCE()
    {
        $helperMock = $this->getHelperMock('ops/version', array('getVersionInfo'));
        $helperMock->expects($this->any())
            ->method('getVersionInfo')
            ->will($this->returnValue(array('minor' => '8')));

        $this->assertTrue($helperMock->canUseApplicableForQuote('Community'));

        $helperMock = $this->getHelperMock('ops/version', array('getVersionInfo'));
        $helperMock->expects($this->any())
            ->method('getVersionInfo')
            ->will($this->returnValue(array('minor' => '7')));

        $this->assertFalse($helperMock->canUseApplicableForQuote('Community'));


    }

    public function testGetVersionInfo()
    {
        $helperMethod = $this->getProtectedMethod($this->helper, 'getVersionInfo');
        $this->assertEquals(Mage::getVersionInfo(), $helperMethod->invoke($this->helper));

    }

    protected function getProtectedMethod($class, $method)
    {
        $reflection_class = new ReflectionClass(get_class($class));
        $method           = $reflection_class->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }
}

