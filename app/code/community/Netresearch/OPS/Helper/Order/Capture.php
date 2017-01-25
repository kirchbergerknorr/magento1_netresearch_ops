<?php

/**
 * Netresearch_OPS_Helper_Order_Capture
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Order_Capture extends Netresearch_OPS_Helper_Order_Abstract
{
    protected function getFullOperationCode()
    {
        return Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_FULL;
    }

    protected function getPartialOperationCode()
    {
        return Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_PARTIAL;
    }

    protected function getPreviouslyProcessedAmount($payment)
    {
        return $payment->getBaseAmountPaidOnline();
    }


    /**
     * Prepare capture informations
     *
     * @param Mage_Sales_Order_Payment $payment
     * @param float $amount
     * @return array
     */
    public function prepareOperation($payment, $amount)
    {
        $params = Mage::app()->getRequest()->getParams();
        if (array_key_exists('invoice', $params)) {
            $arrInfo           = $params['invoice'];
            $arrInfo['amount'] = $amount;
        }
        $arrInfo['type']      = $this->determineType($payment, $amount);
        $arrInfo['operation'] = $this->determineOperationCode($payment, $amount);

        return $arrInfo;
    }

    /**
     * Prepare shipment
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice New invoice
     * @param array $additionalData Array containing additional transaction data
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _prepareShipment($invoice, $additionalData)
    {
        $savedQtys = $additionalData['items'];
        $shipment  = Mage::getModel('sales/service_order', $invoice->getOrder())
                         ->prepareShipment($savedQtys);
        if (!$shipment->getTotalQty()) {
            return false;
        }

        $shipment->register();
        if (array_key_exists('tracking', $additionalData)
            && $additionalData['tracking']
        ) {
            foreach ($additionalData['tracking'] as $data) {
                $track = Mage::getModel('sales/order_shipment_track')
                             ->addData($data);
                $shipment->addTrack($track);
            }
        }

        return $shipment;
    }
}
