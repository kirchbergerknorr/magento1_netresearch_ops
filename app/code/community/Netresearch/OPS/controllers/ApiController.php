<?php

/**
 * Netresearch_OPS_ApiController
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_ApiController extends Netresearch_OPS_Controller_Abstract
{
    /**
     * Order instance
     */
    protected $_order;

    /*
     * Predispatch to check the validation of the request from OPS
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->_validateOPSData()) {
            $this->getResponse()->setHttpResponseCode(422);
            $this->setFlag('', self::FLAG_NO_DISPATCH, 1);

            return;
        }
    }

    /**
     * Action to control postback data from ops
     *
     */
    public function postBackAction()
    {
        $params = $this->getRequest()->getParams();

        try {
            $status = $this->getPaymentHelper()->applyStateForOrder(
                $this->_getOrder(),
                $params
            );
            $redirectRoute = Mage::helper('ops/api')
                ->getRedirectRouteFromStatus($status);

            $this->_redirect(
                $redirectRoute, array(
                    '_store' => $this->_getOrder()->getStoreId(),
                    '_query' => $params,
                    '_nosid' => true
                )
            );
        } catch (Exception $e) {
            Mage::helper('ops')->log(
                "Run into exception '{$e->getMessage()}' in postBackAction"
            );
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * Action to control postback data from ops
     *
     */
    public function directLinkPostBackAction()
    {

        $params = $this->getRequest()->getParams();

        try {
            if (Mage::helper('ops/subscription')->isSubscriptionFeedback($params)) {
                $this->getSubscriptionManager()->processSubscriptionFeedback($params);
            } else {
                $this->getDirectlinkHelper()->processFeedback(
                    $this->_getOrder(),
                    $params
                );
            }
        } catch (Exception $e) {
            Mage::helper('ops')->log(
                "Run into exception '{$e->getMessage()}' in directLinkPostBackAction"
            );
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}
