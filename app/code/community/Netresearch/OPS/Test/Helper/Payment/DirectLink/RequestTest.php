<?php

/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Helper_Payment_DirectLink_RequestTest extends EcomDev_PHPUnit_Test_Case_Controller
{

    /**
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetBaseParams()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $quote->getPayment()->setMethod('ops_cc');
        $configMock = $this->getModelMock('ops/config', array('canSubmitExtraParameter'));
        $configMock->expects($this->any())
            ->method('canSubmitExtraParameter')
            ->will($this->returnValue(false));
        $paramHelper = Mage::helper('ops/creditcard');
        $paramHelper->setConfig($configMock);
        $paramHelper->getRequestHelper()->setConfig($configMock);
        $params = $paramHelper->getDirectLinkRequestParams($quote, $order);
        foreach ($this->getOwnerParams() as $ownerParam) {
            $this->assertArrayNotHasKey($ownerParam, $params);
        }
        foreach ($this->getShippingParams() as $shippingParam) {
            $this->assertArrayNotHasKey($shippingParam, $params);
        }
        $this->assertArrayHasKey('RTIMEOUT', $params);
    }

    /**
     * @loadFixture ../../../../../var/fixtures/orders.yaml
     */
    public function testGetExtraParams()
    {
        $order = Mage::getModel('sales/order')->load(11);
        $quote = Mage::getModel('sales/quote');
        $quote->getPayment()->setMethod('ops_cc');
        $configMock = $this->getModelMock('ops/config', array('canSubmitExtraParameter'));
        $configMock->expects($this->any())
            ->method('canSubmitExtraParameter')
            ->will($this->returnValue(true));
        $paramHelper = Mage::helper('ops/creditcard');
        $paramHelper->setConfig($configMock);
        $paramHelper->getRequestHelper()->setConfig($configMock);
        $params = $paramHelper->getDirectLinkRequestParams($quote, $order);
        foreach ($this->getOwnerParams() as $ownerParam) {
            $this->assertArrayHasKey($ownerParam, $params);
        }
        foreach ($this->getShippingParams() as $shippingParam) {
            $this->assertArrayHasKey($shippingParam, $params);
        }
        $this->assertArrayHasKey('RTIMEOUT', $params);

    }

    protected function getOwnerParams()
    {
        return $ownerParams = array(
            'OWNERADDRESS',
            'OWNERTOWN',
            'OWNERZIP',
            'OWNERTELNO',
            'OWNERCTY',
            'ECOM_BILLTO_POSTAL_POSTALCODE',
        );
    }

    protected function getShippingParams()
    {
        $paramValues = array(
            'ECOM_SHIPTO_POSTAL_NAME_FIRST',
            'ECOM_SHIPTO_POSTAL_NAME_LAST',
            'ECOM_SHIPTO_POSTAL_STREET_LINE1',
            'ECOM_SHIPTO_POSTAL_STREET_LINE2',
            'ECOM_SHIPTO_POSTAL_COUNTRYCODE',
            'ECOM_SHIPTO_POSTAL_CITY',
            'ECOM_SHIPTO_POSTAL_POSTALCODE'
        );

        return $paramValues;
    }
} 