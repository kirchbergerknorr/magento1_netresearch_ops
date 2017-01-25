<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Block_Adminhtml_Kwixo_Shipping_EditTest
    extends EcomDev_PHPUnit_Test_Case
{

    public function setUp()
    {
        $sessionMock = $this->getModelMock(
            'core/session', array('init', 'save', 'getSessionIdForHost')
        );

        $this->replaceByMock('model', 'core/session', $sessionMock);
    }

    public function testGetFormActionUrl()
    {

        $widgetContainer = new Mage_Adminhtml_Block_Widget_Form_Container();
        $block
                         = new Netresearch_OPS_Block_Adminhtml_Kwixo_Shipping_Edit();
        $this->assertEquals(
            $widgetContainer->getUrl('adminhtml/kwixoshipping/save', array()),
            $block->getFormActionUrl()
        );
    }

    public function testGetShippingMethods()
    {
        $shippingConfigMock = $this->getModelMock(
            'shipping/config', array('getAllCarriers')
        );
        $shippingConfigMock->expects($this->any())
            ->method('getAllCarriers')
            ->will(
                $this->returnValue(
                    array('dhl' => 'dhl', 'hermes' => 'hermes', 'ips' => 'ips')
                )
            );
        $this->replaceByMock('model', 'shipping/config', $shippingConfigMock);

        $block = new Netresearch_OPS_Block_Adminhtml_Kwixo_Shipping_Edit();
        $result = $block->getShippingMethods();
        $this->assertEquals(3, count($result));
        $this->assertEquals($result[0]['code'], 'dhl');
        $this->assertEquals($result[1]['code'], 'hermes');
        $this->assertEquals($result[1]['label'], 'hermes');
        $this->assertEquals($result[2]['code'], 'ips');
        $this->assertEquals($result[2]['label'], 'ips');

        $block->setData(
            'postData', array('dhl' => array('error' => 'sample error'))
        );
        $result = $block->getShippingMethods();
        $this->assertEquals($result[0]['values']['error'], 'sample error');
    }

    public function testGetKwixoShippingTypes()
    {
        $block = new Netresearch_OPS_Block_Adminhtml_Kwixo_Shipping_Edit();
        $expectedResult = Mage::getModel('ops/source_kwixo_shipMethodType')
            ->toOptionArray();
        $this->assertEquals($expectedResult, $block->getKwixoShippingTypes());
    }


    public function testGetFormKey()
    {
        $block = new Netresearch_OPS_Block_Adminhtml_Kwixo_Shipping_Edit();
        $expectedResult = Mage::getSingleton('core/session')->getFormKey();
        $this->assertEquals($expectedResult, $block->getFormKey());
    }

} 