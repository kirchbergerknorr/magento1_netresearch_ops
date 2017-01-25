<?php
class Netresearch_OPS_Test_Block_PlaceformShaTest extends EcomDev_PHPUnit_Test_Case
{
    private $_helper;
    private $_shaKey;

    public function setUp()
    {
        parent::setup();

        $this->_helper = new Netresearch_OPS_Helper_Payment();
        $this->_shaKey = 'qu4rkkuchen12345';
    }

    public function testShaGenerationWithTestData()
    {
        $params = array(
            'AMOUNT'                        => '2129',
            'CIVILITY'                      => 'Herr',
            'CURRENCY'                      => 'EUR',
            'ECOM_BILLTO_POSTAL_NAME_FIRST' => 'John',
            'ECOM_BILLTO_POSTAL_NAME_LAST'  => 'Doe',
            'ECOM_SHIPTO_DOB'               => '09/10/1940',
            'EMAIL'                         => 'john@doe.com',
            'ITEMID1'                       => 'article1',
            'ITEMNAME1'                     => 'coffee',
            'ITEMPRICE1'                    => '3.00',
            'ITEMQUANT1'                    => '4',
            'ITEMVAT1'                      => '0.57',
            'LANGUAGE'                      => 'de_DE',
            'ORDERID'                       => 'order123',
            'ORDERSHIPCOST'                 => '100',
            'ORDERSHIPTAX'                  => '6',
            'OWNERADDRESS'                  => 'test street',
            'OWNERCTY'                      => 'DE',
            'OWNERTELNO'                    => '+49 111 222 33 444',
            'OWNERTOWN'                     => 'Berlin',
            'OWNERZIP'                      => '10000',
            'PM'                            => 'Open Invoice DE',
            'PSPID'                         => 'NRMAGbillpay1',
        );
        $expected = '695103f8891dfc80ea46369203925b898a381334';
        $shaSign = $this->_helper->getSHASign($params, $this->_shaKey, 0);
        $result   = $this->_helper->shaCrypt($shaSign);
        $this->assertEquals($expected, $result);
    }

    public function testShaGenerationWithSpecialChars()
    {
        $params = array(
            'PSPID'                         => 'NRMAGbillpay1',
            'AMOUNT'                        => '560',
            'ORDERID'                       => 'TBI72',
            'CURRENCY'                      => 'EUR',
            'OWNERCTY'                      => 'DE',
            'ITEMVAT1'                      => '0.10',
            'LANGUAGE'                      => 'de_DE',
            'PM'                            => 'Open Invoice DE',
            'CIVILITY'                      => 'Herr',
            'EMAIL'                         => 'thomas.kappel@netresearch.de',
            'ORDERSHIPCOST'                 => '500',
            'ORDERSHIPTAX'                  => '0',
            'ECOM_BILLTO_POSTAL_NAME_FIRST' => 'Karla',
            'ECOM_BILLTO_POSTAL_NAME_LAST'  => 'Kolumna',
            'OWNERTELNO'                    => '64065460',
            'ITEMPRICE1'                    => '0.60',
            'ECOM_SHIPTO_DOB'               => '09/10/1940',
            'OWNERADDRESS'                  => 'Tierparkallee 2',
            'OWNERTOWN'                     => 'Leipzig',
            'ITEMID1'                       => '26',
            'ITEMNAME1'                     => 'Club Mate',
            'ITEMQUANT1'                    => '1',
            'OWNERZIP'                      => '04229',
        );
        $expected = 'baf6099446e3bf93ecf26e622032e7db2139839c';
        $shaSign = $this->_helper->getSHASign($params, $this->_shaKey, 0);
        $result   = $this->_helper->shaCrypt($shaSign);
        $this->assertEquals($expected, $result);
    }
}

