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
 * Parameter.php
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag extends Varien_Object
{
    /**
     * Period units from Ingenico
     */
    const PERIOD_UNIT_WEEK = 'ww';
    const PERIOD_UNIT_DAY = 'd';
    const PERIOD_UNIT_MONTH = 'm';

    protected $requestHelper = null;
    protected $config = null;
    protected $quoteHelper = null;
    protected $dataHelper = null;
    protected $subscriptionHelper = null;

    /**
     * @return Netresearch_OPS_Helper_Subscription
     */
    public function getSubscriptionHelper()
    {
        if (null === $this->subscriptionHelper) {
            $this->subscriptionHelper = Mage::helper('ops/subscription');
        }

        return $this->subscriptionHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Subscription $subscriptionHelper
     *
     * @returns $this
     */
    public function setSubscriptionHelper($subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;

        return $this;
    }

    /**
     * @return Netresearch_OPS_Helper_Data
     */
    public function getDataHelper()
    {
        if (null === $this->dataHelper) {
            $this->dataHelper = Mage::helper('ops');
        }

        return $this->dataHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Data $dataHelper
     *
     * @returns $this
     */
    public function setDataHelper($dataHelper)
    {
        $this->dataHelper = $dataHelper;

        return $this;
    }

    /**
     * @return Netresearch_OPS_Helper_Quote
     */
    public function getQuoteHelper()
    {
        if (null === $this->quoteHelper) {
            $this->quoteHelper = Mage::helper('ops/quote');
        }

        return $this->quoteHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Quote $quoteHelper
     *
     * @returns $this
     */
    public function setQuoteHelper($quoteHelper)
    {
        $this->quoteHelper = $quoteHelper;

        return $this;
    }

    /**
     * @return Netresearch_OPS_Model_Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->config = Mage::getModel('ops/config');
        }

        return $this->config;
    }

    /**
     * @param Netresearch_OPS_Model_Config $config
     *
     * @returns $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return Netresearch_OPS_Helper_Payment_Request
     */
    public function getRequestHelper()
    {
        if (null === $this->requestHelper) {
            $this->requestHelper = Mage::helper('ops/payment_request');
        }

        return $this->requestHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Payment_Request $requestHelper
     *
     * @returns $this
     */
    public function setRequestHelper($requestHelper)
    {
        $this->requestHelper = $requestHelper;

        return $this;
    }

    /**
     * Maps the Magento recurring profile units to the Ingenico ePayments ones
     *
     * @param string $unit
     *
     * @return string
     */
    protected function mapUnit($unit)
    {
        switch ($unit) {
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_DAY:
                return self::PERIOD_UNIT_DAY;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_MONTH:
                return self::PERIOD_UNIT_MONTH;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_WEEK:
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_SEMI_MONTH:
                return self::PERIOD_UNIT_WEEK;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_YEAR:
                return self::PERIOD_UNIT_MONTH;
            default:
                return '';
        }
    }

    /**
     * Some period units are not supported by default by Ingenico, therefore we must adjust the frequency for
     * the following period units to match the MONTH unit:
     *
     * Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_SEMI_MONTH
     * Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_YEAR
     *
     * @param string $unit
     * @param int    $frequency
     *
     * @return int adjusted frequency
     */
    protected function adjustFrequencyToUnitSpecialCases($unit, $frequency)
    {
        switch ($unit) {
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_SEMI_MONTH:
                $frequency *= 2;
                break;
            case Mage_Payment_Model_Recurring_Profile::PERIOD_UNIT_YEAR:
                $frequency *= 12;
                break;
            default:
                break;
        }

        return $frequency;
    }

    /**
     * Collects parameters specific to customer (addresses) and quote
     *
     * @param Mage_Payment_Model_Info $paymentInfo
     *
     * @return Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag
     */
    public function collectPaymentParameters(Mage_Payment_Model_Info $paymentInfo)
    {
        $this->setData('CN', $paymentInfo->getAdditionalInformation('CC_CN'))
             ->setData('ALIAS', $paymentInfo->getAdditionalInformation('alias'))
             ->setData('BRAND', $paymentInfo->getAdditionalInformation('CC_BRAND'))
             ->setData('CURRENCY', $this->getQuoteHelper()->getQuoteCurrency($paymentInfo->getQuote()))
             ->setData('OPERATION', Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION)
             ->setData('ORIG', $this->getDataHelper()->getModuleVersionString())
             ->setData('REMOTE_ADDR', $paymentInfo->getQuote()->getRemoteIp());


        if ($paymentInfo->getMethodInstance()->getConfigData('enabled_3dsecure')) {
            $this->addData(
                array(
                    'FLAG3D'          => 'Y',
                    'WIN3DS'          => Netresearch_OPS_Model_Payment_Abstract::OPS_DIRECTLINK_WIN3DS,
                    'LANGUAGE'        => Mage::app()->getLocale()->getLocaleCode(),
                    'HTTP_ACCEPT'     => '*/*',
                    'HTTP_USER_AGENT' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)',
                    'ACCEPTURL'       => $this->getConfig()->getAcceptUrl(),
                    'DECLINEURL'      => $this->getConfig()->getDeclineUrl(),
                    'EXCEPTIONURL'    => $this->getConfig()->getExceptionUrl(),
                )
            );
        }

        return $this;
    }

    /**
     * Collects profile specific request parameters
     *
     * @see http://payment-services.ingenico.com/int/en/ogone/support/guides/integration%20guides/subscription-manager/via-e-commerce-and-directlink
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param boolean                              $trial - if the array should contain the parameters necessary for the trial period
     *
     * @return $this
     */
    public function collectProfileParameters(Mage_Payment_Model_Recurring_Profile $profile, $trial = false)
    {
        /** @var Mage_Sales_Model_Recurring_Profile $profile */

        // get subscription amount from profile values
        list($subscriptionAmount) = $this->getProfileValues($profile, $trial);

        // determine dates and adjust period values depending on trial parameter
        list($startDate, $endDate, $periodUnit, $periodFrequency) = $this->generateDates($profile, $trial);

        $subOrderId = $this->getSubscriptionHelper()->generateSubscriptionId($profile, $trial);

        $this->setData('SUB_COM', $profile->getScheduleDescription())
             ->setData('SUB_ORDERID', $subOrderId)
             ->setData('SUB_PERIOD_NUMBER', $periodFrequency)
             ->setData('SUB_PERIOD_UNIT', $periodUnit)
             ->setData('SUB_STARTDATE', $startDate->format('d/m/Y'))
             ->setData('SUB_ENDDATE', $endDate ? $endDate->format('d/m/Y') : '')
             ->setData('SUB_STATUS', 1)
             ->setData('SUBSCRIPTION_ID', $subOrderId)
             ->setData('ORDERID', $subOrderId)
             ->setData('SUB_AMOUNT', $this->getDataHelper()->getAmount($subscriptionAmount))
            // amount is always 0 for subscription transactions
             ->setData('AMOUNT', 0)
             ->setData(
                 'SUB_PERIOD_MOMENT',
                 $this->getSubscriptionHelper()->getBillingDayForPeriodUnit($periodUnit, $profile->getStoreId())
             );

        // add OWNER* and ECOM_BILLTO_* and ECOM_SHIPTO_* parameters
        $this->collectAddressParameters($profile);

        return $this;
    }


    /**
     * Calculates the end date of the profile from the start date, frequency, period unit and maximum cycles
     *
     * @param DateTime $startDate  | start date of the subscription
     * @param string   $periodUnit | unit of the frequency (d, w, m)
     * @param int      $frequency  | every nth unit the customer will be charged
     * @param int      $maxCycles  | maximum amount of cycles
     *
     * @return DateTime|null - the end date or null if no maximum cycle amount is set
     */
    public function calculateEndDate(DateTime $startDate, $periodUnit, $frequency, $maxCycles)
    {
        if (!$this->isMappedUnit($periodUnit)) {
            $frequency = $this->adjustFrequencyToUnitSpecialCases($periodUnit, $frequency);
            $periodUnit = $this->mapUnit($periodUnit);
        }

        $endDate = null;
        if ($maxCycles) {
            $endDate = clone $startDate;
            if ($endDate->format('d') > 28 && $periodUnit != self::PERIOD_UNIT_DAY) {
                $endDate->sub(new DateInterval('P3D'));
            }

            $dateDiff = $frequency * $maxCycles;
            // fix period unit for week, since the payment provider requests a strange unit
            $periodUnit = $periodUnit == self::PERIOD_UNIT_WEEK ? 'w' : $periodUnit;

            $endDate->add(new DateInterval('P' . $dateDiff . strtoupper($periodUnit)));
        }
        return $endDate;
    }

    /**
     * Tests if the given unit is already mapped to the Ingenico types
     *
     * @param string $unit - unit to check
     *
     * @return bool
     */
    protected function isMappedUnit($unit)
    {
        return in_array(
            $unit, array(
            self::PERIOD_UNIT_DAY,
            self::PERIOD_UNIT_WEEK,
            self::PERIOD_UNIT_MONTH
            )
        );
    }

    /**
     * Collects all parameters from the given objects and returns the parameter array
     *
     * @param Mage_Payment_Model_Info              $paymentInfo
     * @param Mage_Payment_Model_Recurring_Profile $profile
     *
     * @return mixed[] - utf8_encoded request parameters
     */
    public function collectAllParameters(Mage_Payment_Model_Info $paymentInfo,
        Mage_Payment_Model_Recurring_Profile $profile
    ) 
    {
        $this->collectProfileParameters($profile)
             ->collectPaymentParameters($paymentInfo);

        return $this->encodeValues()->toArray();
    }

    /**
     * Collects all parameters relevant for the trial period from the given objects and returns the parameter array
     *
     * @param Mage_Payment_Model_Info              $paymentInfo
     * @param Mage_Payment_Model_Recurring_Profile $profile
     *
     * @return mixed[] - utf8_encoded request parameters
     */
    public function collectAllParametersForTrial(Mage_Payment_Model_Info $paymentInfo,
        Mage_Payment_Model_Recurring_Profile $profile
    ) 
    {
        $this->collectProfileParameters($profile, true)
             ->collectPaymentParameters($paymentInfo, true);

        return $this->encodeValues()->toArray();
    }

    /**
     * collects all relevant parameters for the initial fee
     *
     * @param Mage_Payment_Model_Info              $paymentInfo
     * @param Mage_Payment_Model_Recurring_Profile $profile
     * @param Mage_Sales_Model_Order               $order
     *
     * @return string[] - utf8 encoded request parameters
     */
    public function collectAllParametersForInitialFee(Mage_Payment_Model_Info $paymentInfo,
        Mage_Payment_Model_Recurring_Profile $profile,
        Mage_Sales_Model_Order $order
    ) 
    {
        /** @var $profile Mage_Sales_Model_Recurring_Profile */
        $this->collectPaymentParameters($paymentInfo)->collectAddressParameters($profile);

        $this->setData('AMOUNT', $this->getDataHelper()->getAmount($profile->getInitAmount()));
        $this->setData('ORDERID', Mage::helper('ops/order')->getOpsOrderId($order));

        return $this->encodeValues()->toArray();
    }

    /**
     * utf8_decodes all parameters
     *
     * @return $this
     */
    protected function encodeValues()
    {
        foreach ($this->getData() as $key => $value) {
            $this->setData($key, utf8_decode($value));
        }

        return $this;
    }

    /**
     * Determines base values for the subscription depending on the trial parameter
     *
     * @param Mage_Sales_Model_Recurring_Profile   $profile
     * @param $trial - if the values for the trial subscription should be used or not
     *
     * @return string[] - containing the following:
     *                  [0] => amount for the subscription,
     *                  [1] => period Unit,
     *                  [2] => periodFrequency,
     *                  [3] => max
     */
    protected function getProfileValues($profile, $trial)
    {
        // subscription equals shipping for the product + the product amount+tax itself
        if ($trial) {
            $subscriptionAmount = $profile->getTrialBillingAmount() + $profile->getShippingAmount();
            $periodUnit = $this->mapUnit($profile->getTrialPeriodUnit());
            $periodFrequency = $this->adjustFrequencyToUnitSpecialCases(
                $profile->getTrialPeriodUnit(), $profile->getTrialPeriodFrequency()
            );
            $maxCycles = $profile->getTrialPeriodMaxCycles();

        } else {
            $subscriptionAmount
                = $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount();
            $periodUnit = $this->mapUnit($profile->getPeriodUnit());
            $periodFrequency = $this->adjustFrequencyToUnitSpecialCases(
                $profile->getPeriodUnit(), $profile->getPeriodFrequency()
            );
            $maxCycles = $profile->getPeriodMaxCycles();

        }

        return array($subscriptionAmount, $periodUnit, $periodFrequency, $maxCycles);
    }

    /**
     * Determines the start and end date from the given values.
     *
     * @param Mage_Sales_Model_Recurring_Profile   $profile
     * @param                                      $trial
     *
     * @return string[] - containing the following:
     *                  0 => updated start date
     *                  1 => updated enddate
     *                  2 => updated period unit
     *                  3 => updated period frequency
     */
    protected function generateDates($profile, $trial)
    {
        // get profile values - subscription amount is not needed here
        list(, $periodUnit, $periodFrequency, $maxCycles) = $this->getProfileValues($profile, $trial);

        if (!$profile->getTrialPeriodUnit() && !$trial || $trial) {
            // if we collect the trial parameters, or if we don't have a trial period at all
            $startDate = new DateTime($profile->getStartDateTime());
            $endDate = $this->calculateEndDate(
                $startDate, $periodUnit, $periodFrequency, $maxCycles
            );
        } else {
            // if we collect the regular subscription and a trial is existent
            $trialStartDate = new DateTime($profile->getStartDateTime());
            $trialPeriodUnit = $this->mapUnit($profile->getTrialPeriodUnit());
            $trialPeriodFrequency = $this->adjustFrequencyToUnitSpecialCases(
                $profile->getTrialPeriodUnit(), $profile->getTrialPeriodFrequency()
            );
            // calculate trial end date and use it as start date for the regular subscription
            $trialEndDate = $this->calculateEndDate(
                $trialStartDate, $trialPeriodUnit, $trialPeriodFrequency, $profile->getTrialPeriodMaxCycles()
            );

            $startDate = clone $trialEndDate;
            $periodUnit = $this->mapUnit($profile->getPeriodUnit());
            $periodFrequency = $this->adjustFrequencyToUnitSpecialCases(
                $profile->getPeriodUnit(), $profile->getPeriodFrequency()
            );
            $maxCycles = $profile->getPeriodMaxCycles();
            $endDate = $this->calculateEndDate(
                $trialEndDate, $periodUnit, $periodFrequency, $maxCycles
            );
        }

        return array($startDate, $endDate, $periodUnit, $periodFrequency);
    }

    /**
     * Add address data to the bag if it is available
     *
     * @param Mage_Payment_Model_Recurring_Profile $profile
     */
    protected function collectAddressParameters(Mage_Payment_Model_Recurring_Profile $profile)
    {
        /** @var $profile Mage_Sales_Model_Recurring_Profile */
        if ($profile->getQuote()) {
            $this->addData(
                $this->getRequestHelper()->getOwnerParams(
                    $profile->getQuote()->getBillingAddress(), $profile->getQuote()
                )
            )->addData($this->getRequestHelper()->extractBillToParameters($profile->getQuote()->getBillingAddress()));

            if ($profile->getQuote()->getShippingAddress()) {
                $this->addData(
                    $this->getRequestHelper()->extractShipToParameters($profile->getQuote()->getShippingAddress())
                );
            }
        }
    }
}
