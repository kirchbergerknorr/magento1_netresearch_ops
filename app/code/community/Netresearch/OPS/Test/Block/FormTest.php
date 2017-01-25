<?php

class Netresearch_OPS_Test_Block_FormTest
    extends EcomDev_PHPUnit_Test_Case
{
    private $_block;

    public function setUp()
    {
        parent::setup();
        $this->_block = Mage::app()->getLayout()->getBlockSingleton('ops/form');
    }

    public function testIsUserRegistering()
    {
        $dataHelperMock = $this->getHelperMock('ops/data', array('checkIfUserIsRegistering'));
        $dataHelperMock->expects($this->any())
            ->method('checkIfUserIsRegistering')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'ops/data', $dataHelperMock);
        
        $block = new Netresearch_OPS_Block_Form();
        $this->assertFalse($block->isUserRegistering());
    }

    public function testIsUserNotRegistering()
    {
        $dataHelperMock = $this->getHelperMock('ops/data', array('checkIfUserIsNotRegistering'));
        $dataHelperMock->expects($this->any())
            ->method('checkIfUserIsNotRegistering')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'ops/data', $dataHelperMock);
        
        $block = new Netresearch_OPS_Block_Form();
        $this->assertFalse($block->isUserNotRegistering());
    }

    public function testGetPmLogo()
    {
        $this->assertEquals(null, $this->_block->getPmLogo());
    }

    public function getMethodLabelAfterHtmlSuccess()
    {
        $method = new Varien_Object();
        $method->setData('code', 'ops_cc');

        $blockMock = $this->getBlockMock('ops/form', array('getMethod'));

        $blockMock->expects($this->any())
                  ->method('getMethod')
                  ->will($this->returnValue($method));
        $this->replaceByMock('block', 'ops/form', $blockMock);

        $formBlock = Mage::app()->getLayout()->createBlock('ops/form');

        $result = $formBlock->getMethodLabelAfterHtml();

        $this->assertContains('cc.jpg', $result);
        $this->assertContains(' left', $result);

        $result = $formBlock->getMethodLabelAfterHtml();

        $this->assertContains('store_one', $result);
    }

    public function getMethodLabelAfterHtmlHidden()
    {
        $method = new Varien_Object();
        $method->setData('code', 'ops_dc');

        $blockMock = $this->getBlockMock('ops/form', array('getMethod'));

        $blockMock->expects($this->any())
                  ->method('getMethod')
                  ->will($this->returnValue($method));
        $this->replaceByMock('block', 'ops/form', $blockMock);

        $formBlock = Mage::app()->getLayout()->createBlock('ops/form');

        $result = $formBlock->getMethodLabelAfterHtml();

        $this->assertEmpty($result);
    }

    public function getMethodLabelAfterHtmlFail()
    {
        $method = new Varien_Object();
        $method->setData('code', 'ops_iDEAL');

        $blockMock = $this->getBlockMock('ops/form', array('getMethod'));

        $blockMock->expects($this->any())
                  ->method('getMethod')
                  ->will($this->returnValue($method));
        $this->replaceByMock('block', 'ops/form', $blockMock);

        $formBlock = Mage::app()->getLayout()->createBlock('ops/form');

        $result = $formBlock->getMethodLabelAfterHtml();

        $this->assertContains('ops_iDEAL.jpg', $result);
        $this->assertContains('skin/frontend', $result);
    }

}
