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
 * Netresearch_OPS_Test_Controller_PaymentControllerTest
 *
 * @category  controller
 * @package   Netresearch_OPS
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @author    André Herrn <andre.herrn@netresearch.de>
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Test_Controller_PaymentControllerTest
    extends EcomDev_PHPUnit_Test_Case_Controller
{
    public function setUp()
    {
        parent::setUp();
        $helperMock = $this->getHelperMock(
            'ops/payment', array(
            'shaCryptValidation',
            'cancelOrder',
            'declineOrder',
            'handleException',
            'getSHAInSet',
            'refillCart'
            )
        );
        $helperMock->expects($this->any())
            ->method('shaCryptValidation')
            ->will($this->returnValue(true));

        $this->replaceByMock('helper', 'ops/payment', $helperMock);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testAcceptAction()
    {
        $params = array(
            'orderID' => '#100000011'
        );
        $this->dispatch('ops/payment/accept', $params);
        $this->assertRedirectTo('checkout/onepage/success');

        $params = array(
            'orderID' => '23'
        );
        $this->dispatch('ops/payment/accept', $params);
        $this->assertRedirectTo('checkout/onepage/success');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testExceptionAction()
    {
        $msg = 'Your order has been registered, but your payment is still marked as pending.';
        $msg.= ' Please have patience until the final status is known.';

        $orderId = '11';
        $quoteId = '23';
        $incrementId = '#100000011';


        // assert order increment id parameter handling
        $params = array('orderID' => $incrementId);

        $this->dispatch('ops/payment/exception', $params);
        $this->assertRedirectTo('checkout/onepage/success');
        $this->assertEquals($orderId, Mage::getSingleton('checkout/session')->getLastOrderId());

        $message = Mage::getSingleton('checkout/session')->getMessages()->getLastAddedMessage();
        $this->assertEquals('error', $message->getType());
        $this->assertEquals($msg, $message->getText());


        // assert entity id parameter handling
        $params = array('orderID' => $quoteId);

        $this->dispatch('ops/payment/exception', $params);
        $this->assertRedirectTo('checkout/onepage/success');
        $this->assertEquals($orderId, Mage::getSingleton('checkout/session')->getLastOrderId());

        $message = Mage::getSingleton('checkout/session')->getMessages()->getLastAddedMessage();
        $this->assertEquals('error', $message->getType());
        $this->assertEquals($msg, $message->getText());
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testDeclineAction()
    {
        $routeToDispatch = 'ops/payment/decline';
        $params = array();
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/onepage');


        $params = array(
            'orderID' => '#100000011'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/onepage');

        $params = array(
            'orderID' => '23'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/onepage');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testCancelAction()
    {
        $routeToDispatch = 'ops/payment/cancel';

        $params = array(
            'orderID' => '#100000011'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/cart');

        $params = array(
            'orderID' => '23'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/cart');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testContinueAction()
    {
        $routeToDispatch = 'ops/payment/continue';
        $params = array();
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/cart');


        $params = array(
            'orderID' => '#100000011'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/cart');

        $params = array(
            'orderID' => '23'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('checkout/cart');

        $params = array(
            'orderID'  => '#100000011',
            'redirect' => 'catalog'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('/');

        $params = array(
            'orderID'  => '23',
            'redirect' => 'catalog'
        );
        $this->dispatch($routeToDispatch, $params);
        $this->assertRedirectTo('/');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testRepayActionWithInvalidHash()
    {
        // test 1: hash not valid
        $order = Mage::getModel('sales/order')->load(11);
        $opsOrderId = Mage::helper('ops/order')->getOpsOrderId($order);

        $paymentHelperMock = $this->getHelperMock('ops/payment', array('shaCryptValidation'));
        $paymentHelperMock->expects($this->any())
            ->method('shaCryptValidation')
            ->will($this->returnValue(false));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);


        $params = array('orderID' => $opsOrderId, 'SHASIGN' => 'foo');
        $this->dispatch('ops/payment/retry', $params);
        $this->assertRedirectTo('/');
        $message = Mage::getSingleton('core/session')->getMessages()->getLastAddedMessage();
        $this->assertNotNull($message);
        $this->assertEquals($message->getText(), 'Hash not valid');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testRepayActionWithInvalidOrder()
    {

        // test 1: hash valid, order can not be retried
        // orderID 100000012 - status 5
        $order = Mage::getModel('sales/order')->load(12);
        $opsOrderId = Mage::helper('ops/order')->getOpsOrderId($order);

        $paymentHelperMock = $this->getHelperMock('ops/payment', array('shaCryptValidation'));
        $paymentHelperMock->expects($this->any())
            ->method('shaCryptValidation')
            ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);

        $orderHelperMock = $this->getHelperMock('ops/order', array('getOrder'));
        $orderHelperMock->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $this->replaceByMock('helper', 'ops/order', $orderHelperMock);

        $params = array('orderID' => $opsOrderId, 'SHASIGN' => 'foo');
        $this->dispatch('ops/payment/retry', $params);

        $this->assertRedirectTo('/');

        $message = Mage::getSingleton('core/session')->getMessages()->getLastAddedMessage();
        $this->assertNotNull($message);
        $this->assertEquals(
            $message->getText(), 'Not possible to reenter the payment details for order ' . $order->getIncrementId()
        );
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     */
    public function testRepayActionWithSuccess()
    {
        // test 31: order is fine
        // orderID 100000011

        $order = Mage::getModel('sales/order')->load(11);
        $opsOrderId = Mage::helper('ops/order')->getOpsOrderId($order);

        $paymentHelperMock = $this->getHelperMock('ops/payment', array('shaCryptValidation'));
        $paymentHelperMock->expects($this->any())
            ->method('shaCryptValidation')
            ->will($this->returnValue(true));
        $this->replaceByMock('helper', 'ops/payment', $paymentHelperMock);

        $this->dispatch('ops/payment/retry', $paymentHelperMock->validateOrderForReuse($opsOrderId,1));

        $this->assertLayoutLoaded();
        $this->assertLayoutHandleLoaded('ops_payment_retry');
    }

    /**
     * @test
     */
    public function placeformActionWithoutSuccess()
    {
        $this->dispatch('ops/payment/placeForm');
        $this->assertRedirectTo('checkout/cart');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     * @test
     */
    public function placeformActionWithSuccess()
    {
        $quoteModelMock = $this->getModelMock('sales/quote', array('save'));
        $quoteModelMock->expects($this->any())
                       ->method('save')
                       ->will($this->returnSelf());

        $checkoutSessionMock = $this->getModelMock('checkout/session', array('getLastRealOrderId', 'getQuote'));
        $checkoutSessionMock->expects($this->any())
                            ->method('getLastRealOrderId')
                            ->will($this->returnValue(100000013));
        $checkoutSessionMock->expects($this->any())
                            ->method('getQuote')
                            ->will($this->returnValue($quoteModelMock));
        $this->replaceByMock('singleton', 'checkout/session', $checkoutSessionMock);

        $this->dispatch('ops/payment/placeForm');

        $this->assertLayoutLoaded();
        $this->assertLayoutHandleLoaded('ops_payment_placeform');
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     * @test
     */
    public function updatePaymentAndPlaceFormActionWithException()
    {
        $orderId = 100000013;

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($orderId);

        $orderHelperMock = $this->getHelperMock('ops/order', array('getOrder'));
        $orderHelperMock->expects($this->any())
                        ->method('getOrder')
                        ->will($this->returnValue($order));
        $this->replaceByMock('helper', 'ops/order', $orderHelperMock);


        $this->dispatch('ops/payment/updatePaymentAndPlaceForm', array('orderID' => $orderId));
        $this->assertRedirectTo('checkout/cart');

        /** @var Mage_Core_Model_Session $test */
        $test     = Mage::getSingleton('core/session');
        $messages = $test->getMessages()->count();
        $this->assertEquals(1, $messages);
        $test->getMessages()->clear();
    }

    /**
     * @loadFixture orders.yaml
     * @test
     */
    public function updatePaymentAndPlaceFormActionWithSuccess()
    {
        $orderId = '#100000013';
        $params = array(
            'orderID' => $orderId,
            'payment' => array(
                'method'    => 'ops_iDeal',
                'ops_iDeal' => array('info' => 'foo', 'info2' => 'bar')
            )
        );

        Mage::app()->getRequest()->setParams($params);
        Mage::getConfig()->setNode('stores/default/payment/ops_iDeal/active', 1);
        $this->mockSession('checkout/session', array());

        $this->dispatch('ops/payment/updatePaymentAndPlaceForm');

        $this->assertRedirectToUrlContains('placeForm');

        /** @var Mage_Core_Model_Session $test */
        $test     = Mage::getSingleton('core/session');
        $messages = $test->getMessages()->count();
        $this->assertEquals(0, $messages);
    }

    /**
     * @loadFixture ../../../var/fixtures/orders.yaml
     * @test
     */
    public function updatePaymentAndPlaceFormActionWithSuccessForDirectDebit()
    {
        $this->markTestIncomplete("DirectDebit needs a general rework - INGRC-34");

        $orderId = 100000014;
        Mage::app()->getRequest()->setParam('orderID', $orderId);
        Mage::app()->getRequest()->setParam('payment', array('method' => 'ops_directDebit'));

        /** @var Mage_Sales_Model_Order $orderModelMock */
        $orderModelMock = $this->getModelMock('sales/order', array('save', 'place'));
        $orderModelMock->expects($this->any())
                       ->method('save')
                       ->will($this->returnSelf());
        $orderModelMock->expects($this->any())
                       ->method('place')
                       ->will($this->returnSelf());

        $orderHelperMock = $this->getHelperMock('ops/order', array('getOrder'));
        $orderHelperMock->expects($this->any())
                        ->method('getOrder')
                        ->will($this->returnValue($orderModelMock->loadByIncrementId($orderId)));
        $this->replaceByMock('helper', 'ops/order', $orderHelperMock);

        $quoteModelMock = $this->getModelMock('sales/quote', array('save'));
        $quoteModelMock->expects($this->any())
                       ->method('save')
                       ->will($this->returnSelf());
        $this->replaceByMock('model', 'sales/quote', $quoteModelMock);

        $quotePaymentModelMock = $this->getModelMock('sales/quote_payment', array('save'));
        $quotePaymentModelMock->expects($this->any())
                              ->method('save')
                              ->will($this->returnSelf());
        $this->replaceByMock('model', 'sales/quote_payment', $quotePaymentModelMock);

        $this->dispatch('ops/payment/updatePaymentAndPlaceForm');
        $this->assertRedirectToUrlContains('onepage/success');

        /** @var Mage_Core_Model_Session $test */
        $test     = Mage::getSingleton('core/session');
        $messages = $test->getMessages()->count();
        $this->assertEquals(0, $messages);
    }
}
