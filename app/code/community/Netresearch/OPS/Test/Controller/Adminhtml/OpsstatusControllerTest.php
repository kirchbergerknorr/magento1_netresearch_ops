<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Controller_Adminhtml_OpsstatusControllerTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{

    public function setUp()
    {
        parent::setUp();

        $nodePath = "modules/Enterprise_AdminGws/active";
        if (Mage::helper('core/data')->isModuleEnabled('Enterprise_AdminGws')) {
            Mage::getConfig()->setNode($nodePath, 'false', true);
        }

    }

    public function testUpdateActionWillRedirect()
    {
        $fakeUser = $this->getModelMock('admin/user', array('getId', 'getRole'));
        $fakeUser->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $adminSessionMock = $this->getModelMock('admin/session', array('isAllowed', 'init', 'save', 'getUser'));
        $adminSessionMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(true));
        $adminSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($fakeUser));
        $this->replaceByMock('model', 'admin/session', $adminSessionMock);

        $statusUpdateMock = $this->getModelMock('ops/status_update', array('updateStatusFor'));
        $this->replaceByMock('model', 'ops/status_update', $statusUpdateMock);

        $this->dispatch('adminhtml/opsstatus/update', array('order_id' => 1));
        $this->assertRedirectTo('adminhtml/sales_order/view', array('order_id' => 1));
    }

    public function testUpdateActionWillNotRedirect()
    {
        $fakeUser = $this->getModelMock('admin/user', array('getId', 'getRole'));
        $fakeUser->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $adminSessionMock = $this->getModelMock('admin/session', array('isAllowed', 'init', 'save', 'getUser'));
        $adminSessionMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(false));
        $adminSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($fakeUser));
        $this->replaceByMock('model', 'admin/session', $adminSessionMock);

        $statusUpdateMock = $this->getModelMock('ops/status_update', array('updateStatusFor'));
        $this->replaceByMock('model', 'ops/status_update', $statusUpdateMock);

        $this->dispatch('adminhtml/opsstatus/update', array('order_id' => 1));
        $this->assertEquals('403 Forbidden', $this->getResponse()->getSentHeader('Http/1.1'));
    }

} 