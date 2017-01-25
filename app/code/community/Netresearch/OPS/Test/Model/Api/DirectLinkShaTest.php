<?php
class Netresearch_OPS_Test_Model_Api_DirectLinkShaTest extends EcomDev_PHPUnit_Test_Case
{
    private $_model;
    private $_shaKey;

    public function setUp()
    {
        parent::setup();
        $this->_model = Mage::getModel('ops/api_directlink');
        $this->_shaKey = 'ksdf239sdnkvs2e9';
    }

    public function testShaGenerationWithoutSpecialChars()
    {
        $params = array(
            'ALIAS' => 'foo'
        );
        $expected = $params;
        $expected['SHASIGN'] = '44194456a31b8ea1de461612b19f7255732438d5';
        $this->assertEquals($expected, $this->_model->getEncodedParametersWithHash($params, $this->_shaKey, 0));
    }

    public function testShaGenerationWithSpecialChars()
    {
        $params = array(
            'AMOUNT'    => '36980',
            'CARDNO'    => '257354109BLZ86010090',
            'CN'        => 'André Herrn',
            'CURRENCY'  => 'EUR',
            'ED'        => '9999',
            'OPERATION' => 'SAL',
            'ORDERID'   => '20190',
            'PM'        => 'Direct Debits DE',
            'PSPID'     => 'NRMAGENTO',
            'PSWD'      => 'magento1',
            'USERID'    => 'NRMAGENTO1API',
        );
        $expected = $params;
        $expected['SHASIGN'] = 'eb95f7d66879e9801fdbdf75095ce23147202c30';
        $result = $this->_model->getEncodedParametersWithHash($params, $this->_shaKey, 0);
        $this->assertEquals($expected, $result);
    }
}
