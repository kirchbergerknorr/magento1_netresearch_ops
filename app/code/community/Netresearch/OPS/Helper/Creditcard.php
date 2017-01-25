<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @package     ${MODULENAME}
 * @copyright   Copyright (c) 2013 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Netresearch_OPS_Helper_Creditcard extends Netresearch_OPS_Helper_Payment_DirectLink_Request
{

    protected $aliasHelper = null;

    /**
     * @param Netresearch_OPS_Helper_Alias $aliasHelper
     */
    public function setAliasHelper($aliasHelper)
    {
        $this->aliasHelper = $aliasHelper;
    }

    /**
     * @return Netresearch_OPS_Helper_Alias
     */
    public function getAliasHelper()
    {
        if (null === $this->aliasHelper) {
            $this->aliasHelper = Mage::helper('ops/alias');
        }
        return $this->aliasHelper;
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param array $requestParams
     * @return $this
     */
    public function handleAdminPayment(Mage_Sales_Model_Quote $quote, $requestParams)
    {
       return $this;
    }

    protected function getPaymentSpecificParams(Mage_Sales_Model_Quote $quote)
    {
        $alias = $quote->getPayment()->getAdditionalInformation('alias');
        if (null === $alias && $this->getDataHelper()->isAdminSession()) {
            $alias = $this->getAliasHelper()->getAlias($quote);
        }
        $saveAlias = Mage::getModel('ops/alias')->load($alias, 'alias')->getId();
        $params = array (
            'ALIAS' => $alias,
            'ALIASPERSISTEDAFTERUSE' => $saveAlias ? 'Y' : 'N',
        );
        if ($this->getConfig()->getCreditDebitSplit($quote->getStoreId())) {
            $params['CREDITDEBIT'] = 'C';
        }
        if (is_numeric($quote->getPayment()->getAdditionalInformation('cvc'))) {
            $params['CVC'] = $quote->getPayment()->getAdditionalInformation('cvc');
        }
        $requestParams3ds = array();
        if ($this->getConfig()->get3dSecureIsActive() && false == $this->getDataHelper()->isAdminSession()) {
            $requestParams3ds = array(
                'FLAG3D'           => 'Y',
                'WIN3DS'           => Netresearch_OPS_Model_Payment_Abstract::OPS_DIRECTLINK_WIN3DS,
                'LANGUAGE'         => Mage::app()->getLocale()->getLocaleCode(),
                'HTTP_ACCEPT'      => '*/*',
                'HTTP_USER_AGENT'  => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)',
                'ACCEPTURL'        => $this->getConfig()->getAcceptUrl(),
                'DECLINEURL'       => $this->getConfig()->getDeclineUrl(),
                'EXCEPTIONURL'     => $this->getConfig()->getExceptionUrl(),
            );
        }
        $params = array_merge($params, $requestParams3ds);
        return $params;
    }
} 