<?php
/**
 * @category   OPS
 * @package    Netresearch_OPS
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_OPS_Block_Adminhtml_Customer_Edit_Tab_Alias
 *
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Block_Adminhtml_Customer_Edit_Tab_Alias
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('customer_edit_tab_ops_alias');
        $this->setUseAjax(true);
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Payment Information');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Payment Information');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'orders';
    }


    protected function _prepareCollection()
    {
        $customer = Mage::registry('current_customer');

        $collection = Mage::getModel('ops/alias')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'alias', array(
            'header'        => Mage::helper('ops')->__('Alias'),
            'align'         => 'right',
            'index'         => 'alias',
            )
        );

        $this->addColumn(
            'payment_method', array(
            'header'    => Mage::helper('ops')->__('Payment method'),
            'index'     => 'payment_method',
            'renderer'  => 'Netresearch_OPS_Block_Adminhtml_Customer_Renderer_PaymentMethod'
            )
        );

        $this->addColumn(
            'brand', array(
            'header'    => Mage::helper('ops')->__('Credit Card Type'),
            'index'     => 'brand',
            )
        );

        $this->addColumn(
            'pseudo_account_or_cc_no', array(
            'header'    => Mage::helper('ops')->__('Card Number/Account Number'),
            'index'     => 'pseudo_account_or_cc_no',
            )
        );

        $this->addColumn(
            'expiration_date', array(
            'header'    => Mage::helper('ops')->__('Expiration Date'),
            'index'     => 'expiration_date',
            )
        );
        
        $this->addColumn(
            'card_holder', array(
            'header'    => Mage::helper('ops')->__('Card Holder'),
            'index'     => 'card_holder',
            )
        );
        
        $this->addColumn(
            'state', array(
            'header'    => Mage::helper('ops')->__('State'),
            'index'     => 'state',
            'renderer'  => 'Netresearch_OPS_Block_Adminhtml_Customer_Renderer_State',
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'    =>  Mage::helper('ops')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('ops')->__('Delete'),
                        'url'       => array('base' => 'adminhtml/alias/delete'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

}
