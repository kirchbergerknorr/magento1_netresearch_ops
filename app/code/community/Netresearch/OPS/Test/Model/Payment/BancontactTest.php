<?php


class Netresearch_OPS_Test_Model_Payment_BancontactTest extends EcomDev_PHPUnit_Test_Case
{

    protected $model = null;

    public function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('ops/payment_bancontact');
        $this->model->setInfoInstance(Mage::getModel('payment/info'));
    }

    public function testCanCapturePartial()
    {
        $this->assertTrue($this->model->canCapturePartial());
    }

    public function testGetOpsCode()
    {
        $this->assertEquals('CreditCard', $this->model->getOpsCode());
    }

    public function testGetOpsBrand()
    {
        $this->assertEquals('BCMC', $this->model->getOpsBrand());
    }

    /**
     * @loadFixture ../../../../var/fixtures/orders.yaml
     */
    public function testGetMethodDependendFormFields()
    {
        $order = Mage::getModel('sales/order')->load(32);

        $sessionMock = $this->getModelMockBuilder('core/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'core/session', $sessionMock);

        $sessionMock = $this->getModelMockBuilder('customer/session')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);

        $this->model->getInfoInstance()->setAdditionalInformation('DEVICE', Netresearch_OPS_Helper_MobileDetect::DEVICE_TYPE_MOBILE);
        $formFields = $this->model->getMethodDependendFormFields($order, null);
        $this->assertEquals(Netresearch_OPS_Helper_MobileDetect::DEVICE_TYPE_MOBILE, $formFields['DEVICE']);
    }


    public function testAssignData()
    {
        $infoInstance = Mage::getModel('sales/quote_payment');

        $helperMock = new Varien_Object(
            array('device_type' => Netresearch_OPS_Helper_MobileDetect::DEVICE_TYPE_MOBILE)
        );

        $paymentInstance = Mage::getModel('ops/payment_bancontact');
        $paymentInstance->setMobileDetectHelper($helperMock)
            ->setInfoInstance($infoInstance);

        $paymentInstance->assignData(array());
        $additionalInformation = $infoInstance->getData('additional_information');

        $this->assertEquals(Netresearch_OPS_Helper_MobileDetect::DEVICE_TYPE_MOBILE, $additionalInformation['DEVICE']);
    }

    /**
     * @Test
     */
    public function testGetMobileDetectHelper()
    {
        $this->assertTrue($this->model->getMobileDetectHelper() instanceof Netresearch_OPS_Helper_MobileDetect);
    }
}
