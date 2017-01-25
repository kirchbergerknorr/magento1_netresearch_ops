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
 * Netresearch_OPS_Block_Checkout_DeviceFingerprinting
 *
 * @category Netresearch
 * @package  Netresearch_OPS
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Netresearch_OPS_Block_Checkout_DeviceFingerprinting
    extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $_template = 'ops/checkout/deviceFingerprinting.phtml';

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if (Mage::getModel('ops/config')->getDeviceFingerPrinting()) {
            /** @var Mage_Page_Block_Html_Head $headBlock */
            $headBlock = $this->getLayout()->getBlock('head');
            if ($headBlock) {
                $headBlock->addJs('netresearch/ops/deviceFingerprinting.js');
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::getModel('ops/config')->getDeviceFingerPrinting()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getConsentUrl()
    {
        return $this->getUrl('ops/device');
    }
}
