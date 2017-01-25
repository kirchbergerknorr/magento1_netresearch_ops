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
 * DirectEbanking.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 */
?>
<?php

class Netresearch_OPS_Block_Form_DirectEbanking extends Netresearch_OPS_Block_Form
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ops/form/directEbanking.phtml');
    }
}