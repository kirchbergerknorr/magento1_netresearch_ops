<?php
/**
 * Created by JetBrains PhpStorm.
 * User: michael
 * Date: 07.05.13
 * Time: 16:55
 * To change this template use File | Settings | File Templates.
 */

class Netresearch_OPS_Test_Helper_ApiTest extends EcomDev_PHPUnit_Test_Case
{

    public function testGetRedirectRouteFromStatus()
    {
        $helper = Mage::helper('ops/api');
        $configModel = Mage::getModel('ops/config');
        $successRoute = $configModel->getAcceptRedirectRoute();
        $this->assertEquals(
            $successRoute, $helper->getRedirectRouteFromStatus(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT
            )
        );

        $cancelRoute = $configModel->getCancelRedirectRoute();
        $this->assertEquals(
            $cancelRoute, $helper->getRedirectRouteFromStatus(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_CANCEL
            )
        );

        $declineRoute = $configModel->getDeclineRedirectRoute();
        $this->assertEquals(
            $declineRoute, $helper->getRedirectRouteFromStatus(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_DECLINE
            )
        );

        $exceptionRoute = $configModel->getExceptionRedirectRoute();
        $this->assertEquals(
            $exceptionRoute, $helper->getRedirectRouteFromStatus(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_EXCEPTION
            )
        );

        try {
            $helper->getRedirectRouteFromStatus('');
        } catch (Exception $e) {
            $this->assertEquals('invalid status provided', $e->getMessage());
        }
    }

}