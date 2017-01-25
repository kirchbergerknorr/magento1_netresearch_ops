<?php
class Netresearch_OPS_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    protected $helper;
    protected $store;

    public function setUp()
    {
        parent::setup();
        $this->helper = Mage::helper('ops');
        $this->store  = Mage::app()->getStore(0)->load(0);
    }

    /**
     * @test
     */
    public function getModuleVersionString()
    {
        $path = 'modules/Netresearch_OPS/version';

        Mage::getConfig()->setNode($path, '12.03.01');
        $this->assertSame('OGmg120301', $this->helper->getModuleVersionString());

        Mage::getConfig()->setNode($path, '12.06.12+383');
        $this->assertSame('OGmg120612', $this->helper->getModuleVersionString());

        Mage::getConfig()->setNode($path, '12.09.12-beta2+808');
        $this->assertSame('OGmg120912', $this->helper->getModuleVersionString());

        $this->store->resetConfig();
    }

    public function testCheckIfUserIsRegistering()
    {
        $quote = new Varien_Object();
        $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
        $sessionMock = $this->getModelMock('checkout/session', array('getQuote', 'init', 'save'));
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);


        $this->assertTrue(Mage::helper('ops/data')->checkIfUserIsRegistering());

        $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN);
        $this->assertTrue(Mage::helper('ops/data')->checkIfUserIsRegistering());

        $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
        $this->assertFalse(Mage::helper('ops/data')->checkIfUserIsRegistering());

    }

    public function testCheckIfUserIsNotRegistering()
    {
        $quote = new Varien_Object();
        $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER);
        $sessionMock = $this->getModelMock('checkout/session', array('getQuote', 'init', 'save'));
        $sessionMock->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($quote));
        $this->replaceByMock('model', 'checkout/session', $sessionMock);

        $this->assertTrue(Mage::helper('ops/data')->checkIfUserIsNotRegistering());

        $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_LOGIN_IN);
        $this->assertFalse(Mage::helper('ops/data')->checkIfUserIsNotRegistering());

        $quote->setCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
        $this->assertFalse(Mage::helper('ops/data')->checkIfUserIsNotRegistering());
    }

    public function testClearMsg()
    {
        $helper = Mage::helper('ops/data');
        $testArray = array('cvc' => '1', 'CVC' => '2', 'test' => 'me');
        $testArray = $helper->clearMsg($testArray);
        $this->assertFalse(array_key_exists('cvc', $testArray));
        $this->assertFalse(array_key_exists('CVC', $testArray));
        $this->assertTrue(array_key_exists('test', $testArray));
        $testString = '{"CVC":"123"}';
        $this->assertFalse(strpos($helper->clearMsg($testString), 'CVC'));
        $testString = '{"CVC":"123","CN":"Some Name"}';
        $this->assertFalse(strpos($helper->clearMsg($testString), 'CVC'));
        $testString = '{"cvc":"123","CN":"Some Name"}';
        $this->assertFalse(strpos($helper->clearMsg($testString), 'cvc'));
        $this->assertTrue(false !== strpos($helper->clearMsg($testString), 'CN'));

        $testString = 'a:3:{s:5:"Alias";s:14:"10290855992990";s:3:"CVC";s:3:"777";s:2:"CN";s:13:"Homer Simpson";}';
        $this->assertFalse(strpos($helper->clearMsg($testString), 'CVC'));
        $this->assertTrue(false !== strpos($helper->clearMsg($testString), 'Homer'));
    }

    /**
     * @test
     */
    public function sendNoMail()
    {
        $documentMock = $this->getMockBuilder('Mage_Sales_Model_Order_Shipment')
            ->setMethods(array('getEmailSent'))
            ->getMock();
        $documentMock
            ->expects($this->never())
            ->method('getEmailSent');

        Mage::helper('ops/data')->sendTransactionalEmail($documentMock);
    }

    /**
     * @test
     */
    public function sendOrderEmail()
    {
        $orderMock = $this->getModelMock(
            'sales/order', array(
            'getEmailSent', 'getCanSendNewEmailFlag', 'sendNewOrderEmail'
            )
        );
        $orderMock
            ->expects($this->exactly(3))
            ->method('getEmailSent')
            ->willReturnOnConsecutiveCalls(true, false, false);
        $orderMock
            ->expects($this->exactly(2))
            ->method('getCanSendNewEmailFlag')
            ->willReturnOnConsecutiveCalls(false, true);
        $orderMock
            ->expects($this->once())
            ->method('sendNewOrderEmail');
        $this->replaceByMock('model', 'sales/order', $orderMock);

        $order = Mage::getModel('sales/order');
        Mage::helper('ops/data')->sendTransactionalEmail($order);
        Mage::helper('ops/data')->sendTransactionalEmail($order);
        Mage::helper('ops/data')->sendTransactionalEmail($order);
    }

    /**
     * @test
     */
    public function sendInvoiceEmail()
    {
        $configMock = $this->getModelMock('ops/config', array('getSendInvoice'));
        $configMock
            ->expects($this->exactly(2))
            ->method('getSendInvoice')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->replaceByMock('model', 'ops/config', $configMock);

        $invoiceMock = $this->getModelMock(
            'sales/order_invoice', array(
            'getEmailSent', 'sendEmail'
            )
        );
        $invoiceMock
            ->expects($this->exactly(3))
            ->method('getEmailSent')
            ->willReturnOnConsecutiveCalls(true, false, false);
        $invoiceMock
            ->expects($this->once())
            ->method('sendEmail');
        $this->replaceByMock('model', 'sales/order', $invoiceMock);

        $order = Mage::getModel('sales/order');
        Mage::helper('ops/data')->sendTransactionalEmail($order);
        Mage::helper('ops/data')->sendTransactionalEmail($order);
        Mage::helper('ops/data')->sendTransactionalEmail($order);
    }
}

