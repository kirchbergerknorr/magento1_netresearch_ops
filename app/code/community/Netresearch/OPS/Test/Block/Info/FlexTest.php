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


class Netresearch_OPS_Test_Block_Info_FlexTest extends EcomDev_PHPUnit_Test_Case
{
    public function testGetFlexTitle()
    {
        $payment = Mage::getModel('sales/order_payment');
        $payment->setAdditionalInformation(Netresearch_OPS_Model_Payment_Flex::INFO_KEY_TITLE, 'FLEX');

        /** @var Netresearch_OPS_Block_Info_Flex $block */
        $block = Mage::app()->getLayout()->createBlock('ops/info_flex');
        $method = Mage::getModel('ops/payment_flex');
        $method->setData('info_instance', $payment);
        $payment->setMethodInstance($method);
        $block->setInfo($payment);

        $this->assertEquals('FLEX', $block->getFlexTitle());
    }
    
}
