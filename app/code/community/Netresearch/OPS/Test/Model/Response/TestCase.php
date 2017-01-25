<?php
/**
 * Netresearch OPS
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
 * PHP version 5
 *
 * @category  Netresearch
 * @package   Netresearch_OPS
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

/**
 * Netresearch_OPS_Test_Model_Response_TestCase
 *
 * @category Netresearch
 * @package  Netresearch_OPS
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
abstract class Netresearch_OPS_Test_Model_Response_TestCase extends EcomDev_PHPUnit_Test_Case
{
    protected function mockOrderConfig()
    {
        $configMock = $this->getModelMock('sales/order_config', array('getDefaultStatus'));
        $configMock
            ->expects($this->any())
            ->method('getDefaultStatus')
            ->will($this->returnArgument(0));
        $this->replaceByMock('singleton', 'sales/order_config', $configMock);
    }

    /**
     * Assert order confirmation email being (not) sent.
     *
     * @param PHPUnit_Framework_MockObject_Matcher_Invocation $matcher
     */
    protected function mockEmailHelper(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        $helperMock = $this->getHelperMock('ops/data', array('sendTransactionalEmail'));
        $helperMock
            ->expects($matcher)
            ->method('sendTransactionalEmail')
            ->with($this->isInstanceOf('Mage_Sales_Model_Order'));
        $this->replaceByMock('helper', 'ops/data', $helperMock);
    }
}
