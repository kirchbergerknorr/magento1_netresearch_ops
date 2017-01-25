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
 * ManagerTest.php
 *
 * @category Payment provider
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Model_Subscription_ManagerTest extends EcomDev_PHPUnit_Test_Case
{

    public function testHelperFunctions()
    {
        /** @var Netresearch_OPS_Model_Subscription_Manager $subject */
        $subject = Mage::getModel('ops/subscription_manager');
        $testString = 'abc';
        $this->assertTrue($subject->getSubscriptionHelper() instanceof Netresearch_OPS_Helper_Subscription);
        $subject->setSubscriptionHelper($testString);
        $this->assertEquals($subject->getSubscriptionHelper(), $testString);

        $this->assertTrue($subject->getPaymentHelper() instanceof Netresearch_OPS_Helper_Payment);
        $subject->setPaymentHelper($testString);
        $this->assertEquals($subject->getPaymentHelper(), $testString);
    }

    /**
     * @return Mage_Sales_Model_Recurring_Profile
     */
    protected function getTestProfile()
    {
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setScheduleDescription('abc')
                ->setPeriodFrequency(1)
                ->setPeriodMaxCycles(1)
                ->setPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_WEEK)
                ->setInitAmount(10)
                ->setBillingAmount(15)
                ->setTaxAmount(5)
                ->setShippingAmount(5)
                ->setStartDatetime('')
                ->setBillingAddressInfo(array('quote_id' => 1))
                ->setMethodCode(Netresearch_OPS_Model_Payment_Recurring_Cc::CODE)
                ->setId(0);

        return $profile;
    }

    /**
     * @return string[]
     */
    protected function getResponseParams()
    {

        $responseParams = array(
            'PAYID'    => '123',
            'currency' => 'EUR',
            'STATUS'   => '5',
            'orderID'  => 'SUB-123',
            'amount'   => '0'
        );

        return $responseParams;

    }

    protected function mockDependencies()
    {

        $profileMock = $this->getModelMock('sales/recurring_profile', array('addOrderRelation'));
        $profileMock->expects($this->once())
                    ->method('addOrderRelation')
                    ->will($this->returnValue(''));
        $this->replaceByMock('model', 'sales/recurring_profile', $profileMock);

        $paymentHelperMock = $this->getHelperMock('ops/payment', array('applyStateForOrder'));
        $paymentHelperMock->expects($this->once())
                          ->method('applyStateForOrder')
                          ->will($this->returnValue(''));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);

        $orderMock = $this->getModelMock('sales/order', array('save'));
        $orderMock->expects($this->any())
                  ->method('save')
                  ->will($this->returnValue(''));
        $this->replaceByMock('model', 'sales/order', $orderMock);

        $paymentMock = $this->getModelMock(
            'sales/order_payment',
            array('registerAuthorizationNotification', 'registerCaptureNotification')
        );
        $paymentMock->expects($this->any())
                    ->method('registerCaptureNotification')
                    ->will($this->returnValue(''));
        $paymentMock->expects($this->any())
                    ->method('registerAuthorizationNotification')
                    ->will($this->returnValue(''));
        $this->replaceByMock('model', 'sales/order_payment', $paymentMock);
    }

    public function testProcessSubscriptionFeedbackWithInitialCreation()
    {
        $subject = Mage::getModel('ops/subscription_manager');
        $profile = $this->getTestProfile();
        $responseParams = $this->getResponseParams();

        $responseParams['creation_status'] = Netresearch_OPS_Model_Subscription_Manager::CREATION_SUCCEEDED;
        $responseParams['subscription_id'] = 'test';
        $result = $subject->processSubscriptionFeedback($responseParams, $profile);

        $this->assertFalse($result);
        $this->assertEquals($profile->getReferenceId(), 'test');
        $this->assertEquals($profile->getState(), Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
    }

    public function testProcessSubscriptionFeedbackWithRegularSubscriptionFeedback()
    {
        $this->mockDependencies();
        $subject = Mage::getModel('ops/subscription_manager');
        $profile = $this->getTestProfile();
        $responseParams = $this->getResponseParams();

        $result = $subject->processSubscriptionFeedback($responseParams, $profile);

        $this->assertTrue($result instanceof Mage_Sales_Model_Order);
        $this->assertEquals($result->getShippingAmount(), $profile->getShippingAmount());
        $this->assertEquals($result->getTaxAmount(), $profile->getTaxAmount());
        $this->assertEquals($result->getSubtotal(), $profile->getBillingAmount());
    }

    public function testProcessSubscriptionFeedbackWithTrialSubscriptionFeedback()
    {
        /** @var $subject Netresearch_OPS_Model_Subscription_Manager */
        $this->mockDependencies();
        $subject = Mage::getModel('ops/subscription_manager');
        $profile = $this->getTestProfile();
        $responseParams = $this->getResponseParams();
        $responseParams['orderID'] = $responseParams['orderID'] . Netresearch_OPS_Helper_Subscription::TRIAL_SUFFIX;
        $profile->setTrialPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_MONTH)
                ->setTrialBillingAmount(5)
                ->setTrialPeriodFrequency(3)
                ->setTrialPeriodMaxCycles(4)
                ->setInitAmount(0)
                ->setOrderItemInfo(array('tax_percent' => 7));


        $result = $subject->processSubscriptionFeedback($responseParams, $profile);

        $this->assertTrue($result instanceof Mage_Sales_Model_Order);
        $this->assertEquals($result->getShippingAmount(), $profile->getShippingAmount());
        $this->assertEquals($result->getBaseGrandTotal(), $profile->getTrialBillingAmount() + $profile->getShippingAmount());
    }
}
