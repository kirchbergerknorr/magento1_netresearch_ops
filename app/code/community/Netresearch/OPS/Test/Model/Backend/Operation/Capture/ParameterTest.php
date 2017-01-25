<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch_OPS
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Model_Backend_Operation_Capture_ParameterTest extends EcomDev_PHPUnit_Test_Case
{

    public function testGetRequestParams()
    {
        $sessionMock = $this->mockSession('customer/session');
        $sessionMock->disableOriginalConstructor();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $fakePayment = Mage::getModel('sales/order_payment');
        $fakePayment->setOrder(Mage::getModel('sales/order'));
        $fakePayment->setAdditionalInformation(array('paymentId' => '4711'));
        $arrInfo = array('operation' => 'capture');
        $amount = 10;
        $opsPaymentMethod = Mage::getModel('ops/payment_abstract');

        $captureParameterModel = Mage::getModel('ops/backend_operation_capture_parameter');
        $requestParams = $captureParameterModel->getRequestParams($opsPaymentMethod, $fakePayment, $amount, $arrInfo);
        $this->assertArrayHasKey('AMOUNT', $requestParams);
        $this->assertArrayHasKey('PAYID', $requestParams);
        $this->assertArrayHasKey('OPERATION', $requestParams);
        $this->assertArrayHasKey('CURRENCY', $requestParams);

        $this->assertEquals(1000, $requestParams['AMOUNT']);
        $this->assertEquals(4711, $requestParams['PAYID']);
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_PARTIAL, $requestParams['OPERATION']);
        $this->assertEquals(
            Mage::app()->getStore($fakePayment->getOrder()->getStoreId())->getBaseCurrencyCode(),
            $requestParams['CURRENCY']
        );
    }

    public function testGetRequestParamsWithAdditionalParameters()
    {
        $sessionMock = $this->mockSession('customer/session');
        $sessionMock->disableOriginalConstructor();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
        $fakePayment = Mage::getModel('sales/order_payment');
        $fakePayment->setOrder(Mage::getModel('sales/order'));
        $fakePayment->setAdditionalInformation(array('paymentId' => '4711'));
        $fakeInvoice = Mage::getModel('sales/order_invoice');
        $fakePayment->setInvoice($fakeInvoice);
        $arrInfo = array('operation' => 'capture');
        $amount = 10;
        $opsPaymentMethod = Mage::getModel('ops/payment_openInvoiceNl');
        $captureParameterModel = Mage::getModel('ops/backend_operation_capture_parameter');
        $requestParams = $captureParameterModel->getRequestParams($opsPaymentMethod, $fakePayment, $amount, $arrInfo);
        $this->assertArrayHasKey('AMOUNT', $requestParams);
        $this->assertArrayHasKey('PAYID', $requestParams);
        $this->assertArrayHasKey('OPERATION', $requestParams);
        $this->assertArrayHasKey('CURRENCY', $requestParams);

        $this->assertEquals(1000, $requestParams['AMOUNT']);
        $this->assertEquals(4711, $requestParams['PAYID']);
        $this->assertEquals(Netresearch_OPS_Model_Payment_Abstract::OPS_CAPTURE_PARTIAL, $requestParams['OPERATION']);
        $this->assertEquals(
            Mage::app()->getStore($fakePayment->getOrder()->getStoreId())->getBaseCurrencyCode(),
            $requestParams['CURRENCY']
        );
    }

} 