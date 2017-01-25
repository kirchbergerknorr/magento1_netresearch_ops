<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2016 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * FlexTest.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Model_Payment_FlexTest extends EcomDev_PHPUnit_Test_Case
{

    protected function getAdditionalInfo()
    {
        return array(

            Netresearch_OPS_Model_Payment_Flex::INFO_KEY_TITLE => 'Foobar',
            Netresearch_OPS_Model_Payment_Flex::INFO_KEY_PM    => 'foo',
            Netresearch_OPS_Model_Payment_Flex::INFO_KEY_BRAND => 'bar'
        );
    }

    public function testGetOpsCode()
    {
        $payment = Mage::getModel('sales/order_payment');
        $additionalInfo = $this->getAdditionalInfo();
        $payment->setMethod(Netresearch_OPS_Model_Payment_Flex::CODE)
            ->setAdditionalInformation(
                $additionalInfo
            );
        /** @var Netresearch_OPS_Model_Payment_Flex $subject */
        $subject = $payment->getMethodInstance();

        $this->assertEquals($additionalInfo[Netresearch_OPS_Model_Payment_Flex::INFO_KEY_PM], $subject->getOpsCode());
    }

    public function testGetOpsBrand()
    {
        $payment = Mage::getModel('sales/order_payment');
        $additionalInfo = $this->getAdditionalInfo();
        $payment->setMethod(Netresearch_OPS_Model_Payment_Flex::CODE)
            ->setAdditionalInformation(
                $additionalInfo
            );
        /** @var Netresearch_OPS_Model_Payment_Flex $subject */
        $subject = $payment->getMethodInstance();

        $this->assertEquals(
            $additionalInfo[Netresearch_OPS_Model_Payment_Flex::INFO_KEY_BRAND], $subject->getOpsBrand()
        );

    }
    
}
