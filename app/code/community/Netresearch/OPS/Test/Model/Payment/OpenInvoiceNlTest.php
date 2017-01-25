<?php
class Netresearch_OPS_Test_Model_Payment_OpenInvoiceNlTest extends EcomDev_PHPUnit_Test_Case
{
    protected $model = null;

    public function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('ops/payment_openInvoiceNl');
    }

    public function testQuestionRequired()
    {
        $order = new Varien_Object();
        $requestParams = array(
            'foo'                              => 'bar',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER' => '14'
        );
        $requestParams = array();
        $formFields = array(
            'OWNERADDRESS'                     => '',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER' => '',
            'ECOM_SHIPTO_POSTAL_STREET_LINE1'  => '',
            'ECOM_SHIPTO_POSTAL_STREET_NUMBER' => ''
        );

        $method = $this->getModelMock(
            'ops/payment_openInvoiceNl', array(
            'getFormFields'
            )
        );
        $method->expects($this->any())
            ->method('getFormFields')
            ->will($this->returnValue($formFields));

        $this->assertTrue($method->hasFormMissingParams($order, $requestParams, $formFields), 'expected missing params');
        $this->assertTrue(is_string($method->getQuestion()));
        $this->assertEquals(
            array(
                'OWNERADDRESS',
                'ECOM_BILLTO_POSTAL_STREET_NUMBER',
                'ECOM_SHIPTO_POSTAL_STREET_LINE1',
                'ECOM_SHIPTO_POSTAL_STREET_NUMBER'
            ),
            $method->getQuestionedFormFields($order, $requestParams)
        );
    }

    public function testQuestionNotRequired()
    {
        $order = new Varien_Object();
        $requestParams = array(
            'foo'                              => 'bar',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER' => '14'
        );
        $formFields = array(
            'OWNERADDRESS'                     => 'Nowhere',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER' => '14',
            'ECOM_SHIPTO_POSTAL_STREET_LINE1'  => 'Somewhere',
            'ECOM_SHIPTO_POSTAL_STREET_NUMBER' => '1'
        );

        $method = $this->getModelMock(
            'ops/payment_openInvoiceNl', array(
            'getFormFields'
            )
        );
        $method->expects($this->any())
            ->method('getFormFields')
            ->will($this->returnValue($formFields));

        $this->assertFalse($method->hasFormMissingParams($order, $requestParams, $formFields), 'expected no missing params');

        /* independent from that we expect to get question and questioned params when calling these methods directly */
        $this->assertTrue(is_string($method->getQuestion()));
        $this->assertEquals(
            array(
                'OWNERADDRESS',
                'ECOM_BILLTO_POSTAL_STREET_NUMBER',
                'ECOM_SHIPTO_POSTAL_STREET_LINE1',
                'ECOM_SHIPTO_POSTAL_STREET_NUMBER'
            ),
            $method->getQuestionedFormFields($order, $requestParams)
        );
    }

    /**
     * assure that openInvoiceNL can capture partial
     */
    public function testCanCapturePartial()
    {
        $this->assertTrue($this->model->canCapturePartial());
    }
}
