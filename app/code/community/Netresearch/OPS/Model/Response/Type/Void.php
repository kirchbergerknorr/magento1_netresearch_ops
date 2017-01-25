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
 * Void.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Model_Response_Type_Void extends Netresearch_OPS_Model_Response_Type_Abstract
{
    /**
     * Handles the specific actions for the concrete payment status
     */
    protected function _handleResponse()
    {
        if (!Netresearch_OPS_Model_Status::isVoid($this->getStatus())) {
            Mage::throwException(Mage::helper('ops')->__('%s is not a void status!', $this->getStatus()));
        }

        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $this->getMethodInstance()->getInfoInstance();
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();
        if (Netresearch_OPS_Model_Status::isFinal($this->getStatus())) {
            if ($this->getShouldRegisterFeedback()) {

                $payment->setMessage(
                    Mage::helper('ops')->__('Received Ingenico ePayments status %s. Order cancelled.', $this->getStatus())
                );
                $payment->registerVoidNotification($this->getAmount());

                // payment void does not cancel the order, but sets it to processing.
                // We therefore need to cancel the order ourselves.
                $order->registerCancellation($this->getFinalStatusComment(), true);
            } else {
                $this->addFinalStatusComment();
            }
        } else {
            $payment->setMessage($this->getIntermediateStatusComment());
        }

        $order->save();
    }
}