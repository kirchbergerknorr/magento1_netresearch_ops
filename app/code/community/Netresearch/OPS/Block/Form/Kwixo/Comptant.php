<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */



class Netresearch_OPS_Block_Form_Kwixo_Comptant extends Netresearch_OPS_Block_Form
{
    const FRONTEND_TEMPLATE = 'ops/form/kwixo/comptant.phtml';

    protected $pmLogo = 'images/ops/kwixo/comptant.jpg';

    /**
     * Init OPS payment form
     *
     */
    protected function _construct()
    {
        parent::_construct();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->setTemplate(self::FRONTEND_TEMPLATE);
    }


} 