<?php
/**
 * Netresearch_OPS_Helper_Api
 *
 * @package
 * @copyright 2013 Netresearch
 * @author    Michael Lühr <michael.luehr@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Api extends Mage_Core_Helper_Abstract
{

    protected $configModel = null;


    /**
     * @param $status - one of the fedd back status
     *
     * @throws Mage_Core_Exception - in case the status is not known
     * @return string - the route for redirect
     */
    public function getRedirectRouteFromStatus($status)
    {
        $route = null;
        $configModel = $this->getConfigModel();
        if ($this->isAcceptStatus($status)) {
            $route = $configModel->getAcceptRedirectRoute();
        }
        if ($this->isCancelStatus($status)) {
            $route = $configModel->getCancelRedirectRoute();
        }
        if ($this->isDeclineStatus($status)) {
            $route = $configModel->getDeclineRedirectRoute();
        }
        if ($this->isExceptionStatus($status)) {
            $route = $configModel->getExceptionRedirectRoute();
        }

        // in case none of the cases above match then the status is not known
        if (null === $route) {
            Mage::throwException('invalid status provided');
        }

        return $route;
    }


    /**
     * config getter
     *
     * @return Netresearch_OPS_Model_Config
     */
    protected function getConfigModel()
    {
        if (null === $this->configModel) {
            $this->configModel = Mage::getModel('ops/config');
        }
        return $this->configModel;
    }

    /**
     * determine if the status is known as accepted status
     *
     * @param $status - the status
     *
     * @return bool - true if the status is known as accept status, false otherwise
     */
    protected function isAcceptStatus($status)
    {
        return in_array(
            $status, array(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_ACCEPT
            )
        );
    }

    /**
     * determine if the status is known as canceled status
     *
     * @param $status - the status
     *
     * @return bool - true if the status is known as canceled status, false otherwise
     */
    protected function isCancelStatus($status)
    {
        return in_array(
            $status, array(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_CANCEL
            )
        );
    }

    /**
     * determine if the status is known as declined status
     *
     * @param $status - the status
     *
     * @return bool - true if the status is known as declined status, false otherwise
     */
    protected function isDeclineStatus($status)
    {
        return in_array(
            $status, array(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_DECLINE
            )
        );
    }

    /**
     * determine if the status is known as exception status
     *
     * @param $status - the status
     *
     * @return bool - true if the status is known as exception status, false otherwise
     */
    protected function isExceptionStatus($status)
    {
        return in_array(
            $status, array(
                Netresearch_OPS_Model_Status_Feedback::OPS_ORDER_FEEDBACK_STATUS_EXCEPTION
            )
        );
    }

}