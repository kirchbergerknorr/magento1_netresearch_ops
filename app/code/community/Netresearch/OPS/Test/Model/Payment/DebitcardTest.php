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
 * DebitcardTest.php
 *
 * @category test
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php


class Netresearch_OPS_Test_Model_Payment_DebitcardTest extends EcomDev_PHPUnit_Test_Case
{
    /** @var Netresearch_OPS_Model_Payment_Debitcard model */
    protected $model;

    protected function setUp()
    {
        parent::setUp();
        $this->model = Mage::getModel('ops/payment_debitcard');
    }

    public function testGetOpsCode()
    {
        $this->assertEquals('CreditCard', $this->model->getOpsCode());
    }

    public function testGetRequestParamsHelper()
    {
        $this->assertTrue($this->model->getRequestParamsHelper() instanceof Netresearch_OPS_Helper_Debitcard);
    }
}
