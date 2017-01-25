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
 * Netresearch_OPS_Block_RetryPayment_Methods
 *
 * @category OPS
 * @package  Netresearch_OPS
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 */
class Netresearch_OPS_Block_RetryPayment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    protected $quote = null;

    /**
     * Get Order ID from Url
     *
     * @return null
     * @throws Exception
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            $opsOrderId  = $this->getRequest()->getParam('orderID');
            $order = Mage::helper('ops/order')-> getOrder($opsOrderId);
            $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());

            if ($quote->getId()) {
                $quote->setIsActive(1)
                    ->save();
                Mage::getSingleton('checkout/session')
                    ->replaceQuote($quote);
            }

            $this->quote = $quote;
        }

        return $this->quote;
    }

    /**
     * Retrieve available payment methods - filtered by OPS methods
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $methods = parent::getMethods();
            foreach ($methods as $key => $method) {
                if (!$method instanceof Netresearch_OPS_Model_Payment_Abstract) {
                    $this->_assignMethod($method);
                    unset($methods[$key]);
                }
            }
            $this->setData('methods', $methods);
        }

        return $methods;
    }
}
