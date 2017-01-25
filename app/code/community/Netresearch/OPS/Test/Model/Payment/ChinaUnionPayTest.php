<?php


class Netresearch_OPS_Test_Model_Payment_ChinaUnionPayTest extends EcomDev_PHPUnit_Test_Case
{

    protected $model = null;

    public function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('ops/payment_chinaUnionPay');
        $this->model->setInfoInstance(Mage::getModel('payment/info'));
    }

    /**
     * assure that CUP can not capture partial, because invoice is always created on feedback in this case
     */
    public function testCanCapturePartial()
    {
        $this->assertFalse($this->model->canCapturePartial());
    }

    public function testGetOpsCode()
    {
        $this->assertEquals('PAYDOL_UPOP', $this->model->getOpsCode());
    }

    public function testGetOpsBrand()
    {
        $this->assertEquals('UnionPay', $this->model->getOpsBrand());
    }


    public function testCanRefundPartialPerInvoice()
    {
        $this->assertFalse($this->model->canRefundPartialPerInvoice());
    }

    public function testGetPaymentAction()
    {
        $this->assertEquals(
            Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
            $this->model->getPaymentAction()
        );
    }
}
