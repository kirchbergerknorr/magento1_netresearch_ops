<?php
class Netresearch_OPS_Test_Helper_AddressTest extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @param string $street
     *
     * @test
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function splitStreet($street)
    {
        /** @var Netresearch_OPS_Helper_Address $helper */
        $helper   = Mage::helper('ops/address');
        $split    = $helper->splitStreet($street);
        $expected = $this->expected('auto')->getData();

        $this->assertEquals($expected, $split);
    }
}

