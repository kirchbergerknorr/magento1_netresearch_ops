<?php
/**
 * Netresearch_OPS_Helper_Data
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Helper_Data extends Mage_Core_Helper_Abstract
{
    const LOG_FILE_NAME = 'ops.log';

    /**
     * Returns config model
     *
     * @return Netresearch_OPS_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('ops/config');
    }

    /**
     * Replace all dots or any content following and including plus ("+") and minus ("-") signs.
     * @return string
     */
    public function getModuleVersionString()
    {
        $version = Mage::getConfig()->getNode('modules/Netresearch_OPS/version');
        $plainversion = preg_replace('/\.|[+-].+$/', '', $version);
        return 'IG1X' . $plainversion;
    }

    /**
     * Checks if logging is enabled and if yes, logs given message to logfile
     *
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = null)
    {
        $separator = "\n"."===================================================================";
        $message = $this->clearMsg($message);
        if ($this->getConfig()->shouldLogRequests()) {
            Mage::log($message.$separator, $level, self::LOG_FILE_NAME);
        }
    }

    /**
     * Returns full path to ops.log
     */
    public function getLogPath()
    {
        return Mage::getBaseDir('log'). DIRECTORY_SEPARATOR . self::LOG_FILE_NAME;
    }


    /**
     * deletes certain keys from the message which is going to logged
     *
     * @param $message - the message
     *
     * @return array - the cleared message
     */
    public function clearMsg($message)
    {
        if (is_array($message)) {
            $keysToBeDeleted = array('cvc', 'CVC');
            foreach ($keysToBeDeleted as $keyToDelete) {
                if (array_key_exists($keyToDelete, $message)) {
                    unset($message[$keyToDelete]);
                }
            }
        }
        if (is_string($message)) {
            $message = preg_replace('/"CVC":".*"(,)/i', '', $message);
            $message = preg_replace('/"CVC":".*"/i', '', $message);
            $message = preg_replace('/"CVC".*"[A-Z]*";/', '', $message);
            $message = preg_replace('/"CVC":".*"(})/i', '}', $message);
        }
        return $message;
    }

    public function redirect($url)
    {
        Mage::app()->getResponse()->setRedirect($url);
        Mage::app()->getResponse()->sendResponse();
    }

    /**
     * Redirects to the given order and prints some notice output
     *
     * @param int $orderId
     * @param string $message
     * @return void
    */
    public function redirectNoticed($orderId, $message)
    {
        Mage::getSingleton('core/session')->addNotice($message);
        $this->redirect(
            Mage::getUrl('*/sales_order/view', array('order_id' => $orderId))
        );
    }

    public function getStatusText($statusCode)
    {
        $translationOrigin = "STATUS_".$statusCode;
        $translationResult = $this->__($translationOrigin);
        if ($translationOrigin != $translationResult):
            return $translationResult. " ($statusCode)";
        else:
            return $statusCode;
        endif;
    }

    public function getAmount($amount)
    {
        return round($amount * 100);
    }

    public function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }

    public function isAdminSession()
    {
        if ($this->getAdminSession()->getUser()) {
            return 0 < $this->getAdminSession()->getUser()->getUserId() || $this->getAdminSession()->isLoggedIn();
        }
        return false;
    }

    /*
     * check if user is registering or not
     */
    public function checkIfUserIsRegistering()
    {
        $isRegistering = false;
        $checkoutMethod = Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod();
        if ($checkoutMethod === Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER
            || $checkoutMethod === Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN
           ) {
                $isRegistering = true;
        }
        return $isRegistering;

    }

    /*
     * check if user is registering or not
     */
    public function checkIfUserIsNotRegistering()
    {
        $isRegistering = false;
        $checkoutMethod = Mage::getSingleton('checkout/session')->getQuote()->getCheckoutMethod();
        if ($checkoutMethod === Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
                $isRegistering = true;
        }
        return $isRegistering;

    }

    /**
     * Trigger sending order confirmation and invoice emails when Magento does not:
     * - "authorization" after return from gateway (order emails)
     * - "authorization+capture" (order or invoice emails)
     *
     * @param Mage_Sales_Model_Abstract $document
     * @return Mage_Sales_Model_Abstract
     * @throws Exception
     */
    public function sendTransactionalEmail(Mage_Sales_Model_Abstract $document)
    {
        if ($document instanceof Mage_Sales_Model_Order) {

            if (!$document->getEmailSent() && $document->getCanSendNewEmailFlag()) {
                $document->sendNewOrderEmail();
            }

        } elseif ($document instanceof Mage_Sales_Model_Order_Invoice) {

            if (!$document->getEmailSent() && Mage::getModel('ops/config')->getSendInvoice()) {
                $document->sendEmail();
            }

        }

        return $document;
    }
}
