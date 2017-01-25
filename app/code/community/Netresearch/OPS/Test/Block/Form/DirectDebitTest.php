<?php

class Netresearch_OPS_Test_Block_Form_DirectDebitTest
    extends EcomDev_PHPUnit_Test_Case
{
    public function testTemplate()
    {
        //Frontend case
        $modelMock = $this->getModelMock(
            'ops/config', array('isFrontendEnvironment')
        );
        $modelMock->expects($this->any())
            ->method('isFrontendEnvironment')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/config', $modelMock);
        $ccForm = new Netresearch_OPS_Block_Form_DirectDebit();
        $this->assertEquals(
            Netresearch_OPS_Block_Form_DirectDebit::TEMPLATE,
            $ccForm->getTemplate()
        );

        //Backend case
        $modelMock = $this->getModelMock(
            'ops/config', array('isFrontendEnvironment')
        );
        $modelMock->expects($this->any())
            ->method('isFrontendEnvironment')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'ops/config', $modelMock);
        $ccForm = new Netresearch_OPS_Block_Form_DirectDebit();
        $this->assertEquals(
            Netresearch_OPS_Block_Form_DirectDebit::TEMPLATE,
            $ccForm->getTemplate()
        );
    }

    public function testDirectDebitCountryIds()
    {
        $fakeConfig = new Varien_Object();
        $fakeConfig->setDirectDebitCountryIds("AT, DE, NL");
        $blockMock = $this->getBlockMock(
            'ops/form_directDebit', array('getconfig')
        );
        $blockMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($fakeConfig));
        $this->assertEquals(
            explode(',', 'AT, DE, NL'), $blockMock->getDirectDebitCountryIds()
        );
    }
}
