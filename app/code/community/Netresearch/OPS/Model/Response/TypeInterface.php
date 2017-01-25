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
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * TypeInterface.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

/**
 * Interface Netresearch_OPS_Model_Response_TypeInterface
 */
interface Netresearch_OPS_Model_Response_TypeInterface
{
    /**
     * Performs the necessary actions for Magento to progress the order state correctly and automatically build the
     * create sales objects
     *
     * @param array                                  $responseArray
     * @param Netresearch_OPS_Model_Payment_Abstract $paymentMethod
     * @param bool                                   $shouldRegisterFeedback determines if the Mage_Sales_Model_Order_Payments register*Feedback
     *                                                                       functions get called, defaults to true
     *
     * @return Netresearch_OPS_Model_Response_TypeInterface
     */
    public function handleResponse($responseArray, Netresearch_OPS_Model_Payment_Abstract $paymentMethod,
        $shouldRegisterFeedback
    );
}