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
 * CcTest.php
 *
 * @category payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Test_Model_Payment_Recurring_CcTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @return Mage_Sales_Model_Recurring_Profile
     */
    protected function getTestProfile()
    {
        /** @var Mage_Sales_Model_Recurring_Profile $profile */
        $profile = Mage::getModel('sales/recurring_profile');
        $address = Mage::getModel('sales/quote_address');
        $address->setCity('Leipzig');
        $quote = Mage::getModel('sales/quote');
        $quote->setShippingAddress($address);
        $quote->setBillingAddress($address);
        $quote->setPayment($this->getTestPayment());

        $profile->setScheduleDescription('abc')
                ->setPeriodFrequency(1)
                ->setPeriodMaxCycles(1)
                ->setMethodCode('ops_recurring_cc')
                ->setPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_WEEK)
                ->setOrderItemInfo(array())
                ->setInitAmount(10)
                ->setBillingAmount(15)
                ->setTaxAmount(5)
                ->setShippingAmount(5)
                ->setStartDatetime('')
                ->setQuote($quote)
                ->setCurrencyCode('EUR')
                ->setState(Mage_Sales_Model_Recurring_Profile::STATE_UNKNOWN)
                ->setId(0);

        return $profile;
    }

    protected function getResponseParams($success)
    {
        $params = array(
            'subscription_id' => 'SUB-0',
            'orderID'         => 'SUB-0',
            'PAYID'           => 12345,
            'currency'        => 'EUR',
            'amount'          => 1000
        );
        if ($success) {
            $params['STATUS'] = 9;
            $params['creation_status'] = Netresearch_OPS_Model_Subscription_Manager::CREATION_SUCCEEDED;
        } else {
            $params['STATUS'] = 2;
            $params['creation_status'] = Netresearch_OPS_Model_Subscription_Manager::CREATION_FAILED;
        }

        return $params;
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

    public function testValidateRecurringProfile()
    {
        $subject = Mage::getModel('ops/payment_recurring_cc');

        $profile = $this->getTestProfile();

        $subject->validateRecurringProfile($profile);
    }


    public function testSubmitRecurringProfileOnlyRegular()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');

        $params = $this->getResponseParams(true);
        $this->mockDirectLinkRequest($params);

        $profile = $this->getTestProfile();

        $profile->setInitAmount(0);
        $paymentInfo = $this->getTestPayment();
        $subject->submitRecurringProfile($profile, $paymentInfo);

        $this->assertEquals($profile->getReferenceId(), 'SUB-0');
        $this->assertEquals(serialize($params), $profile->getProfileVendorInfo());
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function testSubmitRecurringProfileOnlyRegularWithException()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');

        $params = $this->getResponseParams(false);
        $this->mockDirectLinkRequest($params);

        $profile = $this->getTestProfile();

        $profile->setInitAmount(0);
        $paymentInfo = $this->getTestPayment();
        $subject->submitRecurringProfile($profile, $paymentInfo);

        $this->assertEquals($profile->getReferenceId(), 'SUB-0');
        $this->assertEquals(serialize($params), $profile->getProfileVendorInfo());
    }

    public function testSubmitRecurringProfileWithTrial()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');

        $params = $this->getResponseParams(true);
        $this->mockDirectLinkRequest($params);

        $profile = $this->getTestProfile();
        $profile->setInitAmount(0)
                ->setTrialBillingAmount(1)
                ->setTrialPeriodFrequency(5)
                ->setTrialPeriodMaxCycles(10)
                ->setTrialPeriodMaxCycles(5)
                ->setTrialPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_DAY);

        $paymentInfo = $this->getTestPayment();
        $subject->submitRecurringProfile($profile, $paymentInfo);

        $this->assertEquals($profile->getReferenceId(), 'SUB-0');
        $this->assertEquals(serialize($params), $profile->getProfileVendorInfo());
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function testSubmitRecurringProfileWithTrialWithException()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');

        $params = $this->getResponseParams(false);
        $this->mockTwoDirectLinkRequests($this->getResponseParams(true), $params);

        $profile = $this->getTestProfile();
        $profile->setInitAmount(0)
                ->setTrialBillingAmount(1)
                ->setTrialPeriodFrequency(5)
                ->setTrialPeriodMaxCycles(10)
                ->setTrialPeriodMaxCycles(5)
                ->setTrialPeriodUnit(Mage_Sales_Model_Recurring_Profile::PERIOD_UNIT_DAY);

        $paymentInfo = $this->getTestPayment();
        $subject->submitRecurringProfile($profile, $paymentInfo);

        $this->assertEquals($profile->getReferenceId(), 'SUB-0');
        $this->assertEquals(serialize($params), $profile->getProfileVendorInfo());
    }

    public function testSubmitRecurringProfileWithInitialFee()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');

        $profileMock = $this->getModelMock('sales/recurring_profile', array('getChildOrderIds', 'addOrderRelation'));
        $profileMock->expects($this->any())
                    ->method('getChildOrderIds')
                    ->will($this->returnSelf());
        $profileMock->expects($this->any())
                    ->method('addOrderRelation')
                    ->will($this->returnSelf());
        $this->replaceByMock('model', 'sales/recurring_profile', $profileMock);

        $paymentHelperMock = $this->getHelperMock('ops/payment', array('applyStateForOrder'));
        $paymentHelperMock->expects($this->any())
                          ->method('applyStateForOrder')
                          ->will($this->returnSelf());
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);

        $params = $this->getResponseParams(true);
        $this->mockDirectLinkRequest($params);

        $profile = $this->getTestProfile();
        $profile->setTrialBillingAmount(1)
                ->setTrialPeriodFrequency(5)
                ->setTrialPeriodMaxCycles(10)
                ->setTrialPeriodMaxCycles(5);

        $paymentInfo = $this->getTestPayment();
        $subject->submitRecurringProfile($profile, $paymentInfo);

        $this->assertEquals($profile->getReferenceId(), 'SUB-0');
        $this->assertEquals(serialize($params), $profile->getProfileVendorInfo());
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function testGetRecurringProfileDetails()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $subject->getRecurringProfileDetails('abc', new Varien_Object());
    }
    
    public function testCanGetRecurringProfileDetails()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $this->assertFalse($subject->canGetRecurringProfileDetails());
    }

    /**
     * @test
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Function not supported
     */
    public function testUpdateRecurringProfile()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $subject->updateRecurringProfile($this->getTestProfile());
    }

    /**
     * @test
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Automatic activation not possible. Please contact our support team.
     */
    public function testUpdateRecurringProfileStatusWithActivateException()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
        $subject->updateRecurringProfileStatus($profile);

    }

    public function testUpdateRecurringProfileStatusWithActivate()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $adminMock->expects($this->once())
                  ->method('isLoggedIn')
                  ->will($this->returnValue(true));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $adminHtmlMock = $this->getModelMock('adminhtml/session', array('addNotice', 'init'));
        $adminHtmlMock->expects($this->once())
                      ->method('addNotice');
        $this->replaceByMock('singleton', 'adminhtml/session', $adminHtmlMock);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_ACTIVE);
        $subject->updateRecurringProfileStatus($profile);
    }

    /**
     * @test
     */
    public function testUpdateRecurringProfileStatusWithCancelAsCustomer()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $sessionMock = $this->getModelMock('customer/session', array('getCustomer', 'init'));
        $customer = new Varien_Object(array('id' => 1));
        $sessionMock->expects($this->once())
                    ->method('getCustomer')
                    ->will($this->returnValue($customer));
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $mailModelMock = $this->getModelMock('ops/payment_features_paymentEmail', array('sendSuspendSubscriptionMail'));
        $mailModelMock->expects($this->once())
                      ->method('sendSuspendSubscriptionMail')
                      ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/payment_features_paymentEmail', $mailModelMock);


        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_CANCELED);
        $profile->setCustomerId(1);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $subject->updateRecurringProfileStatus($profile);

        $this->assertTrue($profile->getOverrideState());
        $this->assertEquals(Mage_Sales_Model_Recurring_Profile::STATE_PENDING, $profile->getNewState());
        $this->assertNotNull($sessionMock->getMessages()->getLastAddedMessage());
    }

    public function testUpdateRecurringProfileStatusWithCancel()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $adminMock->expects($this->once())
                  ->method('isLoggedIn')
                  ->will($this->returnValue(true));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $adminHtmlMock = $this->getModelMock('adminhtml/session', array('addNotice', 'init'));
        $adminHtmlMock->expects($this->once())
                      ->method('addNotice');
        $this->replaceByMock('singleton', 'adminhtml/session', $adminHtmlMock);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_CANCELED);
        $subject->updateRecurringProfileStatus($profile);
    }

    public function testUpdateRecurringProfileStatusWithSuspendAsCustomer()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $sessionMock = $this->getModelMock('customer/session', array('getCustomer', 'init'));
        $customer = new Varien_Object(array('id' => 1));
        $sessionMock->expects($this->once())
                    ->method('getCustomer')
                    ->will($this->returnValue($customer));
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $mailModelMock = $this->getModelMock('ops/payment_features_paymentEmail', array('sendSuspendSubscriptionMail'));
        $mailModelMock->expects($this->once())
                      ->method('sendSuspendSubscriptionMail')
                      ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/payment_features_paymentEmail', $mailModelMock);


        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED);
        $profile->setCustomerId(1);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $subject->updateRecurringProfileStatus($profile);

        $this->assertTrue($profile->getOverrideState());
        $this->assertEquals(Mage_Sales_Model_Recurring_Profile::STATE_PENDING, $profile->getNewState());
        $this->assertNotNull($sessionMock->getMessages()->getLastAddedMessage());
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage You are not allowed to suspend this subscription!
     */
    public function testUpdateRecurringProfileStatusWithSuspendAsCustomerWithUnAllowedException()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $sessionMock = $this->getModelMock('customer/session', array('getCustomer', 'init'));
        $customer = new Varien_Object(array('id' => 2));
        $sessionMock->expects($this->once())
                    ->method('getCustomer')
                    ->will($this->returnValue($customer));
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED);
        $profile->setCustomerId(1);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $subject->updateRecurringProfileStatus($profile);
    }

    /**
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Could not send suspend mail, please try again or contact our support directly.
     */
    public function testUpdateRecurringProfileStatusWithSuspendAsCustomerWithFailedException()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $sessionMock = $this->getModelMock('customer/session', array('getCustomer', 'init'));
        $customer = new Varien_Object(array('id' => 1));
        $sessionMock->expects($this->once())
                    ->method('getCustomer')
                    ->will($this->returnValue($customer));
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $mailModelMock = $this->getModelMock('ops/payment_features_paymentEmail', array('sendSuspendSubscriptionMail'));
        $mailModelMock->expects($this->once())
                      ->method('sendSuspendSubscriptionMail')
                      ->will($this->returnValue(false));
        $this->replaceByMock('model', 'ops/payment_features_paymentEmail', $mailModelMock);


        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED);
        $profile->setCustomerId(1);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $subject->updateRecurringProfileStatus($profile);
    }

    public function testUpdateRecurringProfileStatusWithSuspendAsAdmin()
    {
        $adminMock = $this->getModelMock('admin/session', array('isLoggedIn', 'init'));
        $adminMock->expects($this->once())
                  ->method('isLoggedIn')
                  ->will($this->returnValue(true));
        $this->replaceByMock('singleton', 'admin/session', $adminMock);

        $adminHtmlMock = $this->getModelMock('adminhtml/session', array('addNotice', 'init'));
        $adminHtmlMock->expects($this->once())
                      ->method('addNotice');
        $this->replaceByMock('singleton', 'adminhtml/session', $adminHtmlMock);

        $subject = Mage::getModel('ops/payment_recurring_cc');
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_SUSPENDED);
        $subject->updateRecurringProfileStatus($profile);
    }

    /**
     * @test
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Expire function not implemented!
     */
    public function testUpdateRecurringProfileStatusWithExpireException()
    {
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState(Mage_Sales_Model_Recurring_Profile::STATE_EXPIRED);
        $subject->updateRecurringProfileStatus($profile);
    }

    /**
     * @test
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage Action for state abc not supported
     */
    public function testUpdateRecurringProfileStatusWithNotSupportedException()
    {
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $profile = Mage::getModel('sales/recurring_profile');
        $profile->setNewState('abc');
        $subject->updateRecurringProfileStatus($profile);
    }
    
    public function testIsAvailable()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $items = array();
        $item1 = Mage::getModel('sales/quote_item');
        $item1->setData('is_nominal', false);
        $items[] = $item1;
        $quote2 = $this->getModelMock('sales/quote', array('getAllVisibleItems'));
        $quote2->expects($this->any())
               ->method('getAllVisibleItems')
               ->will($this->returnValue($items));

        $this->assertFalse($subject->isAvailable($quote2));
    }
    
    public function testHasBrandAliasInterfaceSupport()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $this->assertTrue($subject->hasBrandAliasInterfaceSupport());
    }
    
    public function testGetOrderPlaceRedirectUrl()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $payment = Mage::getModel('payment/info');
        $payment->setAdditionalInformation('HTML_ANSWER', 'abc');
        $this->assertEquals(
            $subject->getOrderPlaceRedirectUrl($payment),
            Mage::getModel('ops/config')->get3dSecureRedirectUrl()
        );

        $payment->setAdditionalInformation('HTML_ANSWER', '');
        $this->assertFalse($subject->getOrderPlaceRedirectUrl($payment));
    }
    
    public function testIsZeroAmountAuthorizationAllowed()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $this->assertFalse($subject->isZeroAmountAuthorizationAllowed());
    }
    
    public function testGetBrandsForAliasInterface()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');
        $this->assertEquals(
            $subject->getBrandsForAliasInterface(),
            'American Express,Diners Club,MaestroUK,MasterCard,VISA,JCB'
        );
    }

    public function testGetterSetters()
    {
        /** @var Netresearch_OPS_Model_Payment_Recurring_Cc $subject */
        $subject = Mage::getModel('ops/payment_recurring_cc');

        $this->assertTrue($subject->getSubscriptionManager() instanceof Netresearch_OPS_Model_Subscription_Manager);
        $subject->setSubscriptionManager('abc');
        $this->assertTrue($subject->getSubscriptionManager() === 'abc');

        $this->assertTrue(
            $subject->getParameterModel() instanceof
            Netresearch_OPS_Model_Payment_Recurring_Cc_ParameterBag
        );
        $subject->setParameterModel('abc');
        $this->assertTrue($subject->getParameterModel() === 'abc');
    }

    /**
     * @param $params
     */
    protected function mockDirectLinkRequest($params)
    {
        $helperMock = $this->getHelperMock('ops/directlink', array('performDirectLinkRequest'));
        $helperMock->expects($this->any())
                   ->method('performDirectLinkRequest')
                   ->will($this->returnValue($params));
        $this->replaceByMock('helper', 'ops/directlink', $helperMock);
    }


    protected function mockTwoDirectLinkRequests($params1, $params2)
    {
        $helperMock = $this->getHelperMock('ops/directlink', array('performDirectLinkRequest'));
        $helperMock->expects($this->any())
                   ->method('performDirectLinkRequest')
                   ->will($this->onConsecutiveCalls($params1, $params2));
        $this->replaceByMock('helper', 'ops/directlink', $helperMock);
    }
    
}