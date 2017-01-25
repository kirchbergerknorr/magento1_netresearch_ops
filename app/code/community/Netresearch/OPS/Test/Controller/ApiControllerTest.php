<?php
class Netresearch_OPS_Test_Controller_ApiControllerTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{

    private $oldSeoValue = null;

    private function setUpHelperMock($returnStatus, $setStoreId = true)
    {
        $paymentHelperMock = $this->getHelperMock(
            'ops/payment', array('applyStateForOrder', 'shaCryptValidation')
        );
        $paymentHelperMock->expects($this->any())
            ->method('applyStateForOrder')
            ->will($this->returnValue($returnStatus));

        $paymentHelperMock->expects($this->any())
            ->method('shaCryptValidation')
            ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);

        $fakePayment = new Varien_Object();
        $fakePayment->setMethodInstance(Mage::getModel('ops/payment_cc'));

        $fakeOrder = new Varien_Object();
        $fakeOrder->setPayment($fakePayment);
        $fakeOrder->setId(1);
        if ($setStoreId) {
            $fakeOrder->setStoreId(1);
        }
        $orderHelperMock = $this->getHelperMock('ops/order', array('getOrder'));
        $orderHelperMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($fakeOrder));
        $this->replaceByMock('helper', 'ops/order', $orderHelperMock);
    }

    private function getRequestParams()
    {
        return array(
            'orderID' => 1,
            'SHASIGN' => '12344',
        );
    }

    public function testRedirectToSuccessRoute()
    {
        $this->setUpHelperMock(
            Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT
        );
        $modelMock = $this->getModelMock('core/store', array('getId'));
        $modelMock->expects($this->any())
            ->method('getId()')
            ->will($this->returnValue(null));
        $this->replaceByMock('model', 'core/store', $modelMock);
        $this->dispatch('ops/api/postBack', $this->getRequestParams());
        $this->assertRedirectTo(
            Mage::getModel('ops/config')->getAcceptRedirectRoute(),
            array('_query' => $this->getRequestParams(), '_store' => 1)
        );

    }

    public function testRedirectToSuccessRouteWithOrderId()
    {
        $this->setUpHelperMock(
            Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT
        );
        $modelMock = $this->getModelMock('core/store', array('getId'));
        $modelMock->expects($this->any())
            ->method('getId()')
            ->will($this->returnValue(null));
        $requestParams = $this->getRequestParams();
        $requestParams['orderID'] = '#1000001';
        $this->dispatch('ops/api/postBack', $requestParams);
        $this->assertRedirectTo(
            Mage::getModel('ops/config')->getAcceptRedirectRoute(),
            array('_query' => $requestParams, '_store' => 1)
        );

    }

    public function testRedirectToCancelRoute()
    {
        $this->setUpHelperMock(
            Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_CANCEL
        );
        $modelMock = $this->getModelMock('core/store', array('getId'));
        $modelMock->expects($this->any())
            ->method('getId()')
            ->will($this->returnValue(null));
        $this->dispatch('ops/api/postBack', $this->getRequestParams());
        $this->assertRedirectTo(
            Mage::getModel('ops/config')->getCancelRedirectRoute(),
            array('_query' => $this->getRequestParams(), '_store' => 1)
        );
    }

    public function testRedirectToDeclineRoute()
    {
        $this->setUpHelperMock(
            Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_DECLINE
        );
        $modelMock = $this->getModelMock('core/store', array('getId'));
        $modelMock->expects($this->any())
            ->method('getId()')
            ->will($this->returnValue(null));
        $this->dispatch('ops/api/postBack', $this->getRequestParams());
        $this->assertRedirectTo(
            Mage::getModel('ops/config')->getDeclineRedirectRoute(),
            array('_query' => $this->getRequestParams(), '_store' => 1)
        );
    }

    public function testRedirectToExceptionRoute()
    {
        $this->setUpHelperMock(
            Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_EXCEPTION
        );
        $modelMock = $this->getModelMock('core/store', array('getId'));
        $modelMock->expects($this->any())
            ->method('getId()')
            ->will($this->returnValue(null));
        $this->dispatch('ops/api/postBack', $this->getRequestParams());
        $this->assertRedirectTo(
            Mage::getModel('ops/config')->getExceptionRedirectRoute(),
            array('_query' => $this->getRequestParams(), '_store' => 1)
        );
    }

    public function testExceptionReturnsStatus500()
    {
        $this->setUpHelperMock(
            'INVALID_STATUS'
        );
        $modelMock = $this->getModelMock('core/store', array('getId'));
        $modelMock->expects($this->any())
            ->method('getId()')
            ->will($this->returnValue(null));
        $this->dispatch('ops/api/postBack', $this->getRequestParams());
        $this->assertResponseHttpCode(500);
        $this->getResponse();
    }


    public function testRedirectIfStoreIdDoesNotMatch()
    {
        $this->setUpHelperMock(
            Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT,
            false
        );
        $modelMock = $this->getModelMock('core/store', array('getId'));
        $modelMock->expects($this->any())
            ->method('getId()')
            ->will($this->returnValue(9999));
        $this->replaceByMock('model', 'core/store', $modelMock);
        $this->dispatch('ops/api/postBack', $this->getRequestParams());
        $this->assertRedirectTo(
            'ops/payment/accept', array('_query' => $this->getRequestParams(), '_store' => 1)
        );


    }

}