<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Config model
 */
class Netresearch_OPS_Model_Config extends Mage_Payment_Model_Config
{
    const OPS_PAYMENT_PATH = 'payment_services/ops/';
    const OPS_CONTROLLER_ROUTE_API = 'ops/api/';
    const OPS_CONTROLLER_ROUTE_PAYMENT = 'ops/payment/';
    const OPS_CONTROLLER_ROUTE_ALIAS = 'ops/alias/';

    /**
     * Return ops payment config information
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return Simple_Xml
     */
    public function getConfigData($path, $storeId = null)
    {
        $result = false;
        if (!empty($path)) {
            $result = Mage::getStoreConfig(self::OPS_PAYMENT_PATH . $path, $storeId);
        }

        return $result;
    }

    /**
     * Return SHA1-in crypt key from config. Setup on admin place.
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getShaInCode($storeId = null)
    {
        return Mage::helper('core')->decrypt($this->getConfigData('secret_key_in', $storeId));
    }

    /**
     * Return SHA1-out crypt key from config. Setup on admin place.
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getShaOutCode($storeId = null)
    {
        return Mage::helper('core')->decrypt($this->getConfigData('secret_key_out', $storeId));
    }

    /**
     * Return frontend gateway path, get from config. Setup on admin place.
     *
     * @param string $path
     * @param int $storeId
     *
     * @return string
     */
    public function getFrontendGatewayPath($storeId = null)
    {
        return $this->determineOpsUrl('frontend_gateway', $storeId);
    }

    /**
     * Return Direct Link Gateway path, get from config. Setup on admin place.
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getDirectLinkGatewayPath($storeId = null)
    {
        return $this->determineOpsUrl('directlink_gateway', $storeId);
    }

    public function getDirectLinkGatewayOrderPath($storeId = null)
    {
        return $this->determineOpsUrl('directlink_gateway_order', $storeId);
    }

    /**
     * Return API User, get from config. Setup on admin place.
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getApiUserId($storeId = null)
    {
        return $this->getConfigData('api_userid', $storeId);
    }

    /**
     * Return API Passwd, get from config. Setup on admin place.
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getApiPswd($storeId = null)
    {
        return Mage::helper('core')->decrypt($this->getConfigData('api_pswd', $storeId));
    }

    /**
     * Get PSPID, affiliation name in ops system
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getPSPID($storeId = null)
    {
        return $this->getConfigData('pspid', $storeId);
    }

    public function getPaymentAction($storeId = null)
    {
        return $this->getConfigData('payment_action', $storeId);
    }

    /**
     * Get paypage template for magento style templates using
     *
     * @return string
     */
    public function getPayPageTemplate()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'paypage',
            array('_nosid' => true, '_secure' => $this->isCurrentlySecure())
        );
    }

    /**
     * Return url which ops system will use as accept
     *
     * @return string
     */
    public function getAcceptUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'accept',
            array('_nosid' => true, '_secure' => $this->isCurrentlySecure())
        );
    }

    /**
     * Return url which ops system will use as accept for alias generation
     *
     * @param int  $storeId
     * @param bool $admin
     *
     * @return string
     */
    public function getAliasAcceptUrl($storeId = null, $admin = false)
    {
        $params = array(
            '_secure' => $this->isCurrentlySecure(),
            '_nosid'  => true
        );
        if (null != $storeId) {
            $params['_store'] = $storeId;
        }

        if ($admin) {
            $params['_nosecret'] = true;

            return Mage::getModel('adminhtml/url')->getUrl('adminhtml/alias/accept', $params);
        } else {
            return Mage::getModel('core/url')->getUrl(self::OPS_CONTROLLER_ROUTE_ALIAS . 'accept', $params);
        }
    }

    /**
     * Return url which ops system will use as decline url
     *
     * @return string
     */
    public function getDeclineUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'decline',
            array('_nosid' => true, '_secure' => $this->isCurrentlySecure())
        );
    }

    /**
     * Return url which ops system will use as exception url
     *
     * @return string
     */
    public function getExceptionUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'exception',
            array('_nosid' => true, '_secure' => $this->isCurrentlySecure())
        );
    }

    /**
     * Return url which ops system will use as exception url for alias generation
     *
     * @param int  $storeId
     * @param bool $admin
     *
     * @return string
     */
    public function getAliasExceptionUrl($storeId = null, $admin = false)
    {
        $params = array(
            '_secure' => $this->isCurrentlySecure(),
            '_nosid'  => true
        );
        if (null !=$storeId) {
            $params['_store'] = $storeId;
        }
        if ($admin) {
            $params['_nosecret'] = true;

            return Mage::getModel('adminhtml/url')->getUrl('adminhtml/alias/exception', $params);
        } else {
            return Mage::getModel('core/url')->getUrl(self::OPS_CONTROLLER_ROUTE_ALIAS . 'exception', $params);
        }
    }

    /**
     * Return url which ops system will use as cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'cancel',
            array('_nosid' => true, '_secure' => $this->isCurrentlySecure())
        );
    }

    /**
     * Return url which ops system will use as continue shopping url
     *
     * @param array $redirect
     *
     * @return string
     */
    public function getContinueUrl($redirect = array())
    {
        $urlParams = array('_nosid' => true, '_secure' => $this->isCurrentlySecure());
        if (!empty($redirect)) {
            $urlParams = array_merge($redirect, $urlParams);
        }

        return Mage::getUrl(self::OPS_CONTROLLER_ROUTE_PAYMENT . 'continue', $urlParams);
    }

    /**
     * Return url to redirect after confirming the order
     *
     * @return string
     */
    public function getPaymentRedirectUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'placeform',
            array('_secure' => true, '_nosid' => true)
        );
    }

    /**
     * Return 3D Secure url to redirect after confirming the order
     *
     * @return string
     */
    public function get3dSecureRedirectUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'placeform3dsecure',
            array('_secure' => true, '_nosid' => true)
        );
    }

    public function getSaveCcBrandUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'saveCcBrand',
            array('_secure' => $this->isCurrentlySecure(), '_nosid' => true)
        );
    }

    public function getGenerateHashUrl($storeId = null, $admin = false)
    {
        $params = array(
            '_secure' => $this->isCurrentlySecure(),
            '_nosid'  => true,
        );
        if (null != $storeId) {
            $params['_store'] = $storeId;
        }
        if ($admin) {
            $params['_nosecret'] = true;

            return Mage::getModel('adminhtml/url')->getUrl('adminhtml/alias/generatehash', $params);
        } else {
            return Mage::getModel('core/url')->getUrl(self::OPS_CONTROLLER_ROUTE_ALIAS . 'generatehash', $params);
        }

    }

    /**
     * Checks if requests should be logged or not regarding configuration
     *
     * @param null $storeId
     * @return Simple_Xml
     */
    public function shouldLogRequests($storeId = null)
    {
        return $this->getConfigData('debug_flag', $storeId);
    }

    /**
     * @return mixed
     */
    public function hasCatalogUrl()
    {
        return Mage::getStoreConfig('payment_services/ops/showcatalogbutton');
    }

    /**
     * @return mixed
     */
    public function hasHomeUrl()
    {
        return Mage::getStoreConfig('payment_services/ops/showhomebutton');
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getAcceptedCcTypes($code)
    {
        return Mage::getStoreConfig('payment/' . $code . '/types');
    }

    /**
     * Returns the cc types for which inline payments are activated
     *
     * @param string $code
     *
     * @return string[]
     */
    public function getInlinePaymentCcTypes($code)
    {
        $redirectAll = (bool)(int)Mage::getStoreConfig('payment/' . $code . '/redirect_all');
        if ($redirectAll) {
            return array();
        }

        $inlineTypes = Mage::getStoreConfig('payment/' . $code . '/inline_types');
        if (false == is_array($inlineTypes)) {
            $inlineTypes = explode(',', $inlineTypes);
        }

        return $inlineTypes;
    }

    /**
     * @return mixed
     */
    public function get3dSecureIsActive()
    {
        return Mage::getStoreConfig('payment/ops_cc/enabled_3dsecure');
    }

    /**
     * @return mixed
     */
    public function getDirectDebitCountryIds()
    {
        return Mage::getStoreConfig('payment/ops_directDebit/countryIds');
    }

    /**
     * @return mixed
     */
    public function getBankTransferCountryIds()
    {
        return Mage::getStoreConfig('payment/ops_bankTransfer/countryIds');
    }

    /**
     * @return mixed
     */
    public function getDirectEbankingBrands()
    {
        return Mage::getStoreConfig('payment/ops_directEbanking/brands');
    }

    /**
     * Returns the generated alias (hosted tokenization) url or the special url if needed by vendor
     *
     * @param null $storeId
     *
     * @return mixed|Simple_Xml|string
     */
    public function getAliasGatewayUrl($storeId = null)
    {
        $url = $this->determineOpsUrl('ops_alias_gateway', $storeId);

        if ($this->getConfigData('ops_alias_gateway_test') != '') {
            if ($this->getMode($storeId) == Netresearch_OPS_Model_Source_Mode::TEST) {
                return $this->getConfigData('ops_alias_gateway_test');
            } elseif ($this->getMode($storeId) == Netresearch_OPS_Model_Source_Mode::PROD) {
                $url = str_replace('ncol/prod/', '', $url);
            }
        }

        return $url;
    }

    /**
     * @param null $storeId
     * @param bool $admin
     * @return mixed
     */
    public function getCcSaveAliasUrl($storeId = null, $admin = false)
    {
        $params = array(
            '_secure' => $this->isCurrentlySecure()
        );
        if (false === is_null($storeId)) {
            $params['_store'] = $storeId;
        }
        if ($admin) {
            return Mage::getModel('adminhtml/url')->getUrl('ops/admin/saveAlias', $params);
        } else {
            return Mage::getUrl('ops/alias/save', $params);
        }
    }

    /**
     * get deeplink to transaction view at Ingenico ePayments
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return string
     */
    public function getOpsAdminPaymentUrl($payment)
    {
        return '';
    }

    public function isCurrentlySecure()
    {
        return Mage::app()->getStore()->isCurrentlySecure();
    }

    public function getIntersolveBrands($storeId = null)
    {
        $result = array();
        $brands = Mage::getStoreConfig('payment/ops_interSolve/brands', $storeId);
        if (null !=$brands) {
            $result = unserialize($brands);
        }

        return $result;
    }

    /**
     * @param int $storeId
     *
     * @return string[][]
     */
    public function getFlexMethods($storeId = null)
    {
        $result = array();
        $methods = Mage::getStoreConfig('payment/ops_flex/methods', $storeId);
        if (null !=$methods) {
            $result = unserialize($methods);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllCcTypes()
    {
        return explode(',', Mage::getStoreConfig('payment/ops_cc/availableTypes'));
    }


    /**
     * @return array
     */
    public function getAllDcTypes()
    {
        return explode(',', Mage::getStoreConfig('payment/ops_dc/availableTypes'));
    }

    /**
     * get keys of parameters to be shown in scoring information block
     *
     * @return array
     */
    public function getAdditionalScoringKeys()
    {
        return array(
            'AAVCHECK',
            'ACCEPTANCE',
            'CVCCHECK',
            'CCCTY',
            'IPCTY',
            'NBREMAILUSAGE',
            'NBRIPUSAGE',
            'NBRIPUSAGE_ALLTX',
            'NBRUSAGE',
            'VC',
            'CARDNO',
            'ED',
            'CN'
        );
    }

    /**
     * @return bool
     */
    public function getSendInvoice()
    {
        return (bool)(int)Mage::getStoreConfig('payment_services/ops/send_invoice');
    }

    /**
     * if payment method with given code is enabled for backend payments
     *
     * @param string $code Payment method code
     *
     * @return bool
     */
    public function isEnabledForBackend($code, $storeId = 0)
    {
        return (bool)(int)Mage::getStoreConfig('payment/' . $code . '/backend_enabled', $storeId);
    }

    /**
     * @return bool
     */
    public function isAliasInfoBlockEnabled()
    {
        return (bool)(int)Mage::getStoreConfig('payment/ops_cc/show_alias_manager_info_for_guests');
    }

    /**
     * return config value for Alias Manager enabled
     *
     * @param $code
     * @param $storeId
     *
     * @return bool
     */
    public function isAliasManagerEnabled($code, $storeId = null)
    {
        return (bool)Mage::getStoreConfig('payment/' . $code . '/active_alias', $storeId);
    }

    /**
     * return configured text for alias usage parameter for new alias creation
     *
     * @param $code
     * @param null $storeId
     *
     * @return string
     */
    public function getAliasUsageForNewAlias($code, $storeId = null)
    {
        return Mage::getStoreConfig('payment/' . $code . '/alias_usage_for_new_alias', $storeId);
    }

    /**
     * return configured text for alias usage parameter when using a existing alias
     *
     * @param $code
     * @param null $storeId
     *
     * @return string
     */
    public function getAliasUsageForExistingAlias($code, $storeId = null)
    {
        return Mage::getStoreConfig('payment/' . $code . '/alias_usage_for_existing_alias', $storeId);
    }


    /**
     * getter for usage of order reference
     */
    public function getOrderReference($storeId = null)
    {
        return $this->getConfigData('redirectOrderReference', $storeId);
    }

    /**
     * @param int $storeId - the store id to use
     *
     * @return int whether the QuoteId should be shown in
     * the order grid (1) or not (0)
     */
    public function getShowQuoteIdInOrderGrid($storeId = null)
    {
        return $this->getConfigData('showQuoteIdInOrderGrid', $storeId);
    }

    /**
     * Check if the current environment is frontend or backend
     *
     * @return boolean
     */
    public function isFrontendEnvironment()
    {
        return (false === Mage::app()->getStore()->isAdmin());
    }

    /**
     * getter for the accept route for payments
     *
     * @return string
     */
    public function getAcceptRedirectRoute()
    {
        return self::OPS_CONTROLLER_ROUTE_PAYMENT . 'accept';
    }

    /**
     * getter for the cancel route for payments
     *
     * @return string
     */
    public function getCancelRedirectRoute()
    {
        return self::OPS_CONTROLLER_ROUTE_PAYMENT . 'cancel';
    }

    /**
     * getter for the decline route for payments
     *
     * @return string
     */
    public function getDeclineRedirectRoute()
    {
        return self::OPS_CONTROLLER_ROUTE_PAYMENT . 'decline';
    }

    /**
     * getter for the decline route for payments
     *
     * @return string
     */
    public function getExceptionRedirectRoute()
    {
        return self::OPS_CONTROLLER_ROUTE_PAYMENT . 'exception';
    }


    /**
     * @param $operation
     * @return mixed
     */
    public function getMethodsRequiringAdditionalParametersFor($operation)
    {
        return Mage::getStoreConfig('payment/additional_params_required/' . $operation);
    }


    /**
     * returns the url for the maintenance api calls
     *
     * @param null $storeId
     *
     * @return string - the url for the maintenance api calls
     */
    public function getDirectLinkMaintenanceApiPath($storeId = null)
    {
        return $this->determineOpsUrl('directlink_maintenance_api', $storeId);
    }

    /**
     * getter for the iDeal issuers
     *
     * @return array
     */
    public function getIDealIssuers()
    {
        return Mage::getStoreConfig('payment/ops_iDeal/issuer');
    }

    /**
     * whether extra parameters needs to be passed to Ingenico ePayments or not
     *
     * @param null $storeId
     *
     * @return bool - true if it's enabled, false otherwise
     */
    public function canSubmitExtraParameter($storeId = null)
    {
        return (bool)$this->getConfigData('submitExtraParameters', $storeId);
    }

    public function getParameterLengths()
    {
        return $this->getConfigData('paramLength');
    }

    /**
     * @return Simple_Xml
     */
    public function getFrontendFieldMapping()
    {
        return $this->getConfigData('frontendFieldMapping');

    }

    /**
     * @return mixed
     */
    public function getValidationUrl()
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'validate',
            array('_secure' => $this->isCurrentlySecure(), '_nosid' => true)
        );
    }

    /**
     * @param null $storeId
     * @return Simple_Xml
     */
    public function getInlineOrderReference($storeId = null)
    {
        return $this->getConfigData('inlineOrderReference', $storeId);
    }

    /**
     * Returns the mode of the store
     *
     * @param null $storeId
     *
     * @return string | mode (custom, prod, test) for the store
     * @see Netresearch_OPS_Model_Source_Mode
     */
    public function getMode($storeId = null)
    {
        return $this->getConfigData('mode', $storeId);
    }

    protected function getOpsUrl($path)
    {
        return $this->getConfigData('url/' . $path);
    }

    /**
     * Will always return the base url (https://secure.domain.tld/ncol/[test, prod]) for the mode of the store
     *
     * @return string Url depending of the mode - will be empty for custom mode
     */
    public function getOpsBaseUrl($storeId = null)
    {
        return $this->getOpsUrl('base_' . $this->getMode($storeId));
    }

    /**
     * Returns the default url for the given gateway, depending on the mode, that is set for the the given store
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return string
     */
    public function getDefaultOpsUrl($path, $storeId = null)
    {
        return $this->getOpsBaseUrl($storeId) . $this->getOpsUrl($path);
    }

    /**
     * Returns the url for the given gateway depending on the set mode for the given store
     *
     * @param      $path
     * @param null $storeId
     *
     * @return string
     */
    public function determineOpsUrl($path, $storeId = null)
    {
        if ($this->getMode($storeId) === Netresearch_OPS_Model_Source_Mode::CUSTOM) {
            return $this->getConfigData($path, $storeId);
        } else {
            return $this->getDefaultOpsUrl($path, $storeId);
        }
    }

    /**
     * @param null $storeId
     * @return Simple_Xml
     */
    public function getTemplateIdentifier($storeId = null)
    {
        return $this->getConfigData('template_identifier', $storeId);
    }

    /**
     * @param null $storeId
     * @return Simple_Xml
     */
    public function getResendPaymentInfoIdentity($storeId = null)
    {
        return $this->getConfigData('resendPaymentInfo_identity', $storeId);
    }

    /**
     * @param null $storeId
     * @return Simple_Xml
     */
    public function getResendPaymentInfoTemplate($storeId = null)
    {
        return $this->getConfigData('resendPaymentInfo_template', $storeId);
    }


    /**
     * @param null $storeId
     * @return Simple_Xml
     */
    public function getPayPerMailTemplate($storeId = null)
    {
        return $this->getConfigData('payPerMail_template', $storeId);
    }

    /**
     * @return Simple_Xml
     */
    public function getStateRestriction()
    {
        return $this->getConfigData('ops_state_restriction');
    }

    /**
     * @param $params
     * @param null $storeId
     * @return mixed
     */
    public function getPaymentRetryUrl($params, $storeId = null)
    {
        return Mage::getUrl(
            self::OPS_CONTROLLER_ROUTE_PAYMENT . 'retry',
            array('_secure' => true, '_nosid' => true, '_query' => $params, '_store' => $storeId)
        );
    }

    /**
     * Will return the state of the deviceFingerPrinting:
     * - true if activated in config
     * - false if deactivated in config
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function getDeviceFingerPrinting($storeId = null)
    {
        return (bool)$this->getConfigData('device_fingerprinting', $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getTransActionTimeout($storeId = null)
    {
        return (int)$this->getConfigData('ops_rtimeout', $storeId);
    }

    /**
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function getCreditDebitSplit($storeId = null)
    {
        return (bool)$this->getConfigData('creditdebit_split', $storeId);
    }

    /**
     * @return array
     */
    public function getAllRecurringCcTypes()
    {
        return explode(',', Mage::getStoreConfig('payment/ops_recurring_cc/availableTypes'));
    }

    /**
     * @return array
     */
    public function getAcceptedRecurringCcTypes()
    {
        return explode(',', Mage::getStoreConfig('payment/ops_recurring_cc/acceptedTypes'));
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getMonthlyBillingDay($storeId = null)
    {
        return Mage::getStoreConfig(self::OPS_PAYMENT_PATH . 'billing_day_month', $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getWeeklyBillingDay($storeId = null)
    {
        return Mage::getStoreConfig(self::OPS_PAYMENT_PATH . 'billing_day_week', $storeId);
    }

    /**
     * @param null $storeId
     * @return Simple_Xml
     */
    public function getSuspendSubscriptionTemplate($storeId = null)
    {
        return $this->getConfigData('suspendSubscription_template', $storeId);
    }

    /**
     * @param null $storeId
     * @return Simple_Xml
     */
    public function getSuspendSubscriptionIdentity($storeId = null)
    {
        return $this->getConfigData('suspendSubscription_identity', $storeId);
    }



}

