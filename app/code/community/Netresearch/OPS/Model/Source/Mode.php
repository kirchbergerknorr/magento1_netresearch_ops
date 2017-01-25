<?php
/**
 * Mode.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */


class Netresearch_OPS_Model_Source_Mode
{

    const PROD = 'prod';
    const TEST = 'test';
    const CUSTOM = 'custom';

    /**
     *
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::TEST, 'label' => Mage::helper('ops')->__(self::TEST)),
            array('value' => self::PROD, 'label' => Mage::helper('ops')->__(self::PROD)),
            array('value' => self::CUSTOM, 'label' => Mage::helper('ops')->__(self::CUSTOM)),
        );
    }

}
