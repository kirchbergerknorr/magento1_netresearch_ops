<?php
/**
 * TemplateType.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */

class Netresearch_OPS_Model_Source_TemplateType
{

    const URL = 'url';
    const ID  = 'id';

    public function toOptionArray()
    {
        return array(
            array('value' => self::URL, 'label' => Mage::helper('ops')->__(self::URL)),
            array('value' => self::ID, 'label' => Mage::helper('ops')->__(self::ID))
        );
    }

}
