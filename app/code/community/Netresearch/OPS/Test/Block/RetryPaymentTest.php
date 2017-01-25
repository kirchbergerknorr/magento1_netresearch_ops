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
 * RetryPaymentTest.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 */
class Netresearch_OPS_Test_Block_RetryPaymentTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     */
    public function getFormAction()
    {
        $this->mockSessions();
        $retryBlock = new Netresearch_OPS_Block_RetryPayment();

        $result = $retryBlock->getFormAction();

        $this->assertInternalType('string', $result);
        $this->assertContains('http', $result);

    }

    /**
     * @test
     */
    public function getCancelUrl()
    {
        $this->mockSessions();
        $retryBlock = new Netresearch_OPS_Block_RetryPayment();

        $result = $retryBlock->getCancelUrl();

        $this->assertInternalType('string', $result);
        $this->assertContains('http', $result);

    }

    /**
     * @test
     */
    public function getOrderId()
    {
        $this->mockSessions();
        $retryBlock = new Netresearch_OPS_Block_RetryPayment();
        $orderId    = '100000023';

        Mage::app()->getRequest()->setParam('orderID', $orderId);

        $result = $retryBlock->getOrderId();

        $this->assertContains($orderId, $result);

    }

    /**
     * Helper Function to remove Session Errors
     */
    protected function mockSessions()
    {
        $sessionMock = $this->getModelMockBuilder('checkout/session')
                            ->disableOriginalConstructor()// This one removes session_start and other methods usage
                            ->getMock();
        $this->replaceByMock('singleton', 'core/session', $sessionMock);

        $sessionMock = $this->getModelMockBuilder('customer/session')
                            ->disableOriginalConstructor()// This one removes session_start and other methods usage
                            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
    }

}
