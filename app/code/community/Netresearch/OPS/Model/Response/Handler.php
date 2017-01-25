<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Handler.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Model_Response_Handler
{

    /**
     * @param array                                  $responseArray
     * @param Netresearch_OPS_Model_Payment_Abstract $paymentMethod
     * @param bool                                   $shouldRegisterFeedback
     *                                               determines if the Mage_Sales_Model_Order_Payments register*Feedback
     *                                               functions get called, defaults to true
     *
     */
    public function processResponse(
        $responseArray, Netresearch_OPS_Model_Payment_Abstract $paymentMethod, $shouldRegisterFeedback = true
    )
    {
        $responseArray = array_change_key_case($responseArray, CASE_LOWER);
        $this->getTypeHandler($responseArray['status'])
            ->handleResponse($responseArray, $paymentMethod, $shouldRegisterFeedback);
    }

    /**
     * @param $status
     *
     * @return Netresearch_OPS_Model_Response_TypeInterface
     */
    protected function getTypeHandler($status)
    {
        $type = null;

        if (Netresearch_OPS_Model_Status::isCapture($status)) {
            $type = Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_TRANSACTION_TYPE;
        } elseif (Netresearch_OPS_Model_Status::isRefund($status)) {
            $type = Netresearch_OPS_Model_Payment_Abstract::OPS_REFUND_TRANSACTION_TYPE;
        } elseif (Netresearch_OPS_Model_Status::isVoid($status)) {
            $type = Netresearch_OPS_Model_Payment_Abstract::OPS_VOID_TRANSACTION_TYPE;
        } elseif (Netresearch_OPS_Model_Status::isAuthorize($status)) {
            $type = Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_TRANSACTION_TYPE;
        } elseif (Netresearch_OPS_Model_Status::isSpecialStatus($status)) {
            $type = 'special';
        } else {
            Mage::throwException(Mage::helper('ops')->__('Can not handle status %s.', $status));
        }

        return Mage::getModel('ops/response_type_' . $type);
    }
}