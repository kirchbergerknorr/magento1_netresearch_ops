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
 * SubscriptionTest.php
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Helper_SubscriptionTest extends EcomDev_PHPUnit_Test_Case
{


    public function testGenerateSubscriptionId()
    {
        $subject = Mage::helper('ops/subscription');
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setId(123)
            ->setTrialPeriodUnit(true);
        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . '123';
        $this->assertEquals($id, $subject->generateSubscriptionId($profile));
        $id .= Netresearch_OPS_Helper_Subscription::TRIAL_SUFFIX;
        $this->assertEquals($id, $subject->generateSubscriptionId($profile, true));
    }

    public function testIsSubscriptionFeedback()
    {
        $subject = Mage::helper('ops/subscription');
        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . '123'
            . Netresearch_OPS_Helper_Subscription::TRIAL_SUFFIX;
        $params = array(
            'orderID' => $id
        );
        $this->assertTrue($subject->isSubscriptionFeedback($params));
        $params['orderID'] = '#10123123120';
        $this->assertFalse($subject->isSubscriptionFeedback($params));

    }

    /**
     * @loadFixture profile.yaml
     */
    public function testGetProfileForSubscription()
    {
        $subject = Mage::helper('ops/subscription');

        $id = $subject::SUBSCRIPTION_PREFIX . 1;

        $profile = $subject->getProfileForSubscription($id);
        $this->assertEquals(1, $profile->getId());

        $id = $subject::SUBSCRIPTION_PREFIX . 1 . $subject::TRIAL_SUFFIX;

        $profile = $subject->getProfileForSubscription($id);
        $this->assertEquals(1, $profile->getId());
    }
    /**
     * @loadFixture profile.yaml
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Could find no subscription for id 2
     */
    public function testGetProfileForSubscriptionWithException()
    {
        $subject = Mage::helper('ops/subscription');

        $id = $subject::SUBSCRIPTION_PREFIX . 2;

        $profile = $subject->getProfileForSubscription($id);
        $this->assertEquals(1, $profile->getId());
    }

    public function testIsTrialFeedback()
    {
        $subject = Mage::helper('ops/subscription');
        $id = Netresearch_OPS_Helper_Subscription::SUBSCRIPTION_PREFIX . '123';
        $this->assertFalse($subject->isTrialFeedback($id));
        $id .= Netresearch_OPS_Helper_Subscription::TRIAL_SUFFIX;
        $this->assertTrue($subject->isTrialFeedback($id));

    }

    public function testGetBillingDayForPeriodUnit()
    {
        $subject = Mage::helper('ops/subscription');

        $this->assertEquals(1, $subject->getBillingDayForPeriodUnit('abc'));
        $this->assertEquals(1, $subject->getBillingDayForPeriodUnit(Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_WEEK));
        $this->assertEquals(15, $subject->getBillingDayForPeriodUnit(Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag::PERIOD_UNIT_MONTH));
    }


}

