<?php

class Netresearch_OPS_Test_Block_Form_CcTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var  Netresearch_OPS_Block_Form_Cc $_block */
    private $_block;

    public function setUp()
    {
        parent::setup();
        $this->_block = Mage::app()->getLayout()->getBlockSingleton('ops/form_cc');
        $this->_block->setMethod(Mage::getModel('ops/payment_cc'));
        $this->mockSessions();
    }

    public function testGetAliasBrands()
    {

        $aliasBrands = array(
            'American Express',
            'Diners Club',
            'MaestroUK',
            'MasterCard',
            'VISA',
        );

        $ccAliasInterfaceEnabledTypesMock = $this->getModelMock(
            'ops/source_cc_aliasInterfaceEnabledTypes', array('getAliasInterfaceCompatibleTypes')
        );
        $ccAliasInterfaceEnabledTypesMock->expects($this->any())
            ->method('getAliasInterfaceCompatibleTypes')
            ->will($this->returnValue($aliasBrands));
        $this->replaceByMock('model', 'ops/source_cc_aliasInterfaceEnabledTypes', $ccAliasInterfaceEnabledTypesMock);
        /** @var Netresearch_OPS_Block_Form_Cc $ccForm */
        $ccForm = Mage::app()->getLayout()->getBlockSingleton('ops/form_cc');
        $ccAliases = $ccForm->getAliasBrands();
        $this->assertEquals($aliasBrands, $ccAliases);
    }


    public function testTemplate()
    {
        //Frontend case
        $modelMock = $this->getModelMock('ops/config', array('isFrontendEnvironment'));
        $modelMock->expects($this->any())
            ->method('isFrontendEnvironment')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/config', $modelMock);
        $ccForm = new Netresearch_OPS_Block_Form_Cc();
        $this->assertEquals(Netresearch_OPS_Block_Form_Cc::FRONTEND_TEMPLATE, $ccForm->getTemplate());

        //Backend case
        $modelMock = $this->getModelMock('ops/config', array('isFrontendEnvironment'));
        $modelMock->expects($this->any())
            ->method('isFrontendEnvironment')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'ops/config', $modelMock);
        $ccForm = new Netresearch_OPS_Block_Form_Cc();
        $this->assertEquals(Netresearch_OPS_Block_Form_Cc::FRONTEND_TEMPLATE, $ccForm->getTemplate());
    }

    public function testGetCcBrands()
    {
        $blockMock = $this->getBlockMock('ops/form_cc', array('getMethod'));
        $method = new Varien_Object();
        $method->setCode('ops_cc');
        $blockMock->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $this->replaceByMock('block', 'ops/form_cc', $blockMock);


        $this->assertInternalType('array', $blockMock->getCcBrands());
    }

    public function testIsAliasPMEnabled()
    {
        $model = Mage::getModel('ops/config');
        $this->assertEquals(
            $model->isAliasManagerEnabled('ops_cc'), $this->_block->isAliasPMEnabled()
        );
    }

    public function testGetStoredAliasDataForCustomer()
    {

        $reflectionClass = new ReflectionClass(get_class($this->_block));
        $method = $reflectionClass->getMethod("getStoredAliasDataForCustomer");
        $method->setAccessible(true);
        $this->assertNull($method->invoke($this->_block, 0, 'bla'));

        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasForCustomer')
        );
        $blockMock->expects($this->once())
            ->method('getStoredAliasForCustomer')
            ->will($this->returnValue(array()));
        $reflectionClass = new ReflectionClass(get_class($blockMock));
        $method = $reflectionClass->getMethod("getStoredAliasDataForCustomer");
        $method->setAccessible(true);
        $this->assertNull($method->invoke($blockMock, 0, 'bla'));


        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasForCustomer')
        );
        $blockMock->expects($this->once())
            ->method('getStoredAliasForCustomer')
            ->will($this->returnValue(array(0 => new Varien_Object(array('bla' => 'foo')))));
        $reflectionClass = new ReflectionClass(get_class($blockMock));
        $method = $reflectionClass->getMethod("getStoredAliasDataForCustomer");
        $method->setAccessible(true);
        $this->assertEquals(
            'foo', $method->invoke($blockMock, 0, 'bla')
        );

        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasForCustomer')
        );
        $blockMock->expects($this->once())
            ->method('getStoredAliasForCustomer')
            ->will($this->returnValue(array(0 => new Varien_Object(array('bla' => 'foo')))));
        $reflectionClass = new ReflectionClass(get_class($blockMock));
        $method = $reflectionClass->getMethod("getStoredAliasDataForCustomer");
        $method->setAccessible(true);
        $this->assertNull($method->invoke($blockMock, 0, 'foo'));

    }

    public function testGetAliasCardNumber()
    {
        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasForCustomer')
        );
        $blockMock->expects($this->any())
            ->method('getStoredAliasForCustomer')
            ->will(
                $this->returnValue(
                    array(
                        0 => new Varien_Object(
                            array(
                                'pseudo_account_or_cc_no' => 'xxxxxxxxxxxx1111',
                                'brand'                   => 'visa'
                            )
                        )
                    )
                )
            );
        $this->assertEquals('XXXX XXXX XXXX 1111', $blockMock->getAliasCardNumber(0));
    }

    /**
     * @loadFixture aliases.yaml
     */
    public function testGetStoredAliasForCustomer()
    {

        $reflectionClass = new ReflectionClass(get_class($this->_block));
        $method = $reflectionClass->getMethod("getStoredAliasForCustomer");
        $method->setAccessible(true);
        $this->assertEquals(0, count($method->invoke($this->_block)));


        $configMock = $this->getModelMock('ops/config', array('isAliasManagerEnabled'));
        $configMock->expects($this->any())
            ->method('isAliasManagerEnabled')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/config', $configMock);

        $aliases = Mage::getModel('ops/alias')
            ->getCollection()
            ->addFieldToFilter('customer_id', 1)
            ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
            ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC);
        $alias = $aliases->getFirstItem();
        $aliasHelperMock = $this->getHelperMock('ops/alias', array('getAliasesForAddresses'));
        $aliasHelperMock->expects($this->once())
            ->method('getAliasesForAddresses')
            ->will($this->returnValue($aliases));
        $this->replaceByMock('helper', 'ops/alias', $aliasHelperMock);

        $customerMock = $this->getHelperMock('customer/data', array('isLoggedIn'));
        $customerMock->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'customer/data', $customerMock);

        $fakeCustomer = new Varien_Object();
        $fakeCustomer->setId(1);

        $fakeQuote = new Varien_Object();
        $fakeQuote->setCustomer($fakeCustomer);


        $blockMock = $this->getBlockMock('ops/form_cc', array('getQuote', 'getMethodCode'));
        $blockMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($fakeQuote));
        $blockMock->expects($this->any())
            ->method('getMethodCode')
            ->will($this->returnValue('ops_cc'));
        $this->replaceByMock('block', 'ops/form_cc', $blockMock);

        $reflectionClass = new ReflectionClass(get_class($blockMock));
        $method = $reflectionClass->getMethod("getStoredAliasForCustomer");
        $method->setAccessible(true);
        $aliases = $method->invoke($blockMock);
        $this->assertEquals($alias->getData(), $aliases[1]->getData());

    }


    public function testGetExpirationDatePart()
    {
        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasForCustomer')
        );
        $blockMock->expects($this->any())
            ->method('getStoredAliasForCustomer')
            ->will($this->returnValue(array(0 => new Varien_Object(array('expiration_date' => '0416')))));
        $this->assertEquals('04', $blockMock->getExpirationDatePart(0, 'month'));
        $this->assertEquals('16', $blockMock->getExpirationDatePart(0, 'year'));
    }

    public function testGetCardHolderName()
    {
        $configMock = $this->getModelMock('ops/config', array('isAliasManagerEnabled'));
        $configMock->expects($this->any())
            ->method('isAliasManagerEnabled')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'ops/config', $configMock);

        $this->assertNull($block = $this->_block->getCardHolderName(2));

        $configMock = $this->getModelMock('ops/config', array('isAliasManagerEnabled'));
        $configMock->expects($this->any())
            ->method('isAliasManagerEnabled')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/config', $configMock);


        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasDataForCustomer', 'getStoredAlias')
        );

        $blockMock->expects($this->any())
            ->method('getStoredAliasDataForCustomer')
            ->will($this->returnValue('Hubertus von Fürstenberg'));

        $blockMock->expects($this->any())
            ->method('getStoredAlias')
            ->will($this->returnValue('4711'));
        $this->assertEquals('Hubertus von Fürstenberg', $blockMock->getCardHolderName(2));

        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasDataForCustomer', 'getStoredAlias', 'getMethodCode')
        );
        $blockMock->expects($this->any())
            ->method('getMethodCode')
            ->will($this->returnValue('ops_cc'));
        $blockMock->expects($this->once())
            ->method('getStoredAliasDataForCustomer')
            ->will($this->returnValue(null));

        $blockMock->expects($this->any())
            ->method('getStoredAlias')
            ->will($this->returnValue('4711'));

        $customerHelperMock = $this->getHelperMock('customer/data', array('isLoggedIn', 'getCustomerName'));
        $customerHelperMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));
        $customerHelperMock->expects($this->any())
            ->method('getCustomerName')
            ->will($this->returnValue('Hubertus zu Fürstenberg'));
        $this->replaceByMock('helper', 'customer/data', $customerHelperMock);


        $this->assertEquals('Hubertus zu Fürstenberg', $blockMock->getCardHolderName(2));

        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasDataForCustomer', 'getStoredAlias', 'getMethodCode')
        );
        $blockMock->expects($this->any())
            ->method('getMethodCode')
            ->will($this->returnValue('ops_cc'));
        $blockMock->expects($this->once())
            ->method('getStoredAliasDataForCustomer')
            ->will($this->returnValue(''));

        $blockMock->expects($this->any())
            ->method('getStoredAlias')
            ->will($this->returnValue('4711'));

        $this->assertEquals('Hubertus zu Fürstenberg', $blockMock->getCardHolderName(2));

        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasDataForCustomer', 'getStoredAlias')
        );
        $blockMock->expects($this->once())
            ->method('getStoredAliasDataForCustomer')
            ->will($this->returnValue(null));

        $blockMock->expects($this->any())
            ->method('getStoredAlias')
            ->will($this->returnValue('4711'));

        $customerHelperMock = $this->getHelperMock('customer/data', array('isLoggedIn'));
        $customerHelperMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'customer/data', $customerHelperMock);

        $this->assertNull($blockMock->getCardHolderName(2));


    }

    public function testGetStoredAliasBrandWithInlineBrand()
    {
        /** @var Netresearch_OPS_Block_Form_Cc $blockMock */

        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasForCustomer', 'getMethodCode')
        );
        $blockMock->expects($this->any())
            ->method('getStoredAliasForCustomer')
            ->will(
                $this->returnValue(
                    array(0 => new Varien_Object(
                        array(
                            'pseudo_account_or_cc_no' => 'xxxxxxxxxxxx1111',
                            'brand'                   => 'VISA'
                        )
                    ))
                )
            );
        $blockMock->expects($this->any())
            ->method('getMethodCode')
            ->will($this->returnValue('ops_cc'));

        $modelMock = $this->getModelMock(
            'ops/config', array('getInlinePaymentCcTypes')
        );
        $modelMock->expects($this->any())
            ->method('getInlinePaymentCcTypes')
            ->will(
                $this->returnValue(
                    array(
                        'VISA'
                    )
                )
            );

        $this->assertEquals('VISA', $blockMock->getStoredAliasBrand(0));
    }


    public function testGetStoredAliasBrand()
    {
        /** @var Netresearch_OPS_Block_Form_Cc $blockMock */

        $blockMock = $this->getBlockMock(
            'ops/form_cc', array('getStoredAliasForCustomer', 'getMethodCode')
        );
        $blockMock->expects($this->any())
            ->method('getStoredAliasForCustomer')
            ->will(
                $this->returnValue(
                    array(new Varien_Object(
                        array(
                                  'pseudo_account_or_cc_no' => 'xxxxxxxxxxxx1111',
                                  'brand'                   => 'VISA'
                              )
                    ))
                )
            );
        $blockMock->expects($this->any())
            ->method('getMethodCode')
            ->will($this->returnValue('ops_cc'));

        $modelMock = $this->getModelMock(
            'ops/config', array('getInlinePaymentCcTypes')
        );
        $modelMock->expects($this->any())
            ->method('getInlinePaymentCcTypes')
            ->will(
                $this->returnValue(
                    array(
                        'FOO'
                    )
                )
            );

        $this->replaceByMock('model', 'ops/config', $modelMock);

        $this->assertEquals('VISA', $blockMock->getStoredAliasBrand(0));
    }

    public function testIsAliasInfoBlockEnabled()
    {
        $configMock = $this->getModelMock('ops/config', array('isAliasPMEnabled', 'isAliasInfoBlockEnabled'));
        $configMock->expects($this->any())
            ->method('isAliasPMEnabled')
            ->will($this->returnValue(false));
        $configMock->expects($this->any())
            ->method('isAliasInfoBlockEnabled')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'ops/config', $configMock);

        $this->assertFalse(Mage::app()->getLayout()->getBlockSingleton('ops/form_cc')->isAliasInfoBlockEnabled());

        $configMock = $this->getModelMock('ops/config', array('isAliasPMEnabled', 'isAliasInfoBlockEnabled'));
        $configMock->expects($this->any())
            ->method('isAliasPMEnabled')
            ->will($this->returnValue(false));
        $configMock->expects($this->any())
            ->method('isAliasInfoBlockEnabled')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/config', $configMock);

        $this->assertFalse(Mage::app()->getLayout()->getBlockSingleton('ops/form_cc')->isAliasInfoBlockEnabled());

        $configMock = $this->getModelMock('ops/config', array('isAliasPMEnabled', 'isAliasInfoBlockEnabled'));
        $configMock->expects($this->any())
            ->method('isAliasPMEnabled')
            ->will($this->returnValue(true));
        $configMock->expects($this->any())
            ->method('isAliasInfoBlockEnabled')
            ->will($this->returnValue(false));
        $this->replaceByMock('model', 'ops/config', $configMock);

        $this->assertFalse(Mage::app()->getLayout()->getBlockSingleton('ops/form_cc')->isAliasInfoBlockEnabled());

        $configMock = $this->getModelMock('ops/config', array('isAliasPMEnabled', 'isAliasInfoBlockEnabled'));
        $configMock->expects($this->any())
            ->method('isAliasPMEnabled')
            ->will($this->returnValue(true));
        $configMock->expects($this->any())
            ->method('isAliasInfoBlockEnabled')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'ops/config', $configMock);

        $this->assertFalse(Mage::app()->getLayout()->getBlockSingleton('ops/form_cc')->isAliasInfoBlockEnabled());
    }

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
