<?php
/**
 * @category   OPS
 * @package    Netresearch_OPS
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_OPS_Test_Block_Alias_ListTest
 *
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Test_Block_Alias_ListTest
    extends EcomDev_PHPUnit_Test_Case
{
    private $block;

    public function setUp()
    {
        parent::setup();
        $this->block = Mage::app()->getLayout()->getBlockSingleton('ops/alias_list');
    }

    public function testGetMethodName()
    {
        $this->assertEquals('', $this->block->getMethodName('something_stupid'));

        Mage::app()->getStore()->setConfig('payment/ops_cc/title', 'OPS Credit Card');
        $this->assertEquals(
            'OPS Credit Card',
            $this->block->getMethodName('ops_cc')
        );
    }

    public function testGetAliasDeleteUrl()
    {
//        array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure());
        $this->mockSessions();
        $aliasId = 1;
        // previous behaviour until OGNB-229: Mage::getUrl('ops/customer/deleteAlias/id/' . $aliasId)
        $this->assertEquals(Mage::getUrl('ops/customer/deleteAlias/id/' . $aliasId), $this->block->getAliasDeleteUrl($aliasId));

        // new since OGNB-229: passed aliasId as array param
        $this->assertEquals(Mage::getUrl('ops/customer/deleteAlias/', array('id' => $aliasId)), $this->block->getAliasDeleteUrl($aliasId));

        $_SERVER['HTTPS'] = 'on';
        $this->assertEquals(
            Mage::getUrl(
                'ops/customer/deleteAlias/',
                array(
                     '_secure'  => true,
                     'id'       => $aliasId)
            ),
            $this->block->getAliasDeleteUrl($aliasId)
        );
    }
    protected function mockSessions()
    {
        $sessionMock = $this->getModelMockBuilder('checkout/session')
                            ->disableOriginalConstructor() // This one removes session_start and other methods usage
                            ->getMock();
        $this->replaceByMock('singleton', 'core/session', $sessionMock);

        $sessionMock = $this->getModelMockBuilder('customer/session')
                            ->disableOriginalConstructor() // This one removes session_start and other methods usage
                            ->getMock();
        $this->replaceByMock('singleton', 'customer/session', $sessionMock);
    }
    
}

