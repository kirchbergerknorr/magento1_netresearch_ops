<?php

/**
 * Netresearch_OPS_Model_Payment_Cc
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_Cc extends Netresearch_OPS_Model_Payment_DirectLink
{
    const CODE = 'ops_cc';

    /** info source path */
    protected $_infoBlockType = 'ops/info_cc';

    /** @var string $_formBlockType define a specific form block */
    protected $_formBlockType = 'ops/form_cc';

    /** payment code */
    protected $_code = self::CODE;

    protected $featureModel = null;


    /**
     * @param null $payment
     * @return string
     */
    public function getOpsCode($payment = null)
    {
        $opsBrand = $this->getOpsBrand($payment);
        if ('PostFinance card' == $opsBrand) {
            return 'PostFinance Card';
        }
        if ('UNEUROCOM' == $this->getOpsBrand($payment)) {
            return 'UNEUROCOM';
        }

        return 'CreditCard';
    }

    /**
     * @param null $payment
     *
     * @return array|mixed|null
     */
    public function getOpsBrand($payment = null)
    {
        if (null === $payment) {
            $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
        }

        return $payment->getAdditionalInformation('CC_BRAND');
    }

    /**
     * If payment is inline there should be no orderPlaceRedirectUrl except for 3d secure cards - if order was placed
     * through admin it is definitely an inline payment.
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return bool|string
     */
    public function getOrderPlaceRedirectUrl($payment = null)
    {
        $salesObject = $this->getInfoInstance()->getOrder() ? : $this->getInfoInstance()->getQuote();
        if ($this->hasBrandAliasInterfaceSupport($payment) || null === $salesObject->getRemoteIp()) {
            if ('' == $this->getOpsHtmlAnswer($payment)) {
                return false;
            } // Prevent redirect on cc payment
            else {
                return $this->getConfig()->get3dSecureRedirectUrl();
            }
        }

        return parent::getOrderPlaceRedirectUrl();
    }

    /**
     * only some brands are supported to be integrated into onepage checkout
     *
     * @return array
     */
    public function getBrandsForAliasInterface()
    {
        $brands = $this->getConfig()->getInlinePaymentCcTypes($this->getCode());

        return $brands;
    }

    /**
     * if cc brand supports ops alias interface
     *
     * @param Mage_Payment_Model_Info $payment
     *
     * @return bool
     */
    public function hasBrandAliasInterfaceSupport($payment = null)
    {
        return in_array(
            $this->getOpsBrand($payment),
            $this->getBrandsForAliasInterface()
        );
    }

    /**
     * Validates alias for in quote provided addresses
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Varien_Object          $payment
     *
     * @throws Mage_Core_Exception
     */
    protected function validateAlias($quote, $payment)
    {
        $alias = $payment->getAdditionalInformation('alias');
        if (0 < strlen(trim($alias))
            && is_numeric($payment->getAdditionalInformation('cvc'))
            && false === Mage::helper('ops/alias')->isAliasValidForAddresses(
                $quote->getCustomerId(),
                $alias,
                $quote->getBillingAddress(),
                $quote->getShippingAddress(),
                $quote->getStoreId()
            )
        ) {
            $this->getOnepage()->getCheckout()->setGotoSection('payment');
            Mage::throwException(
                $this->getHelper()->__('Invalid payment information provided!')
            );
        }
    }

    /**
     * @return Netresearch_OPS_Helper_Creditcard
     */
    public function getRequestParamsHelper()
    {
        if (null === $this->requestParamsHelper) {
            $this->requestParamsHelper = Mage::helper('ops/creditcard');
        }

        return $this->requestParamsHelper;
    }


    protected function performPreDirectLinkCallActions(
        Mage_Sales_Model_Quote $quote, Varien_Object $payment,
        $requestParams = array()
    ) 
    {
        Mage::helper('ops/alias')->cleanUpAdditionalInformation($payment, true);
        if (true === Mage::getModel('ops/config')->isAliasManagerEnabled($this->getCode())) {
            $this->validateAlias($quote, $payment);
        }

        return $this;
    }

    protected function performPostDirectLinkCallAction(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order)
    {
        Mage::helper('ops/alias')->setAliasActive($quote, $order);

        return $this;
    }

    protected function handleAdminPayment(Mage_Sales_Model_Quote $quote)
    {
        return $this;
    }


    /**
     * returns allow zero amount authorization
     * only TRUE if configured payment action for the store is authorize
     *
     * @param mixed null|int $storeId
     *
     * @return bool
     */
    public function isZeroAmountAuthorizationAllowed($storeId = null)
    {
        $result = false;
        if (
            $this->getConfig()->getPaymentAction($storeId) == Netresearch_OPS_Model_Payment_Abstract::ACTION_AUTHORIZE
            && true == Mage::getStoreConfig('payment/ops_cc/zero_amount_checkout', $storeId)
        ) {
            $result = true;
        }

        return $result;
    }


    /**
     * method was implemented in CE 1.8 / EE 1.14
     * if Version is CE 1.8 / EE 1.14 use parent method otherwise use our implementation
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param                        $checksBitMask
     *
     * @return bool
     */
    public function isApplicableToQuote($quote, $checksBitMask)
    {
        $result = true;
        if (Mage::helper('ops/version')->canUseApplicableForQuote(Mage::getEdition())) {
            $result = parent::isApplicableToQuote($quote, $checksBitMask);
        }

        if ($quote->getBaseGrandTotal() < 0.01 && $result === false) {
            $result = $this->getFeatureModel()->isCCAndZeroAmountAuthAllowed($this, $quote);
        }

        return $result;
    }

    /**
     * @return Netresearch_OPS_Model_Payment_Features_ZeroAmountAuth
     */
    public function getFeatureModel()
    {
        if (null === $this->featureModel) {
            $this->featureModel = Mage::getModel('ops/payment_features_zeroAmountAuth');
        }

        return $this->featureModel;
    }

    /**
     * setter for canCapture from outside, needed for zero amount order since we need to disable online capture
     * but still need to be able to create a invoice
     *
     * @param $canCapture
     */
    public function setCanCapture($canCapture)
    {
        if ($this->_canCapture != $canCapture) {
            $this->_canCapture = $canCapture;
        }
    }

    /**
     * Check wether payment method is available for quote
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (null != $quote && !$quote->getItemsCount() > 0 && $this->getDataHelper()->isAdminSession()) {
            /* Disable payment method in backend as long as there are no items in quote to
            *  avoid problems with alias creation in EE1.12 & EE1.13
            */
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function isInitializeNeeded()
    {
        return !$this->getPaymentHelper()->isInlinePayment($this->getInfoInstance());
    }

    /**
     * @inheritdoc
     */
    public function getMethodDependendFormFields($order, $requestParams = null)
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);
        if ($this->getConfig()->getCreditDebitSplit($order->getStoreId())) {
            $formFields['CREDITDEBIT'] = "C";
        }

        $alias = $order->getPayment()->getAdditionalInformation('alias');

        if ($alias) {
            $formFields['ALIAS'] = $alias;
            $formFields['ALIASOPERATION'] = "BYPSP";
            $formFields['ECI'] = 9;
            $formFields['ALIASUSAGE'] = $this->getConfig()->getAliasUsageForExistingAlias(
                $order->getPayment()->getMethodInstance()->getCode(),
                $order->getStoreId()
            );
        } else {
            $formFields['ALIAS'] = "";
            $formFields['ALIASOPERATION'] = "BYPSP";
            $formFields['ALIASUSAGE'] = $this->getConfig()->getAliasUsageForNewAlias(
                $order->getPayment()->getMethodInstance()->getCode(),
                $order->getStoreId()
            );

        }

        return $formFields;
    }
}

