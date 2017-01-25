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
 * Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBagTest.php
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Model_Payment_Recurring_Cc_ParameterBagTest extends EcomDev_PHPUnit_Test_Case
{


    public function testcalculateEndDate()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        $startDate = new DateTime('01.01.2015');
        $periodUnit = Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_DAY;
        $frequency = 1;
        $maxCycles = 2;

        $endDate = $subject->calculateEndDate($startDate, $periodUnit, $frequency, $maxCycles);
        $startDate = new DateTime('03.01.2015');
        $this->assertEquals($startDate->format('d.M.Y'), $endDate->format('d.M.Y'));

        $startDate = new DateTime('01.01.2015');
        $periodUnit = Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_MONTH;
        $frequency = 1;
        $maxCycles = 2;

        $endDate = $subject->calculateEndDate($startDate, $periodUnit, $frequency, $maxCycles);
        $startDate = new DateTime('01.03.2015');
        $this->assertEquals($startDate->format('d.M.Y'), $endDate->format('d.M.Y'));

        $startDate = new DateTime('31.01.2015');
        $periodUnit = Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_MONTH;
        $frequency = 1;
        $maxCycles = 2;

        $endDate = $subject->calculateEndDate($startDate, $periodUnit, $frequency, $maxCycles);
        $startDate = new DateTime('28.03.2015');
        $this->assertEquals($startDate->format('d.M.Y'), $endDate->format('d.M.Y'));

        $startDate = new DateTime('01.01.2015');
        $periodUnit = Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_WEEK;
        $frequency = 1;
        $maxCycles = 2;

        $endDate = $subject->calculateEndDate($startDate, $periodUnit, $frequency, $maxCycles);
        $startDate = new DateTime('15.01.2015');
        $this->assertEquals($startDate->format('d.M.Y'), $endDate->format('d.M.Y'));

        $endDate = $subject->calculateEndDate($startDate, $periodUnit, $frequency, '');
        $this->assertNull($endDate);
    }

    public function testHelperFunctions()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        $testString = 'abc';
        $this->assertTrue($subject->getSubscriptionHelper() instanceof Netresearch_OPS_Helper_Subscription);
        $subject->setSubscriptionHelper($testString);
        $this->assertEquals($subject->getSubscriptionHelper(), $testString);

        $this->assertTrue($subject->getQuoteHelper() instanceof Netresearch_OPS_Helper_Quote);
        $subject->setQuoteHelper($testString);
        $this->assertEquals($subject->getQuoteHelper(), $testString);

        $this->assertTrue($subject->getDataHelper() instanceof Netresearch_OPS_Helper_Data);
        $subject->setDataHelper($testString);
        $this->assertEquals($subject->getDataHelper(), $testString);

        $this->assertTrue($subject->getRequestHelper() instanceof Netresearch_OPS_Helper_Payment_Request);
        $subject->setRequestHelper($testString);
        $this->assertEquals($subject->getRequestHelper(), $testString);

        $this->assertTrue($subject->getConfig() instanceof Netresearch_OPS_Model_Config);
        $subject->setConfig($testString);
        $this->assertEquals($subject->getConfig(), $testString);
    }

    /**
     * @return Mage_Sales_Model_Recurring_Profile
     */
    protected function getTestProfile($withTrial = false)
    {
        $profile = Mage::getModel('sales/recurring_profile');
        $address = Mage::getModel('sales/quote_address');
        $address->setCity('Leipzig');
        $address->setStreet('Test 123');
        $quote = Mage::getModel('sales/quote');
        $quote->setShippingAddress($address);
        $quote->setBillingAddress($address);

        $profile->setScheduleDescription('abc')
            ->setPeriodFrequency(1)
            ->setPeriodMaxCycles(1)
            ->setPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_WEEK)
            ->setInitAmount(10)
            ->setBillingAmount(15)
            ->setTaxAmount(5)
            ->setShippingAmount(5)
            ->setStartDatetime('')
            ->setQuote($quote)
            ->setId(0);

        if ($withTrial) {
            $profile->setTrialPeriodMaxCycles(1)
                ->setTrialPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_DAY)
                ->setTrialPeriodFrequency(1)
                ->setTrialBillingAmount(15);
        }
        return $profile;
    }

    public function testCollectProfileParameters()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        $profile = $this->getTestProfile();
        $profile->setPeriodMaxCycles(0);

        $params = $subject->collectProfileParameters($profile)->toArray();

        $startDate = new DateTime();
        $this->assertArrayHasKey('SUB_STARTDATE', $params);
        $this->assertEquals($params['SUB_STARTDATE'], $startDate->format('d/m/Y'));

        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . $profile->getId();
        $this->assertArrayHasKey('SUBSCRIPTION_ID', $params);
        $this->assertEquals($id, $params['SUBSCRIPTION_ID']);

        $this->assertArrayHasKey('SUB_ORDERID', $params);
        $this->assertEquals($id, $params['SUB_ORDERID']);

        $this->assertArrayHasKey('ORDERID', $params);
        $this->assertEquals($id, $params['ORDERID']);

        $this->assertArrayHasKey('SUB_COM', $params);
        $this->assertEquals($params['SUB_COM'], 'abc');

        $this->assertEquals(0, $params['AMOUNT']);
        $subAmount = $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount();
        $this->assertEquals($subAmount, $params['SUB_AMOUNT'] / 100);

        $this->assertArrayHasKey('ECOM_SHIPTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_SHIPTO_POSTAL_CITY']);

        $this->assertArrayHasKey('ECOM_BILLTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_BILLTO_POSTAL_CITY']);

        $this->assertArrayHasKey('OWNERTOWN', $params);
        $this->assertEquals('Leipzig', $params['OWNERTOWN']);

        $this->assertArrayHasKey('SUB_ENDDATE', $params);
        $this->assertEquals('', $params['SUB_ENDDATE']);


        // test map unit and adjust frequency:

        $profile->setPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_YEAR)
            ->setPeriodFrequency(1);
        $params = $subject->collectProfileParameters($profile);

        $this->assertEquals(12, $params['SUB_PERIOD_NUMBER']);
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_MONTH, $params['SUB_PERIOD_UNIT']
        );

        $profile->setPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_SEMI_MONTH);

        $params = $subject->collectProfileParameters($profile);
        $this->assertEquals(2, $params['SUB_PERIOD_NUMBER']);
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_WEEK, $params['SUB_PERIOD_UNIT']
        );

        $profile->setPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_MONTH);

        $params = $subject->collectProfileParameters($profile);
        $this->assertEquals(1, $params['SUB_PERIOD_NUMBER']);
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_MONTH, $params['SUB_PERIOD_UNIT']
        );

        $profile->setPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_DAY);

        $params = $subject->collectProfileParameters($profile);
        $this->assertEquals(1, $params['SUB_PERIOD_NUMBER']);
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_DAY, $params['SUB_PERIOD_UNIT']
        );


        //test trial use-cases

        $profile = $this->getTestProfile(true);

        $params = $subject->collectProfileParameters($profile, true);

        $this->assertEquals(Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_DAY, $params['SUB_PERIOD_UNIT']);

        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . $profile->getId() . Netresearch_OPS_Helper_Subscription::TRIAL_SUFFIX ;
        $this->assertArrayHasKey('SUBSCRIPTION_ID', $params);
        $this->assertEquals($id, $params['SUBSCRIPTION_ID']);

        $this->assertArrayHasKey('SUB_ORDERID', $params);
        $this->assertEquals($id, $params['SUB_ORDERID']);

        $this->assertArrayHasKey('ORDERID', $params);
        $this->assertEquals($id, $params['ORDERID']);

        $this->assertEquals($profile->getTrialBillingAmount()+$profile->getShippingAmount(), $params['SUB_AMOUNT']/100);
    }

    public function testCollectPaymentParameters()
    {
        $subject = Mage::getModel('ops/payment_recurring_cc_parameterBag');

        $payment = $this->getTestPayment();

        $params = $subject->collectPaymentParameters($payment)->toArray();

        $this->assertEquals($params['CN'], 'Olaf');
        $this->assertEquals($params['ALIAS'], '123');
        $this->assertEquals($params['OPERATION'], Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION);
        $this->assertEquals($params['FLAG3D'], 'Y');
    }

    /**
     * @return Mage_Sales_Model_Quote_Payment
     */
    protected function getTestPayment()
    {
        $quote = Mage::getModel('sales/quote');
        $quote->setRemoteIp('127.0.0.1');
        $payment = Mage::getModel('sales/quote_payment');
        $payment->setAdditionalInformation('alias', '123')
            ->setAdditionalInformation('CC_CN', 'Olaf')
            ->setAdditionalInformation('CC_BRAND', 'VISA')
            ->setMethod(Netresearch_OPS_Model_Payment_Recurring_Cc::CODE)
            ->setQuote($quote);
        Mage::app()->getStore()->setConfig('payment/ops_recurring_cc/enabled_3dsecure', 1);
        return $payment;
    }

    public function testCollectAllParameters()
    {
        $subject = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        $payment = $this->getTestPayment();
        $profile = $this->getTestProfile();

        $params = $subject->collectAllParameters($payment, $profile);

        $startDate = new DateTime();
        $this->assertArrayHasKey('SUB_STARTDATE', $params);
        $this->assertEquals($startDate->format('d/m/Y'), $params['SUB_STARTDATE']);

        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . $profile->getId();
        $this->assertArrayHasKey('SUBSCRIPTION_ID', $params);
        $this->assertEquals($id, $params['SUBSCRIPTION_ID']);

        $this->assertArrayHasKey('SUB_ORDERID', $params);
        $this->assertEquals($id, $params['SUB_ORDERID']);

        $this->assertArrayHasKey('SUB_COM', $params);
        $this->assertEquals($params['SUB_COM'], 'abc');

        $this->assertEquals(0, $params['AMOUNT']);
        $subAmount = $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount();
        $this->assertEquals($subAmount, $params['SUB_AMOUNT'] / 100);

        $this->assertArrayHasKey('ECOM_SHIPTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_SHIPTO_POSTAL_CITY']);

        $this->assertArrayHasKey('ECOM_BILLTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_BILLTO_POSTAL_CITY']);

        $this->assertArrayHasKey('OWNERTOWN', $params);
        $this->assertEquals('Leipzig', $params['OWNERTOWN']);
        $this->assertEquals($params['CN'], 'Olaf');
        $this->assertEquals($params['ALIAS'], '123');
        $this->assertEquals($params['ORDERID'], $id);
        $this->assertEquals($params['OPERATION'], Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION);
        $this->assertEquals($params['FLAG3D'], 'Y');
    }

    public function testCollectAllParametersWithTrial()
    {
        $subject = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        // test dry run with trial data
        $payment = $this->getTestPayment();
        $profile = $this->getTestProfile(true);

        $params = $subject->collectAllParameters($payment, $profile);

        $startDate = new DateTime();
        $startDate->add(new DateInterval('P1D'));
        $this->assertArrayHasKey('SUB_STARTDATE', $params);
        $this->assertEquals($params['SUB_STARTDATE'], $startDate->format('d/m/Y'));

        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . $profile->getId();
        $this->assertArrayHasKey('SUBSCRIPTION_ID', $params);
        $this->assertEquals($id, $params['SUBSCRIPTION_ID']);

        $this->assertArrayHasKey('SUB_ORDERID', $params);
        $this->assertEquals($id, $params['SUB_ORDERID']);

        $this->assertArrayHasKey('SUB_COM', $params);
        $this->assertEquals($params['SUB_COM'], 'abc');

        $this->assertEquals(0, $params['AMOUNT']);
        $subAmount = $profile->getBillingAmount() + $profile->getTaxAmount() + $profile->getShippingAmount();
        $this->assertEquals($subAmount, $params['SUB_AMOUNT'] / 100);

        $this->assertArrayHasKey('ECOM_SHIPTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_SHIPTO_POSTAL_CITY']);

        $this->assertArrayHasKey('ECOM_BILLTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_BILLTO_POSTAL_CITY']);

        $this->assertArrayHasKey('OWNERTOWN', $params);
        $this->assertEquals('Leipzig', $params['OWNERTOWN']);
        $this->assertEquals($params['CN'], 'Olaf');
        $this->assertEquals($params['ALIAS'], '123');
        $this->assertEquals($params['ORDERID'], $id);
        $this->assertEquals($params['OPERATION'], Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION);
        $this->assertEquals($params['FLAG3D'], 'Y');
    }

    public function testCollectAllParametersForTrial()
    {
        $subject = Mage::getModel('ops/payment_recurring_cc_parameterBag');
        $payment = $this->getTestPayment();
        $profile = $this->getTestProfile(true);

        $params = $subject->collectAllParametersForTrial($payment, $profile);

        $startDate = new DateTime();
        $this->assertArrayHasKey('SUB_STARTDATE', $params);
        $this->assertEquals($params['SUB_STARTDATE'], $startDate->format('d/m/Y'));

        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . $profile->getId() . Netresearch_OPS_Helper_Subscription::TRIAL_SUFFIX;
        $this->assertArrayHasKey('SUBSCRIPTION_ID', $params);
        $this->assertEquals($id, $params['SUBSCRIPTION_ID']);

        $this->assertArrayHasKey('SUB_ORDERID', $params);
        $this->assertEquals($id, $params['SUB_ORDERID']);

        $this->assertArrayHasKey('SUB_COM', $params);
        $this->assertEquals($params['SUB_COM'], 'abc');

        $this->assertEquals(0, $params['AMOUNT']);
        $subAmount = $profile->getTrialBillingAmount() + $profile->getShippingAmount();
        $this->assertEquals($subAmount, $params['SUB_AMOUNT'] / 100);

        $this->assertArrayHasKey('ECOM_SHIPTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_SHIPTO_POSTAL_CITY']);

        $this->assertArrayHasKey('ECOM_BILLTO_POSTAL_CITY', $params);
        $this->assertEquals('Leipzig', $params['ECOM_BILLTO_POSTAL_CITY']);

        $this->assertArrayHasKey('OWNERTOWN', $params);
        $this->assertEquals('Leipzig', $params['OWNERTOWN']);
        $this->assertEquals($params['CN'], 'Olaf');
        $this->assertEquals($params['ALIAS'], '123');
        $this->assertEquals($params['ORDERID'], $id);
        $this->assertEquals($params['OPERATION'], Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION);
        $this->assertEquals($params['FLAG3D'], 'Y');
    }
}
