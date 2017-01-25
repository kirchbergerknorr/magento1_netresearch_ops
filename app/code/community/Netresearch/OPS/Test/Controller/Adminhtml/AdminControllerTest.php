<?php

/**
 * AdminControllerTest.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */

class Netresearch_OPS_Test_Controller_Adminhtml_AdminControllerTest extends EcomDev_PHPUnit_Test_Case_Controller
{

    public function setUp()
    {
        parent::setUp();

        $nodePath = "modules/Enterprise_AdminGws/active";
        if (Mage::helper('core/data')->isModuleEnabled('Enterprise_AdminGws')) {
            Mage::getConfig()->setNode($nodePath, 'false', true);
        }

        $this->fakeAdminUser();
    }

    public function testResendInfoActionWillSucceed()
    {
        $mailFeatureMock = $this->getModelMock('ops/payment_features_paymentEmail', array('resendPaymentInfo', 'isAvailableForOrder'));
        $mailFeatureMock->expects($this->any())
            ->method('isAvailableForOrder')
            ->will($this->returnValue(true));
        $mailFeatureMock->expects($this->once())
            ->method('resendPaymentInfo')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/payment_features_paymentEmail', $mailFeatureMock);

        $orderMock = $this->getModelMock('sales/order', array('load'));
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($orderMock));
        $this->replaceByMock('model', 'sales/order', $orderMock);

        $this->dispatch('adminhtml/admin/resendInfo', array('order_id' => 1));
    }

    public function testResendInfoActionWillFail()
    {
        $mailFeatureMock = $this->getModelMock('ops/payment_features_paymentEmail', array('resendPaymentInfo', 'isAvailableForOrder'));
        $mailFeatureMock->expects($this->any())
            ->method('isAvailableForOrder')
            ->will($this->returnValue(true));
        $mailFeatureMock->expects($this->once())
            ->method('resendPaymentInfo')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'ops/payment_features_paymentEmail', $mailFeatureMock);

        $orderMock = $this->getModelMock('sales/order', array('load'));
        $orderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($orderMock));
        $this->replaceByMock('model', 'sales/order', $orderMock);

        $this->dispatch('adminhtml/admin/resendInfo', array('order_id' => 1));
    }

    protected function fakeAdminUser()
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
    }

}
