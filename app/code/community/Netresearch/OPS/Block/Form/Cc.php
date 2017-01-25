<?php

/**
 * Netresearch_OPS_Block_Form_OpsId
 *
 * @package   OPS
 * @copyright 2012 Netresearch App Factory AG <http://www.netresearch.de>
 * @author    Thomas Birke <thomas.birke@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Block_Form_Cc extends Netresearch_OPS_Block_Form
{

    protected $_aliasDataForCustomer = array();

    /**
     * CC Payment Template
     */
    const FRONTEND_TEMPLATE = 'ops/form/cc.phtml';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::FRONTEND_TEMPLATE);
    }


    /**
     * gets all Alias CC brands
     *
     * @return array
     */
    public function getAliasBrands()
    {
        return Mage::getModel('ops/source_cc_aliasInterfaceEnabledTypes')
            ->getAliasInterfaceCompatibleTypes();
    }

    /**
     * @return string
     */
    public function getSaveCcBrandUrl()
    {
        return Mage::getModel('ops/config')->getSaveCcBrandUrl();
    }

    /**
     * @param null $storeId
     * @param bool $admin
     *
     * @return mixed
     */
    public function getCcSaveAliasUrl($storeId = null, $admin = false)
    {
        return Mage::getModel('ops/config')->getCcSaveAliasUrl($storeId, $admin);
    }

    /**
     * checks if the 'alias' payment method (!) is available
     * no check for customer has aliases here
     * just a passthrough of the isAvailable of Netresearch_OPS_Model_Payment_Abstract::isAvailable
     *
     * @return boolean
     */
    public function isAliasPMEnabled()
    {
        return Mage::getModel('ops/config')->isAliasManagerEnabled($this->getMethodCode());
    }


    /**
     * retrieves the alias data for the logged in customer
     *
     * @return array | null - array the alias data or null if the customer
     * is not logged in
     */
    protected function getStoredAliasForCustomer()
    {
        if (Mage::helper('customer/data')->isLoggedIn()
            && Mage::getModel('ops/config')->isAliasManagerEnabled($this->getMethodCode())
        ) {
            $quote = $this->getQuote();
            $aliases = Mage::helper('ops/alias')->getAliasesForAddresses(
                $quote->getCustomer()->getId(), $quote->getBillingAddress(),
                $quote->getShippingAddress(), $quote->getStoreId()
            )
                ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
                ->addFieldToFilter('payment_method', $this->getMethodCode())
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC);


            foreach ($aliases as $key => $alias) {
                $this->_aliasDataForCustomer[$key] = $alias;
            }
        }

        return $this->_aliasDataForCustomer;
    }




    /**
     * @param $aliasId
     * @param $key
     * @return null|string
     */
    public function getExpirationDatePart($aliasId, $key)
    {
        $returnValue = null;
        $expirationDate = $this->getStoredAliasDataForCustomer($aliasId, 'expiration_date');
        // set expiration date to actual date if no stored Alias is used
        if ($expirationDate === null) {
            $expirationDate = date('my');
        }

        if (0 < strlen(trim($expirationDate))
        ) {
            $expirationDateValues = str_split($expirationDate, 2);

            if ($key == 'month') {
                $returnValue = $expirationDateValues[0];
            }
            if ($key == 'year') {
                $returnValue = $expirationDateValues[1];
            }

            if ($key == 'complete') {
                $returnValue = implode('/', $expirationDateValues);
            }
        }

        return $returnValue;

    }


    /**
     * the brand of the stored card data
     *
     * @param $aliasId
     *
     * @return null|string - string if stored card data were found, null otherwise
     */
    public function getStoredAliasBrand($aliasId)
    {
        return $this->getStoredAliasDataForCustomer($aliasId, 'brand');
    }

    /**
     * determines whether the alias hint is shown to guests or not
     *
     * @return bool true if alias feature is enabled and display the hint to
     * guests is enabled
     */
    public function isAliasInfoBlockEnabled()
    {
        return ($this->isAliasPMEnabled()
            && Mage::getModel('ops/config')->isAliasInfoBlockEnabled());
    }

    /**
     * @return string[]
     */
    public function getCcBrands()
    {
        return explode(',', $this->getConfig()->getAcceptedCcTypes($this->getMethodCode()));
    }

    public function checkIfBrandHasAliasInterfaceSupport($alias)
    {
        $brand         = $this->getStoredAliasBrand($alias);
        $allowedBrands = $this->getMethod()->getBrandsForAliasInterface();

        return in_array($brand, $allowedBrands);
    }

}
