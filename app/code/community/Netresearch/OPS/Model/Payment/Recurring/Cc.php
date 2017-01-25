<?php
/**
 * Netresearch OPS
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
 * Implementation of the Cc payment method implementing subscription manager functionality
 * via Magentos recurring profiles
 *
 * @category Payment method
 * @package  Netresearch OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Model_Payment_Recurring_Cc
    extends Netresearch_OPS_Model_Payment_Cc
    implements Mage_Payment_Model_Recurring_Profile_MethodInterface
{
    const CODE = 'ops_recurring_cc';
    protected $_code = self::CODE;

    protected $_canFetchTransactionInfo = false;
    protected $_canManageRecurringProfiles = true;
    protected $_canUseInternal = false;

    /** info source path */
    protected $_infoBlockType = 'ops/info_recurringCc';

    /** @var string $_formBlockType define a specific form block */
    protected $_formBlockType = 'ops/form_recurringCc';

    protected $parameterModel = null;
    protected $subscriptionManager = null;

    /**
     * @return Netresearch_OPS_Model_Subscription_Manager
     */
    public function getSubscriptionManager()
    {
        if (null === $this->subscriptionManager) {
            $this->subscriptionManager = Mage::getModel('ops/subscription_manager');
        }

        return $this->subscriptionManager;
    }

    /**
     * @param Netresearch_OPS_Model_Subscription_Manager $subscriptionManager
     *
     * @returns $this
     */
    public function setSubscriptionManager($subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;

        return $this;
    }


    /**
     * @return Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag
     */
    public function getParameterModel()
    {
        if (null === $this->parameterModel) {
            $this->parameterModel = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        }

        return $this->parameterModel;
    }

    /**
     * @param Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag $parameterModel
     *
     * @returns $this
     */
    public function setParameterModel($parameterModel)
    {
        $this->parameterModel = $parameterModel;

        return $this;
    }


    /**
     * Validate data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     *
     * @throws Mage_Core_Exception
     */
    public function validateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {
        if ($profile->getState() === Mage_Sales_Model_Recurring_Profile::STATE_UNKNOWN) {
            $this->invokeRequestParamValidation(
                $this->getParameterModel()->collectProfileParameters($profile)->toArray()
            );
        }
    }

    /**
     * Submits the trial subscription to the Ingenico ePayments webservice
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info              $paymentInfo
     *
     */
    protected function submitTrialSubscription(
        Mage_Payment_Model_Recurring_Profile $profile,
        Mage_Payment_Model_Info $paymentInfo
    ) 
    {
        if ($profile->getTrialPeriodUnit()) {
            $requestParams = $this->getParameterModel()->collectAllParametersForTrial($paymentInfo, $profile);
            $this->getParameterModel()->unsetData();
            $response = $this->getDirectLinkHelper()->performDirectLinkRequest(
                $profile->getQuote(), $requestParams, $profile->getQuote()->getStoreId()
            );

            if ($this->getPaymentHelper()->isPaymentFailed($response['STATUS'])
                || $response['creation_status'] == Netresearch_OPS_Model_Subscription_Manager::CREATION_FAILED
            ) {
                Mage::throwException($this->getDataHelper()->__('Placing of trial subscription transaction failed'));
            }
        }
    }

    /**
     * Submit to the gateway
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info              $paymentInfo
     */
    public function submitRecurringProfile(
        Mage_Payment_Model_Recurring_Profile $profile,
        Mage_Payment_Model_Info $paymentInfo
    ) 
    {
        $this->performPreDirectLinkCallActions($profile->getQuote(), $paymentInfo);

        $this->submitTrialSubscription($profile, $paymentInfo);

        $this->submitRegularSubscription($profile, $paymentInfo);

        $this->submitInitialFee($profile, $paymentInfo);
    }

    /**
     * Fetch details
     *
     * @param string        $referenceId
     * @param Varien_Object $result
     */
    public function getRecurringProfileDetails($referenceId, Varien_Object $result)
    {
        Mage::throwException('Fetching profile details from Ingenico ePayments not supported');
    }

    /**
     * Check whether can get recurring profile details
     *
     * @return bool
     */
    public function canGetRecurringProfileDetails()
    {
        // querying the subscription status via API is not possible
        return false;
    }

    /**
     * Update data
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfile(Mage_Payment_Model_Recurring_Profile $profile)
    {
        Mage::throwException('Function not supported');
    }

    /**
     * Manage status update according to given new state on the profile
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    public function updateRecurringProfileStatus(Mage_Payment_Model_Recurring_Profile $profile)
    {
        /** @var Mage_Sales_Model_Recurring_Profile $profile */
        switch ($profile->getNewState()) {
            case Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE:
                if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                    $this->addAdminNotice(
                        'To actually activate the subscription an update in the Ingenico ePayments backend is needed.'
                    );
                } else {
                    Mage::throwException(
                        $this->getDataHelper()->__(
                            'Automatic activation not possible. Please contact our support team.'
                        )
                    );
                }
                break;
            case Mage_Sales_Model_Recurring_Profile::STATE_CANCELED:
                if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                    $this->addAdminNotice(
                        'To actually cancel the subscription an update in the Ingenico ePayments backend is needed.'
                    );
                } else {
                    $this->sendSuspendMail($profile);
                }
                break;
            case Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED:
                if (Mage::getSingleton('admin/session')->isLoggedIn()) {
                    $this->addAdminNotice(
                        'To actually suspend the subscription an update in the Ingenico ePayments backend is needed.'
                    );
                } else {
                    $this->sendSuspendMail($profile);
                }
                break;
            case Mage_Sales_Model_Recurring_Profile::STATE_EXPIRED:
                Mage::throwException('Expire function not implemented!');
                break;
            default:
                $message = $this->getDataHelper()->__('Action for state %s not supported', $profile->getNewState());
                Mage::throwException($message);
                break;
        }
    }

    /**
     * Adds translated message to admin session as notice
     *
     * @param string $message
     */
    protected function addAdminNotice($message)
    {
        Mage::getSingleton('adminhtml/session')->addNotice($this->getDataHelper()->__($message));
    }

    /**
     *
     * @param Mage_Sales_Model_Recurring_Profile $profile
     *
     * @throws Mage_Core_Exception
     */
    protected function sendSuspendMail($profile)
    {
        $session = Mage::getSingleton('customer/session');
        $mailModel = Mage::getModel('ops/payment_features_paymentEmail');
        if ($session->getCustomer()->getId() != $profile->getCustomerId()) {
            // prevent access to subscriptions not of the customers account
            Mage::throwException(
                $this->getDataHelper()->__('You are not allowed to suspend this subscription!')
            );
        }
        $customer = Mage::getModel('customer/customer')->load($profile->getCustomerId());
        $result = $mailModel->sendSuspendSubscriptionMail($profile, $customer);
        if ($result) {
            // mail successfully sent
            $profile->setNewState($profile::STATE_PENDING);
            $profile->setOverrideState(true);
            $session->addSuccess(
                $this->getDataHelper()
                     ->__(
                         'Your suspend request was successfully sent. A copy of the email will be sent to your address.'
                     )
            );
        } else {
            // sending the mail failed
            Mage::throwException(
                $this->getDataHelper()
                     ->__('Could not send suspend mail, please try again or contact our support directly.')
            );
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
        if ($quote && !$quote->isNominal()) {
            // allow only nominal quotes
            return false;
        }

        return parent::isAvailable($quote);

    }

    public function hasBrandAliasInterfaceSupport($payment = null)
    {
        // only support inline, since we need the alias
        return true;
    }

    public function getOrderPlaceRedirectUrl($payment = null)
    {
        if ('' == $this->getOpsHtmlAnswer($payment)) {
            // Prevent redirect on cc payment
            return false;
        } else {
            // 3ds redirect
            return Mage::getModel('ops/config')->get3dSecureRedirectUrl();

        }
    }

    public function isZeroAmountAuthorizationAllowed($storeId = null)
    {
        return false;
    }

    public function getBrandsForAliasInterface()
    {
        return $this->getConfigData('availableTypes');
    }

    /**
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Payment_Model_Info              $paymentInfo
     *
     * @throws Mage_Core_Exception
     */
    protected function submitRegularSubscription(Mage_Payment_Model_Recurring_Profile $profile,
        Mage_Payment_Model_Info $paymentInfo
    ) 
    {
        $requestParams = $this->getParameterModel()->collectAllParameters($paymentInfo, $profile);
        $this->getParameterModel()->unsetData();
        $response = $this->getDirectLinkHelper()->performDirectLinkRequest(
            $profile->getQuote(), $requestParams, $profile->getQuote()->getStoreId()
        );

        if ($this->getPaymentHelper()->isPaymentFailed($response['STATUS'])
            || $response['creation_status'] == Netresearch_OPS_Model_Subscription_Manager::CREATION_FAILED
        ) {
            Mage::throwException($this->getDataHelper()->__('Placing of subscription transaction failed'));
        }

        $this->getSubscriptionManager()->processSubscriptionFeedback($response, $profile);
    }

    protected function submitInitialFee(
        Mage_Payment_Model_Recurring_Profile $profile,
        Mage_Payment_Model_Info $paymentInfo
    ) 
    {
        /** @var $profile Mage_Sales_Model_Recurring_Profile */
        if ($profile->getInitAmount() > 0) {
            $order = $this->getSubscriptionManager()->createInitialOrder($profile);

            $requestParams = $this->getParameterModel()
                                  ->collectAllParametersForInitialFee($paymentInfo, $profile, $order);
            $this->getParameterModel()->unsetData();
            try{

                $response = $this->getDirectLinkHelper()->performDirectLinkRequest(
                    $profile->getQuote(), $requestParams, $profile->getQuote()->getStoreId()
                );
            } catch (Exception $e){
                Mage::logException($e);
            }

            $this->getSubscriptionManager()->processSubscriptionFeedback($response, $profile, $order);

        }

    }


}