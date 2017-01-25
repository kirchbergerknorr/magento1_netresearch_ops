<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Model_Validator_Kwixo_Shipping_Setting
    extends EcomDev_PHPUnit_Test_Case_Config
{

    private function getValidator()
    {
        return Mage::getModel('ops/validator_kwixo_shipping_setting');
    }

    public function testKwixoShippingTypeIsValid()
    {
        $validator = $this->getValidator();
        $this->assertFalse($validator->isValid(array()));
        $falseData = array('dhl' => array('kwixo_shipping_type' => 'abc'));
        $this->assertFalse($validator->isValid($falseData));
        $this->assertTrue(0 < count($validator->getMessages()));
        $falseData = array('dhl' => array('foo' => 'abc'));
        $this->assertFalse($validator->isValid($falseData));
        $falseData = array('dhl' => array('kwixo_shipping_type' => 0));
        $this->assertFalse($validator->isValid($falseData));
        $falseData = array('dhl' => array('kwixo_shipping_type' => 6));
        $this->assertFalse($validator->isValid($falseData));
        $messages = $validator->getMessages();
        $this->assertEquals(
            'invalid shipping type provided',
            $messages['dhl']['kwixo_shipping_type_error']
        );
        $correctData = array(
            'dhl' => array(
                'kwixo_shipping_type' => 4, 'kwixo_shipping_speed' => 1,
                'kwixo_shipping_details' => ''
            )
        );
        $this->assertTrue($validator->isValid($correctData));
    }

    public function testKwixoShippingSpeedIsValid()
    {
        $validator = $this->getValidator();
        $this->assertFalse($validator->isValid(array()));
        $falseData = array(
            'dhl' => array(
                'kwixo_shipping_speed' => 'abc', 'kwixo_shipping_type' => 4
            )
        );
        $this->assertFalse($validator->isValid($falseData));
        $this->assertTrue(0 < count($validator->getMessages()));
        $falseData = array(
            'dhl' => array(
                'foo' => 'abc', 'kwixo_shipping_type' => 4
            )
        );
        $this->assertFalse($validator->isValid($falseData));
        $falseData = array(
            'dhl' => array(
                'kwixo_shipping_speed' => 0, 'kwixo_shipping_type' => 4
            )
        );
        $this->assertFalse($validator->isValid($falseData));
        $messages = $validator->getMessages();
        $this->assertEquals(
            'invalid shipping speed provided',
            $messages['dhl']['kwixo_shipping_speed_error']
        );
        $correctData = array(
            'dhl' => array(
                'kwixo_shipping_speed' => 4, 'kwixo_shipping_type' => 4,
                'kwixo_shipping_details' => ''
            )
        );
        $this->assertTrue($validator->isValid($correctData));
    }

    public function testKwixoShippingDetailsIsValid()
    {
        $validator = $this->getValidator();
        $this->assertFalse($validator->isValid(array()));
        $falseData = array(
            'dhl' => array(
                'kwixo_shipping_speed' => 4, 'kwixo_shipping_type' => 4
            )
        );
        $this->assertFalse($validator->isValid($falseData));
        $correctData = array(
            'dhl' => array(
                'kwixo_shipping_speed' => 4, 'kwixo_shipping_type' => 4,
                'kwixo_shipping_details' => ''
            )
        );
        $this->assertTrue($validator->isValid($correctData));
        $longString = '012345678901234567890123456789012345678901234567891';
        $falseData  = array(
            'dhl' => array(
                'kwixo_shipping_speed' => 4, 'kwixo_shipping_type' => 4,
                'kwixo_shipping_details' => $longString
            )
        );
        $this->assertFalse($validator->isValid($falseData));
        $messages = $validator->getMessages();
        $this->assertEquals(
            'invalid shipping details provided',
            $messages['dhl']['kwixo_shipping_details_error']
        );
        $longString  = '01234567890123456789012345678901234567890123456789';
        $correctData = array(
            'dhl' => array(
                'kwixo_shipping_speed' => 4, 'kwixo_shipping_type' => 4,
                'kwixo_shipping_details' => $longString
            )
        );
        $this->assertTrue($validator->isValid($correctData));
    }

} 