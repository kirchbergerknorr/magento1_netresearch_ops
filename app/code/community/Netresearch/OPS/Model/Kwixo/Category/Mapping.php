<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    OPS
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Alias model
 */

class Netresearch_OPS_Model_Kwixo_Category_Mapping
    extends Mage_Core_Model_Abstract
{

    /**
     * Constructor
     *
     * @see lib/Varien/Varien_Object#_construct()
     */
    public function _construct()
    {
        $this->_init('ops/kwixo_category_mapping');
        parent::_construct();
    }


    public function loadByCategoryId($categoryId)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('category_id', $categoryId)
            ->load()
            ->getFirstItem();

        return $collection;
    }

}