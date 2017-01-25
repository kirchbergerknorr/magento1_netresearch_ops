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
 * @copyright Copyright (c) 2016 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Netresearch_OPS_PaymentController
 *
 * @category  controller
 * @package   Netresearch_OPS
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_PaymentController extends Netresearch_OPS_Controller_Abstract
{
    /**
     * Load place from layout to make POST on ops
     */
    public function placeformAction()
    {

        $lastIncrementId = $this->_getCheckout()->getLastRealOrderId();

        if ($lastIncrementId) {
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($lastIncrementId);
        } else {
            return $this->_redirect('checkout/cart');
        }

        $quote = $this->_getCheckout()->getQuote();
        if ($quote) {
            $quote->setIsActive(false)->save();
        }
        $this->_getCheckout()->setOPSQuoteId($this->_getCheckout()->getQuoteId());
        $this->_getCheckout()->setOPSLastSuccessQuoteId($this->_getCheckout()->getLastSuccessQuoteId());
        $this->_getCheckout()->clear();

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Render 3DSecure response HTML_ANSWER
     */
    public function placeform3dsecureAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Display our pay page, need to ops payment with external pay page mode     *
     */
    public function paypageAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * when payment gateway accept the payment, it will land to here
     * need to change order status as processed ops
     * update transaction id
     *
     */
    public function acceptAction()
    {
        $redirect = '';
        try {
            $order = $this->_getOrder();
            if ($this->getQuote()) {
                $this->getQuote()->setIsActive(false)->save();
            }
            $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
            $this->_getCheckout()->setLastQuoteId($order->getQuoteId());
            $this->_getCheckout()->setLastOrderId($order->getId());
        } catch (Exception $e) {
            /** @var Netresearch_OPS_Helper_Data $helper */
            $helper = Mage::helper('ops');
            $helper->log($helper->__("Exception in acceptAction: " . $e->getMessage()));
            $this->getPaymentHelper()->refillCart($this->_getOrder());
            $redirect = 'checkout/cart';
        }
        if ($redirect === '') {
            $redirect = 'checkout/onepage/success';
        }

        $this->redirectOpsRequest($redirect);
    }

    /**
     * the payment result is uncertain
     * exception status can be 52 or 92
     * need to change order status as processing ops
     * update transaction id
     *
     */
    public function exceptionAction()
    {
        $order = $this->_getOrder();
        $this->_getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
        $this->_getCheckout()->setLastQuoteId($order->getQuoteId());
        $this->_getCheckout()->setLastOrderId($order->getId());

        $msg = 'Your order has been registered, but your payment is still marked as pending.';
        $msg .= ' Please have patience until the final status is known.';
        $this->_getCheckout()->addError(Mage::helper('ops/data')->__($msg));

        $this->redirectOpsRequest('checkout/onepage/success');
    }

    /**
     * when payment got decline
     * need to change order status to cancelled
     * take the user back to shopping cart
     *
     */
    public function declineAction()
    {
        try {
            $this->_getCheckout()->setQuoteId($this->_getCheckout()->getOPSQuoteId());
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $this->getPaymentHelper()->refillCart($this->_getOrder());

        $message = Mage::helper('ops')->__(
            'Your payment information was declined. Please select another payment method.'
        );
        Mage::getSingleton('core/session')->addNotice($message);
        $redirect = 'checkout/onepage';
        $this->redirectOpsRequest($redirect);
    }

    /**
     * when user cancel the payment
     * change order status to cancelled
     * need to redirect user to shopping cart
     *
     */
    public function cancelAction()
    {
        try {
            $this->_getCheckout()->setQuoteId($this->_getCheckout()->getOPSQuoteId());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        if (false == $this->_getOrder()->getId()) {
            $this->_order = null;
            $this->_getOrder($this->_getCheckout()->getLastQuoteId());
        }

        $this->getPaymentHelper()->refillCart($this->_getOrder());

        $redirect = 'checkout/cart';
        $this->redirectOpsRequest($redirect);

    }

    /**
     * when user cancel the payment and press on button "Back to Catalog" or "Back to Merchant Shop" in Orops
     *
     */
    public function continueAction()
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load(
            $this->_getCheckout()->getLastOrderId()
        );
        $this->getPaymentHelper()->refillCart($order);
        $redirect = $this->getRequest()->getParam('redirect');
        if ($redirect == 'catalog'): //In Case of "Back to Catalog" Button in OPS
            $redirect = '/';
        else: //In Case of Cancel Auto-Redirect or "Back to Merchant Shop" Button
            $redirect = 'checkout/cart';
        endif;
        $this->redirectOpsRequest($redirect);
    }

    /*
     * Check the validation of the request from OPS
     */
    protected function checkRequestValidity()
    {
        if (!$this->_validateOPSData()) {
            Mage::throwException("Hash is not valid");
        }
    }

    public function registerDirectDebitPaymentAction()
    {
        $params = $this->getRequest()->getParams();
        $validator = Mage::getModel('ops/validator_payment_directDebit');
        if (false === $validator->isValid($params)) {
            $this->getResponse()
                ->setHttpResponseCode(406)
                ->setBody($this->__(implode(PHP_EOL, $validator->getMessages())))
                ->sendHeaders();

            return;
        }
        $payment = $this->_getCheckout()->getQuote()->getPayment();
        $helper = Mage::helper('ops/directDebit');
        $payment = $helper->setDirectDebitDataToPayment($payment, $params);

        $payment->save();

        $this->getResponse()->sendHeaders();
    }

    public function saveCcBrandAction()
    {
        $brand = $this->getRequest()->getParam('brand');
        $alias = $this->getRequest()->getParam('alias');
        $quote = $this->getQuote();
        if ($quote->getId() === null) {
            $this->_redirect('checkout/cart');
        } else {
            $payment = $quote->getPayment();
            $payment->setAdditionalInformation('CC_BRAND', $brand);
            $payment->setAdditionalInformation('alias', $alias);
            $payment->setDataChanges(true);
            $payment->save();
            Mage::helper('ops')->log('saved cc brand ' . $brand . ' for quote #' . $quote->getId());
            $this->getResponse()->sendHeaders();
        }
    }

    /**
     * Action to retry paying the order on Ingenico
     *
     */
    public function retryAction()
    {

        $order = $this->_getOrder();
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();
        $message = false;

        // only validate the parameters we added to the BACKURL ourselves
        $params = array(
            'SHASIGN' => $this->getRequest()->getParam('SHASIGN'),
            'orderID' => $this->getRequest()->getParam('orderID')
        );

        if ($this->_validateOPSData($params) === false) {
            $message = Mage::helper('ops')->__('Hash not valid');

        } else {

            if ($this->canRetryPayment($payment)) {

                // Add Quote to Session, for the payment methods
                /** @var Mage_Sales_Model_Quote $quote */
                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                /** @var Mage_Checkout_Model_Session $checkoutSession */
                $checkoutSession = Mage::getSingleton('checkout/session');
                $checkoutSession->setQuoteId($quote->getId());
                // Set Quote to Active, to be able to load quote data
                $quote->setIsActive(1);
                $quote->save();

                $this->loadLayout();
                $this->renderLayout();

            } else {
                $message = Mage::helper('ops')->__(
                    'Not possible to reenter the payment details for order %s', $order->getIncrementId()
                );
            }
        }
        if ($message) {
            Mage::getSingleton('core/session')->addNotice($message);
            $this->redirectOpsRequest('/');
        }
    }

    public function updatePaymentAndPlaceFormAction()
    {
        // Save new payment method in order
        /** @var Mage_Sales_Model_Order $order */
        $order = $this->_getOrder();
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $order->getPayment();
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $quote->setIsActive(0);
        $message = false;

        try {
            $newPayment = $this->getRequest()->getParam('payment');
            $quote->getPayment()->importData($newPayment);
            $payment->setMethod($newPayment['method'])->getMethodInstance()->assignData(new Varien_Object($newPayment));
            $remoteAddr = Mage::helper('core/http')->getRemoteAddr();
            $quote->setRemoteIp($remoteAddr);
            $order->setRemoteIp($remoteAddr);
            $quote->save();
            $payment->save();
            $order->save();

            // Set Session Data for further process
            /** @var Mage_Checkout_Model_Session $checkoutSession */
            $checkoutSession = Mage::getSingleton('checkout/session');
            $checkoutSession->replaceQuote($quote);
            $checkoutSession->setLastOrderId($order->getId());
            $checkoutSession->setLastRealOrderId($order->getIncrementId());
            $checkoutSession->setLastQuoteId($quote->getId());
            $checkoutSession->setLastSuccessQuoteId($quote->getId());

            $redirectUrl = $payment->getMethodInstance()->getOrderPlaceRedirectUrl();

            // Place order or rather in this case, send the inline payment method to Ingenico ePayments
            if (empty($redirectUrl)) {
                $checkoutSession->setRedirectUrl($redirectUrl);
                $order->place();
                $order->save();
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        if ($message) {
            Mage::getSingleton('core/session')->addNotice($message);
            $this->_redirect('checkout/cart');
        } else {
            if (empty($redirectUrl)) {
                $this->_redirect('checkout/onepage/success');
            } else {
                $this->_redirectUrl($redirectUrl);
            }
        }
    }

    protected function wasIframeRequest()
    {
        return $this->getConfig()->getConfigData('template', $this->_getOrder()->getStoreId())
        === Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_IFRAME;
    }

    /**
     * Generates the Javascript snippet that move the redirect to the parent frame in iframe mode.
     *
     * @param $redirect
     *
     * @return string javascript snippet
     */
    protected function generateJavaScript($redirect)
    {
        $javascript
            = "
        <script type=\"text/javascript\">
            window.top.location.href = '" . Mage::getUrl($redirect) . "'
        </script>";

        return $javascript;
    }

    /**
     * Redirects the customer to the given redirect path or inserts the js-snippet needed for iframe template mode into
     * the response instead
     *
     * @param $redirect
     */
    protected function redirectOpsRequest($redirect)
    {
        if ($this->wasIframeRequest()) {
            $this->getResponse()->setBody($this->generateJavaScript($redirect));
        } else {
            $this->_redirect($redirect);
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return bool
     */
    protected function canRetryPayment($payment)
    {
        $additionalInformation = $payment->getAdditionalInformation();
        if (is_array($additionalInformation) && array_key_exists('status', $additionalInformation)) {
            $status = $additionalInformation['status'];
            return Netresearch_OPS_Model_Status::canResendPaymentInfo($status);
        }

        return true;
    }
}
