<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG
 *              (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Controller_Adminhtml_KwixoshippingControllerTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{

    public function setUp()
    {
        parent::setUp();
        $fakeUser = $this->getModelMock('admin/user', array('getId', 'getRole'));
        $fakeUser->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $sessionMock = $this->getModelMock(
            'admin/session', array('getUser', 'init', 'save', 'isAllowed')
        );
        $sessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($fakeUser));

        $sessionMock->expects($this->any())
                    ->method('isAllowed')
                    ->will($this->returnValue(true));

        $this->replaceByMock('singleton', 'admin/session', $sessionMock);

        $nodePath = "modules/Enterprise_AdminGws/active";
        if (Mage::helper('core/data')->isModuleEnabled('Enterprise_AdminGws')) {
            Mage::getConfig()->setNode($nodePath, 'false', true);
        }


    }


    public function testIndexAction()
    {

        $this->dispatch('adminhtml/kwixoshipping/index', array());
        $this->assertRequestRoute('adminhtml/kwixoshipping/index');
        $this->assertLayoutBlockCreated('kwixoshipping');
    }

    public function testSaveAction()
    {
        $this->dispatch('adminhtml/kwixoshipping/save', array());
        $this->assertRequestRoute('adminhtml/kwixoshipping/save');
        $this->assertRedirectTo('adminhtml/kwixoshipping/index');
    }

    public function testSaveActionWithPost()
    {
        $postData = array(
            'form_key' => '1234'
        );
        $this->getRequest()->setPost($postData);
        $this->getRequest()->setMethod('POST');
        $this->dispatch('adminhtml/kwixoshipping/save', array());
        $this->assertRequestRoute('adminhtml/kwixoshipping/save');
        $this->assertRedirectTo('adminhtml/kwixoshipping/index');
    }

    /**
     * @loadFixture shipping_settings.yaml
     */
    public function testSaveActionWithRealPostData()
    {
        $postData = array(
            'form_key' => '1234',
            'flatrate' => array(
                'kwixo_shipping_type' => 2,
                'kwixo_shipping_speed' => 2,
                'kwixo_shipping_details' => 'foo'
            ),
            'foobar' => 'barfoo'
        );
        $this->getRequest()->setPost($postData);
        $this->getRequest()->setMethod('POST');
        $this->dispatch('adminhtml/kwixoshipping/save', array());
        $this->assertRequestRoute('adminhtml/kwixoshipping/save');
        $this->assertRedirectTo('adminhtml/kwixoshipping/index');
        $kwixoModel = Mage::getModel('ops/kwixo_shipping_setting')->load(1);

        // assure that saving the data worked properly

        $this->assertEquals('flatrate', $kwixoModel->getShippingCode());
        $this->assertEquals(2, $kwixoModel->getKwixoShippingType());
        $this->assertEquals(2, $kwixoModel->getKwixoShippingSpeed());
        $this->assertEquals('foo', $kwixoModel->getKwixoShippingDetails());
    }


    /**
     * @loadFixture shipping_settings.yaml
     */
    public function testSaveActionWithErrorneousPostData()
    {
        $postData = array(
            'form_key' => '1234',
            'flatrate' => array(
                'kwixo_shipping_type' => -1,
                'kwixo_shipping_speed' => 'abc',
                'kwixo_shipping_details' => 'foo'
            ),
            'foobar' => 'barfoo'
        );
        $this->getRequest()->setPost($postData);
        $this->getRequest()->setMethod('POST');
        $this->dispatch('adminhtml/kwixoshipping/save', array());
        $this->assertRequestRoute('adminhtml/kwixoshipping/save');
        $this->assertRedirectTo('adminhtml/kwixoshipping/index');
        $kwixoModel = Mage::getModel('ops/kwixo_shipping_setting')->load(1);

        // assure that saving the data worked properly

        $this->assertEquals('flatrate', $kwixoModel->getShippingCode());
        $this->assertNotEquals(-1, $kwixoModel->getKwixoShippingType());
        $this->assertNotEquals('abc', $kwixoModel->getKwixoShippingSpeed());
        $this->assertNotEquals('foo', $kwixoModel->getKwixoShippingDetails());
    }

} 