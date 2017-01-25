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
 * PaymentHelper.php
 *
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

trait Netresearch_OPS_Trait_PaymentHelper
{
    protected $paymentHelper = null;

    /**
     * @return Netresearch_OPS_Helper_Payment
     */
    public function getPaymentHelper()
    {
        if (null === $this->paymentHelper) {
            $this->paymentHelper = Mage::helper('ops/payment');
        }

        return $this->paymentHelper;
    }

    /**
     * @param Netresearch_OPS_Helper_Payment $paymentHelper
     *
     * @returns $this
     */
    public function setPaymentHelper($paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;

        return $this;
    }


}