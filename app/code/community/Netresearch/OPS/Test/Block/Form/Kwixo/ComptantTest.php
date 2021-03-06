<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Test_Block_Form_Kwixo_ComptantTest extends EcomDev_PHPUnit_Test_Case
{
    protected $block = null;

    public function setUp()
    {
        $this->block = new Netresearch_OPS_Block_Form_Kwixo_Comptant();
    }

    public function testGetTemplate()
    {
        $this->assertEquals('ops/form/kwixo/comptant.phtml', $this->block->getTemplate());
    }

    public function testGetPmLogo()
    {
        $this->assertEquals('images/ops/kwixo/comptant.jpg', $this->block->getPmLogo());
    }

} 