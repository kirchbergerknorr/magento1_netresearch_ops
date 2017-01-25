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
 * Subscription.php
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Helper_Subscription extends Mage_Core_Helper_Abstract
{
    const SUBSCRIPTION_PREFIX = 'SUB-';
    const TRIAL_SUFFIX = '-TRIAL';

    /**
     * Generates subscription id in the following pattern:
     *
     * SUB-subscriptionId-TRIAL
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile   - the profile itself
     * @param bool $withTrial - if you wish the trial suffix appended in case the profile has a trial subscription
     *
     * @return string
     */
    public function generateSubscriptionId(Mage_Payment_Model_Recurring_Profile $profile, $withTrial = false)
    {
        $config = Mage::getModel('ops/config');
        $devPrefix = $config->getConfigData('devprefix');

        $subscriptionId = self::SUBSCRIPTION_PREFIX . $profile->getId();
        if ($profile->getTrialPeriodUnit() && $withTrial) {
            $subscriptionId .= self::TRIAL_SUFFIX;
        }

        return $devPrefix . $subscriptionId;
    }

    /**
     * Determine if the given request parameters belong to a subscription
     *
     * @param $requestParams
     *
     * @return bool
     */
    public function isSubscriptionFeedback($requestParams)
    {
        $result = false;
        if (is_array($requestParams)
            && array_key_exists('orderID', $requestParams)
            && false != strstr($this->removeDevPrefix($requestParams['orderID']), self::SUBSCRIPTION_PREFIX)
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * Loads the recurring profile for the given ORDERID
     *
     * @param $orderId
     *
     * @throws Mage_Core_Exception if no profile could be found
     *
     * @return Mage_Sales_Model_Order
     * @throws Mage_Core_Exception
     */
    public function getProfileForSubscription($orderId)
    {
        $orderId = $this->removeDevPrefix($orderId);

        if ($this->isTrialFeedback($orderId)) {
            $orderId = substr($orderId, -0, strlen(self::TRIAL_SUFFIX));
        }
        $orderId = substr($orderId, strlen(self::SUBSCRIPTION_PREFIX));
        $profile = Mage::getModel('sales/recurring_profile')->load($orderId);
        if (!$profile->getId()) {
            Mage::throwException('Could find no subscription for id ' . $orderId);
        }

        return $profile;
    }

    /**
     * Determines via the given orderId if the feedback request was a from a trial subscription
     *
     * @param string $orderId
     *
     * @return bool
     */
    public function isTrialFeedback($orderId)
    {
        return (bool)strstr($orderId, self::TRIAL_SUFFIX);
    }

    /**
     * Determine day of billing according to the period unit of the subscription
     *
     * @param string $periodUnit @see Mage_Payment_Model_Recurring_Profile
     * @param int    $storeId
     *
     * @return int
     */
    public function getBillingDayForPeriodUnit($periodUnit, $storeId = null)
    {
        $config = Mage::getModel('ops/config');
        $day = 1;

        switch ($periodUnit) {
            case Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_MONTH:
                $day = $config->getMonthlyBillingDay($storeId);
                break;
            case Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_WEEK:
                // offset the day by one, since Magento counts from 0, but Ingenico ePayments from 1
                $day = $config->getWeeklyBillingDay($storeId) + 1;
                break;
        }

        return $day;
    }

    protected function removeDevPrefix($orderId)
    {
        $config = Mage::getModel('ops/config');
        $devPrefix = $config->getConfigData('devprefix');
        $orderId = substr($orderId, strlen($devPrefix));
        return $orderId;
    }
}