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
 * Netresearch_OPS_Block_Form_OpenInvoice
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class Netresearch_OPS_Block_Form_OpenInvoice extends Netresearch_OPS_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ops/form/openInvoice.phtml');
    }

    /**
     * @return string
     * @see Netresearch_OPS_Model_Payment_OpenInvoice_Abstract::getInvoiceTermsTitle
     */
    public function getInvoiceTermsTitle()
    {
        return $this->getMethod()->getInvoiceTermsTitle();
    }

    /**
     * @return string
     * @see Netresearch_OPS_Model_Payment_OpenInvoice_Abstract::getInvoiceTermsUrl
     */
    public function getInvoiceTermsUrl()
    {
        return $this->getMethod()->getInvoiceTermsUrl();
    }

    /**
     * @return bool
     * @see Netresearch_OPS_Model_Payment_OpenInvoice_Abstract::showInvoiceTermsLink
     */
    public function showInvoiceTermsLink()
    {
        return $this->getMethod()->showInvoiceTermsLink();
    }
}
