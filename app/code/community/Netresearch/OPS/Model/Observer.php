<?php
/**
 * @category   OPS
 * @package    Netresearch_OPS
 * @author     André Herrn <andre.herrn@netresearch.de>
 * @copyright  Copyright (c) 2013 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_OPS_Model_Observer
 *
 * @author     André Herrn <andre.herrn@netresearch.de>
 * @copyright  Copyright (c) 2013 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Model_Observer
{

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }

    public function isAdminSession()
    {

        if ($this->getAdminSession()->getUser()) {
            return 0 < $this->getAdminSession()->getUser()->getUserId();
        }

        return false;
    }

    public function getHelper($name = null)
    {
        if (null === $name) {
            return Mage::helper('ops');
        }

        return Mage::helper('ops/' . $name);
    }

    /**
     * @return Netresearch_OPS_Model_Config
     */
    public function getConfig()
    {
        return Mage::getModel('ops/config');
    }

    /**
     * trigger ops payment
     */
    public function checkoutTypeOnepageSaveOrderBefore($observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $code = $quote->getPayment()->getMethodInstance()->getCode();

        try {
            if ('ops_directDebit' == $code
                && Mage::helper('ops/payment')->isInlinePaymentWithQuoteId(
                    $quote->getPayment()
                )
            ) {
                $this->confirmDdPayment($order, $quote );
            } elseif ($quote->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Abstract) {
                $requestParams = $quote->getPayment()->getMethodInstance()->getFormFields($order, array(), false);
                $this->invokeRequestParamValidation($requestParams);
            }
        } catch (Exception $e) {
            $quote->setIsActive(true);
            $this->getOnepage()->getCheckout()->setGotoSection('payment');
            Mage::throwException($e->getMessage());
        }
    }

    public function salesModelServiceQuoteSubmitFailure($observer)
    {
        $quote = $observer->getQuote();
        if (Mage::helper('ops/payment')->isInlinePaymentWithQuoteId($quote->getPayment())) {
            $this->handleFailedCheckout(
                $observer->getQuote(),
                $observer->getOrder()
            );
        }
    }

    protected function getQuoteCurrency($quote)
    {
        if ($quote->hasForcedCurrency()) {
            return $quote->getForcedCurrency()->getCode();
        } else {
            return Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode();
        }
    }

    public function confirmAliasPayment($order, $quote)
    {
        $requestParams = Mage::helper('ops/creditcard')->getDirectLinkRequestParams($quote, $order);
        $this->invokeRequestParamValidation($requestParams);
        Mage::helper('ops/alias')->cleanUpAdditionalInformation($quote->getPayment(), true);

        return $this->performDirectLinkRequest($quote, $requestParams, $quote->getStoreId());

    }

    public function confirmDdPayment($order, $quote)
    {
        /** @var Netresearch_OPS_Helper_DirectDebit $directDebitHelper */
        $directDebitHelper = Mage::helper('ops/directDebit');
        $requestParams = Mage::app()->getRequest()->getParam('ops_directDebit');
        $directDebitHelper->handleAdminPayment($quote, $requestParams);
        $requestParams = $directDebitHelper->getDirectLinkRequestParams($quote, $order, $requestParams);
        $this->invokeRequestParamValidation($requestParams);

        return $this->performDirectLinkRequest($quote, $requestParams, $quote->getStoreId());
    }

    public function performDirectLinkRequest($quote, $params, $storeId = null)
    {
        $url = Mage::getModel('ops/config')->getDirectLinkGatewayOrderPath($storeId);
        $response = Mage::getSingleton('ops/api_directlink')->performRequest($params, $url, $storeId);
        /**
         * allow null as valid state for creating the order with status 'pending'
         */
        if (null != $response['STATUS'] && Mage::helper('ops/payment')->isPaymentFailed($response['STATUS'])) {
            Mage::throwException($this->getHelper()->__('Ingenico ePayments Payment failed'));
        }
        $quote->getPayment()->setAdditionalInformation('ops_response', $response)->save();

    }


    /**
     * Check if checkout was made with OPS CreditCart or DirectDebit
     *
     * @return boolean
     */
    protected function isCheckoutWithExistingTxId($code)
    {
        if ('ops_opsid' == $code) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Replace order cancel comfirm message of Magento by a custom message from Ingenico ePayments
     *
     * @event adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Netresearch_OPS_Model_Observer
     */
    public function updateOrderCancelButton(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Adminhtml_Block_Template */
        $block = $observer->getEvent()->getBlock();

        //Stop if block is not sales order view
        if ($block->getType() != 'adminhtml/sales_order_view') {
            return $this;
        }

        //If payment method is one of the Ingenico ePayments-ones and order can be cancelled manually
        if ($block->getOrder()->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Abstract
            && true === $block->getOrder()->getPayment()->getMethodInstance()->canCancelManually($block->getOrder())
        ) {
            //Build message and update cancel button
            $message = Mage::helper('ops')->__(
                "Are you sure you want to cancel this order? Warning:" .
                " Please check the payment status in the back-office of Ingenico ePayments before." .
                " By cancelling this order you won\\'t be able to update the status in Magento anymore."
            );
            $block->updateButton(
                'order_cancel',
                'onclick',
                'deleteConfirm(\'' . $message . '\', \'' . $block->getCancelUrl() . '\')'
            );
        }

        return $this;
    }

    /**
     *
     * appends a checkbox for closing the transaction if it's a Ingenico ePayments payment
     *
     * @event core_block_abstract_to_html_after
     *
     * @param Varien_Event_Observer $observer
     *
     * @return string
     */
    public function appendCheckBoxToRefundForm($observer)
    {
        $html = '';
        /*
         * show the checkbox only if the credit memo create page is displayed and
         * the refund can be done online and the payment is done via Ingenico ePayments
         */
        if ($observer->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals
            && $observer->getBlock()->getParentBlock()
            instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Items
            && $observer->getBlock()->getParentBlock()->getCreditmemo()->getOrder()->getPayment()
            && $observer->getBlock()->getParentBlock()->getCreditmemo()->getOrder()->getPayment()->getMethodInstance()
            instanceof Netresearch_OPS_Model_Payment_Abstract
            && $observer->getBlock()->getParentBlock()->getCreditmemo()->canRefund()
            && $observer->getBlock()->getParentBlock()->getCreditmemo()->getInvoice()
            && $observer->getBlock()->getParentBlock()->getCreditmemo()->getInvoice()->getTransactionId()
        ) {
            $transport = $observer->getTransport();
            $block = $observer->getBlock();
            $layout = $block->getLayout();
            $html = $transport->getHtml();
            $checkBoxHtml = $layout->createBlock(
                'ops/adminhtml_sales_order_creditmemo_totals_checkbox',
                'ops_refund_checkbox'
            )
                ->setTemplate('ops/sales/order/creditmemo/totals/checkbox.phtml')
                ->renderView();
            $html = $html . $checkBoxHtml;
            $transport->setHtml($html);
        }

        return $html;
    }

    /**
     *
     * fetch the creation of credit memo event and display warning message when
     * - credit memo could be done online
     * - payment is a Ingenico ePayments payment
     * - Ingenico ePayments transaction is closed
     *
     * @event core_block_abstract_to_html_after
     *
     * @param Varien_Event_Observer $observer
     *
     * @return string
     */
    public function showWarningForClosedTransactions($observer)
    {
        $html = '';
        /**
         * - credit memo could be done online
         * - payment is a Ingenico ePayments payment
         * - Ingenico ePayments transaction is closed
         */
        if ($observer->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create
            && $observer->getBlock()->getCreditmemo()->getOrder()->getPayment()
            && $observer->getBlock()->getCreditmemo()->getOrder()->getPayment()->getMethodInstance()
            instanceof Netresearch_OPS_Model_Payment_Abstract
            && $observer->getBlock()->getCreditmemo()->getInvoice()
            && $observer->getBlock()->getCreditmemo()->getInvoice()->getTransactionId()
            && false === $observer->getBlock()->getCreditmemo()->canRefund()
        ) {
            $transport = $observer->getTransport();
            $block = $observer->getBlock();
            $layout = $block->getLayout();
            $html = $transport->getHtml();
            $warningHtml = $layout->createBlock(
                'ops/adminhtml_sales_order_creditmemo_closedTransaction_warning',
                'ops_closed-transaction-warning'
            )->renderView();
            $html = $warningHtml . $html;
            $transport->setHtml($html);
        }

        return $html;
    }


    /**
     * triggered by cron for deleting old payment data from the additional payment information
     *
     * @param $observer
     */
    public function cleanUpOldPaymentData()
    {
        Mage::helper('ops/quote')->cleanUpOldPaymentInformation();
    }

    /**
     * in some cases the payment method is not set properly by Magento so we need to reset the
     * payment method in the quote's payment before importing the data
     *
     * @event sales_quote_payment_import_data_before
     *
     * @param $observer
     *
     * @return $this
     */
    public function clearPaymentMethodFromQuote(Varien_Event_Observer $observer)
    {
        if ($observer->getEventName() == 'sales_quote_payment_import_data_before'
            && $observer->getEvent()->getPayment() instanceof Mage_Sales_Model_Quote_Payment
        ) {
            $observer->getEvent()->getPayment()->setMethod(null);
        }

        return $this;
    }

    /**
     * appends the status update button to the order's button in case it's an Ingenico ePayments payment
     *
     * @event core_block_abstract_prepare_layout_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addStatusUpdateButtonToOrderView(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $paymentMethod = $block->getOrder()->getPayment()->getMethodInstance();
            if ($paymentMethod instanceof Netresearch_OPS_Model_Payment_Abstract
                && Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/invoice')
            ) {

                $block->addButton(
                    'ops_refresh', array(
                        'label'   => Mage::helper('ops/data')->__('Refresh payment status'),
                        'onclick' => 'setLocation(\'' . $block->getUrl('adminhtml/opsstatus/update') . '\')')
                );
            }
        }

        return $this;
    }

    /**
     * @event core_block_abstract_prepare_layout_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addCcPaymentMethod(Varien_Event_Observer $observer)
    {
        /** @var  $block Mage_Payment_Block_Form_Container */
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Payment_Block_Form_Container
            && !Mage::helper('ops/version')->canUseApplicableForQuote(Mage::getEdition())
        ) {
            Mage::helper('ops/payment')->addCCForZeroAmountCheckout($block);
        }

        return $this;
    }

    /**
     * @event core_block_abstract_prepare_layout_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function disableCaptureForZeroAmountInvoice(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items) {
            $invoice = $block->getInvoice();
            if ($invoice->getBaseGrandTotal() <= 0.01
                && $invoice->getOrder()->getPayment()->getMethodInstance() instanceof
                Netresearch_OPS_Model_Payment_Abstract
            ) {
                $invoice->getOrder()->getPayment()->getMethodInstance()->setCanCapture(false);
            }
        }

        return $this;
    }


    /**
     * @param $requestParams
     *
     * @throws Mage_Core_Exception
     * @return Netresearch_OPS_Model_Observer
     */
    protected function invokeRequestParamValidation($requestParams)
    {
        $validator = Mage::getModel('ops/validator_parameter_factory')->getValidatorFor(
            Netresearch_OPS_Model_Validator_Parameter_Factory::TYPE_REQUEST_PARAMS_VALIDATION
        );
        if (false == $validator->isValid($requestParams)) {
            $this->getOnepage()->getCheckout()->setGotoSection('payment');
            Mage::throwException(
                $this->getHelper()->__('The data you have provided can not be processed by Ingenico ePayments')
            );
        }

        return $this;
    }


    /**
     * validates the input fields after the payment step in OPC
     *
     * @event controller_action_postdispatch_checkout_onepage_savePayment
     *
     * @param Varien_Event_Observer $event
     *
     * @return $this
     */
    public function controllerActionCheckoutOnepagePostdispatch(Varien_Event_Observer $event)
    {
        $controller = $event->getControllerAction();
        $quote = $this->getOnepage()->getQuote();
        if ($quote->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Abstract) {
            /** @var Netresearch_OPS_Helper_Payment_Request $paramHelper */
            $paramHelper = Mage::helper('ops/payment_request');
            $shippingParams = array();
            $ownerParams   = $paramHelper->getOwnerParams($quote->getBillingAddress(), $quote);
            $billingParams = $paramHelper->extractBillToParameters($quote->getBillingAddress(), $quote);
            if ($quote->getShippingAddress()) {
                $shippingParams = $paramHelper->extractShipToParameters($quote->getShippingAddress(), $quote);
            }
            $params = array_merge($ownerParams, $shippingParams, $billingParams);
            $validator = Mage::getModel('ops/validator_parameter_factory')->getValidatorFor(
                Netresearch_OPS_Model_Validator_Parameter_Factory::TYPE_REQUEST_PARAMS_VALIDATION
            );
            if (false == $validator->isValid($params)) {
                $result = Mage::helper('ops/validation_result')->getValidationFailedResult(
                    $validator->getMessages(),
                    $quote
                );
                $controller->getResponse()->setBody(Mage::helper('core/data')->jsonEncode($result));
            }
        }

        return $this;
    }


    public function salesOrderPaymentCapture(Varien_Event_Observer $event)
    {
        /** @var $payment Mage_Sales_Model_Order_Payment */
        $payment = $event->getPayment();
        $invoice = $event->getInvoice();
        if ($payment->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Abstract) {
            $payment->setInvoice($invoice);
        }


        return $this;
    }

    /**
     * resets the order status back to pending payment in case of directlink payments in Ingenico ePayments authorize status
     *
     * @event sales_order_payment_place_end
     *
     * @param Varien_Event_Observer $event
     */
    public function setOrderStateDirectLink(Varien_Event_Observer $event)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $event->getPayment();

        if ($payment->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_DirectLink
            && Mage::helper('ops/payment')->isInlinePayment($payment)
            && Netresearch_OPS_Model_Status::AUTHORIZED == $payment->getAdditionalInformation('status')
            && $payment->getOrder()->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
        ) {
            $payment->getOrder()->setState(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true,
                $this->getHelper()->__('Payment has been authorized by Ingenico ePayments, but not yet captured.')
            );
        }
    }


    /**
     * appends the resend payment info button to the order's button in case it's an Ingenico ePayments payment
     * and the payment status is an authorize status
     *
     * @event core_block_abstract_prepare_layout_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addResendPaymentInfoButtonToOrderView(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
            $payment = $block->getOrder()->getPayment();
            $paymentMethod = $payment->getMethodInstance();
            if ($paymentMethod instanceof Netresearch_OPS_Model_Payment_Abstract
                && Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/invoice')
                && Netresearch_OPS_Model_Status::canResendPaymentInfo($payment->getAdditionalInformation('status'))
                && !in_array(
                    $block->getOrder()->getState(),
                    array(
                        Mage_Sales_Model_Order::STATE_CANCELED,
                        Mage_Sales_Model_Order::STATE_CLOSED,
                        Mage_Sales_Model_Order::STATE_COMPLETE
                    )
                )
            ) {
                $block->addButton(
                    'ops_resend_info', array(
                        'label'   => Mage::helper('ops/data')->__('Resend payment information'),
                        'onclick' => 'setLocation(\'' . $block->getUrl('adminhtml/admin/resendInfo') . '\')')
                );
            }
        }
    }

    /**
     * Adjusts the confirmation message text of the recurring profiles cancel and suspend button to inform the merchant
     * that no call to Ingenico ePayments will happen
     *
     * @event        adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function updateRecurringProfileButtons(Varien_Event_Observer $observer)
    {
        /** @var $block Mage_Sales_Block_Adminhtml_Recurring_Profile_View */
        $block = $observer->getEvent()->getBlock();

        if ($block->getType() == 'sales/adminhtml_recurring_profile_view') {
            $profile = Mage::registry('current_recurring_profile');
            if ($profile->getMethodCode() == Netresearch_OPS_Model_Payment_Recurring_Cc::CODE) {
                $cancelMessage = Mage::helper('ops')
                    ->__(
                        'Are you sure you want to perform this action?' .
                        ' Canceling the subscription here will not actually cancel the subscription on Ingenico ePayments side.' .
                        ' To stop charging the customer you will have to deactivate the subscription there.'
                    );
                $cancelUrl = $block->getUrl(
                    '*/*/updateState',
                    array('profile' => $profile->getId(), 'action' => 'cancel')
                );

                $block->updateButton(
                    'cancel',
                    'onclick',
                    "confirmSetLocation('{$cancelMessage}', '{$cancelUrl}')"
                );

                $suspendMessage = Mage::helper('ops')
                    ->__(
                        'Are you sure you want to perform this action?' .
                        'Suspending the subscription here will not actually cancel the subscription on Ingenico ePayments side.' .
                        'To stop charging the customer you will have to deactivate the subscription there.'
                    );
                $suspendUrl = $block->getUrl(
                    '*/*/updateState',
                    array('profile' => $profile->getId(), 'action' => 'suspend')
                );

                $block->updateButton(
                    'suspend',
                    'onclick',
                    "confirmSetLocation('{$suspendMessage}', '{$suspendUrl}')"
                );
            }
        }
    }

    /**
     * Overwrites the state of the recurring profile if necessary
     *
     * @event model_save_before - due to lack of event prefix for recurring profile models
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function overrideRecurringProfileState(Varien_Event_Observer $observer)
    {
        $object = $observer->getObject();

        /** @var $object Mage_Payment_Model_Recurring_Profile */
        if ($object instanceof Mage_Payment_Model_Recurring_Profile
            && $object->getMethodCode() === Netresearch_OPS_Model_Payment_Recurring_Cc::CODE
            && $object->getState() != $object->getNewState()
            && $object->getOverrideState() === true
        ) {
            $object->setState($object->getNewState());
        }

        return $this;
    }


    /**
     * @event adminhtml_block_html_before
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function updateRecurringProfileEditForm(Varien_Event_Observer $observer)
    {
        if ($observer->getBlock() instanceof Mage_Sales_Block_Adminhtml_Recurring_Profile_Edit_Form
            && Mage::getModel('ops/payment_recurring_cc')->getConfigData('active')
        ) {
            /** @var Mage_Sales_Block_Adminhtml_Recurring_Profile_Edit_Form $form */
            $html = $observer->getTransport()->getHtml();

            $method = Mage::getModel('ops/payment_recurring_cc');

            $message = Mage::helper('ops')
                ->__(
                    "When using %s as payment method the settings for '%s' and '%s' will not be processed.",
                    $method->getTitle(),
                    Mage::helper('payment')->__('Allow Initial Fee Failure'),
                    Mage::helper('payment')->__('Maximum Payment Failures')
                );

            $message = '<div class="notice-msg" style="padding-left: 26px;"><p style="padding: 7px;">' . $message
                . '</p></div>';
            $observer->getTransport()->setHtml($html . $message);


        }

        return $this;
    }

    /**
     * Since there is no other way for inline payments to change the order state, we enforce the pending_payment state
     * for only authorized, not yet payed orders
     *
     * @param Varien_Event_Observer $observer
     *
     * @event sales_order_payment_place_end
     * @return $this
     */
    public function enforcePaymentPendingForAuthorizedOrders(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getData('payment');
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        $status = $payment->getAdditionalInformation('status');
        if ($this->getConfig()->getPaymentAction($order->getStoreId())
            == Netresearch_OPS_Model_Payment_Abstract::ACTION_AUTHORIZE
            && Netresearch_OPS_Model_Status::isAuthorize($status)
            && $order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
        ) {
            $message = $this->getHelper()->__('Order has been authorized, but not captured/paid yet.');
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, $message);

        }

        return $this;
    }

    /**
     * Magento does not send order confirmation emails when
     * - payment action "authorization" is processed in frontend via gateway.
     * Magento does not send invoice emails when
     * - payment action "authorization+capture" is processed in frontend or admin
     *
     * event: checkout_submit_all_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendTransactionalEmails(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        if ($order == null || !$order->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_Abstract) {
            // ignore third-party payment methods
            return;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getQuote();
        if ($quote && $quote->getPayment()->getOrderPlaceRedirectUrl()) {
            // redirect payments require special treatment, may still get cancelled or declined
            return;
        }

        try {
            Mage::helper('ops/data')->sendTransactionalEmail($order);
            Mage::helper('ops/data')->sendTransactionalEmail($order->getPayment()->getCreatedInvoice());
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * triggers the email sending for payment method payPerMail
     *
     * event: sales_order_place_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendPayPerMailInfo(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        if (!$order->getPayment()->getMethodInstance() instanceof Netresearch_OPS_Model_Payment_PayPerMail) {
            return;
        }

        /** @var Netresearch_OPS_Model_Payment_Features_PaymentEmail $sendEmailModel */
        $sendEmailModel = Mage::getModel('ops/payment_features_paymentEmail');
        $sendEmailModel->resendPaymentInfo($order);
    }
}
