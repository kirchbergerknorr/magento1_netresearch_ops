<?php
class Netresearch_OPS_Test_Model_Payment_CcTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  $_model Netresearch_OPS_Model_Payment_Cc */
    private $_model;
    private $_payment;

    public function setUp()
    {
        parent::setup();
        $this->_model = Mage::getModel('ops/payment_cc');
        $this->_payment = ObjectHandler::getObject('quoteBeforeSaveOrder')->getPayment();
    }

    public function testBrand()
    {
        $this->_payment->setAdditionalInformation('CC_BRAND', 'VISA');
        $this->assertEquals('VISA', $this->_model->getOpsBrand($this->_payment), 'VISA should have brand VISA');
        $this->assertEquals('CreditCard', $this->_model->getOpsCode($this->_payment), 'VISA should be a CreditCard');
        $this->assertTrue($this->_model->hasBrandAliasInterfaceSupport($this->_payment), 'VISA should support alias interface');

        $this->_payment->setAdditionalInformation('CC_BRAND', 'UNEUROCOM');
        $this->assertEquals('UNEUROCOM', $this->_model->getOpsBrand($this->_payment), 'UNEUROCOM should have brand UNEUROCOM');
        $this->assertEquals('UNEUROCOM', $this->_model->getOpsCode($this->_payment), 'UNEUROCOM should have code UNEUROCOM');
        $this->assertFalse($this->_model->hasBrandAliasInterfaceSupport($this->_payment), 'UNEUROCOM should NOT support alias interface');

        $this->_payment->setAdditionalInformation('CC_BRAND', 'PostFinance card');
        $this->assertEquals('PostFinance card', $this->_model->getOpsBrand($this->_payment), 'PostFinance Card should have brand "PostFinance card"');
        $this->assertEquals('PostFinance Card', $this->_model->getOpsCode($this->_payment), 'PostFinance Card should have code "PostFinance Card"');
        $this->assertFalse($this->_model->hasBrandAliasInterfaceSupport($this->_payment), 'PostFinance Card should NOT support alias interface');

        $this->_payment->setAdditionalInformation('CC_BRAND', 'PRIVILEGE');
        $this->assertEquals('PRIVILEGE', $this->_model->getOpsBrand($this->_payment), 'PRIVILEGE should have brand PRIVILEGE');
        $this->assertEquals('CreditCard', $this->_model->getOpsCode($this->_payment), 'PRIVILEGE should be a CreditCard');
        $this->assertFalse($this->_model->hasBrandAliasInterfaceSupport($this->_payment), 'PRIVILEGE should NOT support alias interface');
    }

    public function testOrderPlaceRedirectUrl()
    {
        $this->_model->setInfoInstance($this->_payment);
        $this->_payment->setAdditionalInformation('CC_BRAND', 'VISA');
        $this->assertFalse($this->_model->getOrderPlaceRedirectUrl($this->_payment), 'VISA should NOT require a redirect after checkout');
        
        $this->_payment->setAdditionalInformation('CC_BRAND', 'VISA');
        $this->_payment->setAdditionalInformation('HTML_ANSWER', 'BASE64ENCODEDSTRING');
        $this->assertInternalType('string', $this->_model->getOrderPlaceRedirectUrl($this->_payment), 'If Brand is VIA and HTML_ANSWER isset, a redirect should happen after checkout');
        
        $this->_payment->setAdditionalInformation('CC_BRAND', 'PRIVILEGE');
        $this->assertInternalType('string', $this->_model->getOrderPlaceRedirectUrl($this->_payment), 'PRIVILEGE should require a redirect after checkout');
        
    }

    public function testIsZeroAmountAuthorizationAllowed()
    {
        $model = new Netresearch_OPS_Model_Payment_Cc();
        $store = Mage::app()->getStore(0)->load(0);
        $store->resetConfig();
        $store->setConfig('payment_services/ops/payment_action', Netresearch_OPS_Model_Payment_Abstract::ACTION_AUTHORIZE_CAPTURE);
        $store->setConfig('payment/ops_cc/zero_amount_checkout', 0);
        $this->assertFalse($model->isZeroAmountAuthorizationAllowed());

        $store->resetConfig();
        $store->setConfig('payment_services/ops/payment_action', Netresearch_OPS_Model_Payment_Abstract::ACTION_AUTHORIZE_CAPTURE);
        $store->setConfig('payment/ops_cc/zero_amount_checkout', 1);
        $this->assertFalse($model->isZeroAmountAuthorizationAllowed());

        $store->resetConfig();
        $store->setConfig('payment_services/ops/payment_action', Netresearch_OPS_Model_Payment_Abstract::ACTION_AUTHORIZE);
        $store->setConfig('payment/ops_cc/zero_amount_checkout', 0);
        $this->assertFalse($model->isZeroAmountAuthorizationAllowed());
    }

    public function testZeroAmountAuthAllowed()
    {
        $model = new Netresearch_OPS_Model_Payment_Cc();
        $store = Mage::app()->getStore(0)->load(0);
        $configMock = $this->getModelMock('ops/config', array('getConfigData'));
        $configMock->expects($this->once())
            ->method('getConfigData')
            ->with('payment_action')
            ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::ACTION_AUTHORIZE));
        $this->replaceByMock('model', 'ops/config', $configMock);
        $store->setConfig('payment/ops_cc/zero_amount_checkout', 1);
        $this->assertTrue($model->isZeroAmountAuthorizationAllowed());
    }



    public function testIsApplicableToQuoteTrue()
    {
        $helperMock = $this->getHelperMock('ops/version', array('canUseApplicableForQuote'));
        $helperMock->expects($this->any())
            ->method('canUseApplicableForQuote')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'ops/version', $helperMock);

        $quote = Mage::getModel('sales/quote');
        $quote->setBaseGrandTotal(0.0);
        $this->assertTrue($this->_model->isApplicableToQuote($quote, 1));

    }

    public function testIsApplicableToQuoteTrueWithZeroAmount()
    {
        $versionInfo = Mage::getVersionInfo();
        if ((array_key_exists('minor', $versionInfo))
            && (Mage::getEdition() === Mage::EDITION_COMMUNITY && $versionInfo['minor'] > '7')
            || (Mage::getEdition() === Mage::EDITION_ENTERPRISE && $versionInfo['minor'] > '13')
        )
        {
            $store = Mage::app()->getStore(0)->load(0);
            $configMock = $this->getModelMock('ops/config', array('getConfigData'));
            $configMock->expects($this->once())
                ->method('getConfigData')
                ->with('payment_action')
                ->will($this->returnValue(Netresearch_OPS_Model_Payment_Abstract::ACTION_AUTHORIZE));
            $this->replaceByMock('model', 'ops/config', $configMock);
            $store->setConfig('payment/ops_cc/zero_amount_checkout', 1);


            $helperMock = $this->getHelperMock('ops/version', array('canUseApplicableForQuote'));
            $helperMock->expects($this->any())
                ->method('canUseApplicableForQuote')
                ->will($this->returnValue(true));
            $this->replaceByMock('helper', 'ops/version', $helperMock);

            $quote       = $this->getModelMock('sales/quote', array('getItemsCount', 'getBaseGrandTotal', 'isNominal'));
            $quote->expects($this->once())
                ->method('getItemsCount')
                ->will($this->returnValue(1));
            $quote->expects($this->any())
                ->method('getBaseGrandTotal')
                ->will($this->returnValue(0.0));
            $quote->expects($this->any())
                ->method('isNominal')
                ->will($this->returnValue(false));
            $this->assertTrue($this->_model->isApplicableToQuote($quote, 128));
        }
    }

    public function testIsApplicableToQuoteFeatureModelTrue()
    {
        $versionInfo = Mage::getVersionInfo();
        if ((array_key_exists('minor', $versionInfo))
            && (Mage::getEdition() === Mage::EDITION_COMMUNITY && $versionInfo['minor'] > '7')
            || (Mage::getEdition() === Mage::EDITION_ENTERPRISE && $versionInfo['minor'] > '13')
        )
        {
            $featureModelMock = $this->getModelMock('ops/payment_features_zeroAmountAuth', array('isCCAndZeroAmountAuthAllowed'));
            $featureModelMock->expects($this->any())
                ->method('isCCAndZeroAmountAuthAllowed')
                ->will($this->returnValue(true));
            $this->replaceByMock('model', 'ops/payment_features_zeroAmountAuth', $featureModelMock);

            $helperMock = $this->getHelperMock('ops/version', array('canUseApplicableForQuote'));

            $helperMock->expects($this->any())
                ->method('canUseApplicableForQuote')
                ->will($this->returnValue(true));
            $this->replaceByMock('helper', 'ops/version', $helperMock);

            $quote = Mage::getModel('sales/quote');
            $this->assertTrue($this->_model->isApplicableToQuote($quote, '1'));
        }
    }

    public function testGetFeatureModel()
    {
        $this->assertTrue($this->_model->getFeatureModel() instanceof Netresearch_OPS_Model_Payment_Features_ZeroAmountAuth);
    }

    public function testSetCanCapture()
    {
        $ccPaymentObject = Mage::getModel('ops/payment_cc');
        $this->assertTrue($ccPaymentObject->canCapture());

        $ccPaymentObject->setCanCapture(false);
        $this->assertFalse($ccPaymentObject->canCapture());
    }

    public function testGetOpsBrand()
    {
        $payment = Mage::getModel('sales/quote_payment');
        $payment->setAdditionalInformation('CC_BRAND', 'VISA');
        $quote = $this->getModelMock('sales/quote', array('getPayment'));
        $quote->expects($this->any())
            ->method('getPayment')
            ->will($this->returnValue($payment));
        $quote->setId(1);
        $checkoutSessionMock = $this->getModelMock('checkout/session', array('getQuote', 'init', 'save'));
        $checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));
        $this->replaceByMock('singleton', 'checkout/session', $checkoutSessionMock);
        $this->assertEquals('VISA', $this->_model->getOpsBrand(null));

    }

    public function testIsAvailable()
    {
        $helperMock = $this->getHelperMock('ops/data', array('isAdminSession'));
        $helperMock->expects($this->once())
            ->method('isAdminSession')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'ops/data', $helperMock);
        $quote = Mage::getModel('sales/quote');
        $quote->setItemsCount(0);
        $this->assertFalse($this->_model->isAvailable($quote));
        $quote->setItemsCount(500);
        $this->assertFalse($this->_model->isAvailable($quote));
    }
}

