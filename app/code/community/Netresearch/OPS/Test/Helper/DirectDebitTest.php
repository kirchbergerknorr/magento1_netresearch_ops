<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Helper_DirectDebitTest
    extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @return Netresearch_OPS_Helper_DirectDebit
     */
    protected function getDirectDebitHelper()
    {
        return Mage::helper('ops/directDebit');
    }


    public function testGetDataHelper()
    {
        $this->assertTrue(
            $this->getDirectDebitHelper()->getDataHelper() instanceof
            Netresearch_OPS_Helper_Data
        );
    }

    public function testSetDataHelper()
    {
        $this->getDirectDebitHelper()->setDataHelper(Mage::helper('ops/data'));

        $this->assertTrue(
            $this->getDirectDebitHelper()->getDataHelper() instanceof
            Netresearch_OPS_Helper_Data
        );
    }

    public function testGetQuoteHelper()
    {
        $this->assertTrue(
            $this->getDirectDebitHelper()->getQuoteHelper() instanceof
            Netresearch_OPS_Helper_Quote
        );
    }

    public function testSetQuoteHelper()
    {
        $this->getDirectDebitHelper()->setQuoteHelper(Mage::helper('ops/quote'));

        $this->assertTrue(
            $this->getDirectDebitHelper()->getQuoteHelper() instanceof
            Netresearch_OPS_Helper_Quote
        );
    }

    public function testGetOrderHelper()
    {
        $this->assertTrue(
            $this->getDirectDebitHelper()->getOrderHelper() instanceof
            Netresearch_OPS_Helper_Order
        );
    }

    public function testSetOrderHelper()
    {
        $this->getDirectDebitHelper()->setOrderHelper(Mage::helper('ops/order'));

        $this->assertTrue(
            $this->getDirectDebitHelper()->getOrderHelper() instanceof
            Netresearch_OPS_Helper_Order
        );
    }

    public function testGetCustomerHelper()
    {
        $this->assertTrue(
            $this->getDirectDebitHelper()->getCustomerHelper() instanceof
            Mage_Customer_Helper_Data
        );
    }

    public function testSetCustomerHelper()
    {
        $this->getDirectDebitHelper()->setCustomerHelper(Mage::helper('customer/data'));

        $this->assertTrue(
            $this->getDirectDebitHelper()->getCustomerHelper() instanceof
            Mage_Customer_Helper_Data
        );
    }

    public function testHandleAdminPayment()
    {
        $quote = Mage::getModel('sales/quote');
        $this->assertTrue(
            $this->getDirectDebitHelper()->handleAdminPayment($quote, array())
            instanceof Netresearch_OPS_Helper_DirectDebit
        );
    }

    /**
     * @loadFixture orders.yaml
     */
    public function testGetPaymentSpecificParamsForFrontend()
    {
        $this->mockSessions();
        $order  = Mage::getModel('sales/order')->load(1);
        $quote = Mage::getModel('sales/quote')->load(1);
        $helper = $this->getDirectDebitHelper();
        $params = array(
            'country_id' => 'de',
            'CN' => 'Account Holder',
            'account' => '1234567',
            'bankcode' => '1234567'
        );
        $result = $helper->getDirectLinkRequestParams($quote, $order, $params);

        $this->assertTrue((isset($result['BRAND'])) && $result['BRAND'] = "Direct Debits DE");
        $this->assertTrue((isset($result['PM'])) && $result['PM'] = "Direct Debits DE");
        $this->assertTrue((isset($result['ALIAS'])) && $result['ALIAS'] = "0000000012385139");
    }

    protected function mockSessions()
    {
        $sessionMock = $this->getModelMockBuilder('admin/session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->replaceByMock('singleton', 'admin/session', $sessionMock);

        $sessionMock = $this->getModelMockBuilder('core/session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->replaceByMock('singleton', 'core/session', $sessionMock);

        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
    }
}