<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netresearch_OPS_Test_Helper_QuoteTest extends EcomDev_PHPUnit_Test_Case
{

    public function testGetDataHelper()
    {
        $this->assertTrue(Mage::helper('ops/quote')->getDataHelper() instanceof Netresearch_OPS_Helper_Data);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testCleanUpOldPaymentInformation()
    {
        $payment = Mage::getModel('sales/quote_payment')->load(3);
        $this->assertArrayHasKey('cvc', $payment->getAdditionalInformation());
        Mage::helper('ops/quote')->cleanUpOldPaymentInformation();
        $payment = Mage::getModel('sales/quote_payment')->load(3);
        $this->assertArrayNotHasKey(
            'cvc', $payment->getAdditionalInformation()
        );
    }

    public function testGetQuoteCurrency()
    {
        $quote = Mage::getModel('sales/quote');
        $this->assertEquals(
            Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode(),
            Mage::helper('ops/quote')->getQuoteCurrency($quote)
        );
        $forcedCurrency = new Varien_Object();
        $forcedCurrency->setCode('USD');
        $quote->setForcedCurrency($forcedCurrency);
        $this->assertEquals(
            'USD', Mage::helper('ops/quote')->getQuoteCurrency($quote)
        );
    }

    public function testGetPaymentActionForAuthorize()
    {
        $order   = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $order->setPayment($payment);
        $modelMock = $this->getModelMock(
            'ops/config', array('getPaymentAction')
        );
        $modelMock->expects($this->any())
            ->method('getPaymentAction')
            ->will(
                $this->returnValue(
                    'Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION'
                )
            );
        $this->replaceByMock('model', 'ops/config', $modelMock);
        $helper = Mage::helper('ops/quote');
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION,
            $helper->getPaymentAction($order)
        );
        $order->getPayment()->setAdditionalInformation(
            'PM', 'Direct Debits DE'
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION,
            $helper->getPaymentAction($order)
        );
        $order->getPayment()->setAdditionalInformation(
            'PM', 'Direct Debits AT'
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION,
            $helper->getPaymentAction($order)
        );
        $order->getPayment()->setAdditionalInformation(
            'PM', 'Direct Debits NL'
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_ACTION,
            $helper->getPaymentAction($order)
        );
    }

    public function testGetPaymentActionForAuthorizeCapture()
    {
        $order   = Mage::getModel('sales/order');
        $payment = Mage::getModel('sales/order_payment');
        $order->setPayment($payment);
        $modelMock = $this->getModelMock(
            'ops/config', array('getPaymentAction')
        );
        $modelMock->expects($this->any())
            ->method('getPaymentAction')
            ->will($this->returnValue('authorize_capture'));
        $this->replaceByMock('model', 'ops/config', $modelMock);
        $helper = Mage::helper('ops/quote');
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION,
            $helper->getPaymentAction($order)
        );
        $order->getPayment()->setAdditionalInformation(
            'PM', 'Direct Debits DE'
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION,
            $helper->getPaymentAction($order)
        );
        $order->getPayment()->setAdditionalInformation(
            'PM', 'Direct Debits AT'
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_AUTHORIZE_CAPTURE_ACTION,
            $helper->getPaymentAction($order)
        );
        $order->getPayment()->setAdditionalInformation(
            'PM', 'Direct Debits NL'
        );
        $this->assertEquals(
            Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_DIRECTDEBIT_NL,
            $helper->getPaymentAction($order)
        );
    }


    public function testGetQuoteWithAdminSession()
    {
        $fakeQuote = $this->getModelMock('sales/quote');
        $this->replaceByMock('model', 'sales/quote', $fakeQuote);

        $sessionMock = $this->getModelMockBuilder('adminhtml/session_quote')
            ->disableOriginalConstructor() // This one removes session_start and other methods usage
            ->setMethods(null) // Enables original methods usage, because by default it overrides all methods
            ->getMock();
        $sessionMock->setData('quote', $fakeQuote);
        $this->replaceByMock('singleton', 'adminhtml/session_quote', $sessionMock);
        $helper = Mage::helper('ops/quote');
        $this->assertEquals($fakeQuote, $helper->getQuote());
    }

    public function testGetQuoteWithCheckoutSession()
    {
        Mage::app()->setCurrentStore(1);

        $fakeQuote = $this->getModelMock('sales/quote', array('setStoreId'));
        $fakeQuote->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnValue($fakeQuote));
        $this->replaceByMock('model', 'sales/quote', $fakeQuote);


        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $sessionMock = $this->getModelMockBuilder('checkout/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $sessionMock->setData('quote', $fakeQuote);
        $this->replaceByMock('singleton', 'checkout/session', $sessionMock);
        /** @var Netresearch_OPS_Helper_Quote $helper */
        $helper = Mage::helper('ops/quote');
        $this->assertEquals($fakeQuote, $helper->getQuote());
    }


}