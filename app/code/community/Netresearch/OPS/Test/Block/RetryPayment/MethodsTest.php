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
 * Methods.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 */
class Netresearch_OPS_Test_Block_RetryPayment_MethodsTest extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     */
    public function getMethods()
    {
        $this->mockSessions();

        /** @var Netresearch_OPS_Block_RetryPayment_Methods $retryBlockMock */
        $retryBlockMock = $this->getBlockMock('ops/retryPayment_methods', array('_canUseMethod'));
        $retryBlockMock->expects($this->any())
                       ->method('_canUseMethod')
                       ->will($this->returnValue(true));

        $method = Mage::getModel('ops/payment_ops_cc');
        $retryBlockMock->setMethods(array($method));

        $paymentMethodMock = $this->getModelMock('ops/payment_abstract', array('isApplicableToQuote'));
        $paymentMethodMock->expects($this->any())
                          ->method('isApplicableToQuote')
                          ->will($this->returnValue(true));

        $paymentHelperMock = $this->getHelperMock('payment', array('getStoreMethods'));
        $paymentHelperMock->expects($this->any())
                          ->method('getStoreMethods')
                          ->will($this->returnValue(array($paymentMethodMock)));
        $this->replaceByMock('helper', 'payment', $paymentHelperMock);

        $result = $retryBlockMock->getMethods();

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, count($result));

    }

    /**
     * Helper Function to remove Session Errors
     */
    protected function mockSessions()
    {
        $sessionMock = $this->getModelMockBuilder('admin/session')
                            ->disableOriginalConstructor()// This one removes session_start and other methods usage
                            ->getMock();
        $this->replaceByMock('singleton', 'admin/session', $sessionMock);

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
