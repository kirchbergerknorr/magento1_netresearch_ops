<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de> 
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */




class Netresearch_OPS_Helper_Validation_Result
{

    protected $checkoutStepHelper = null;

    protected $config = null;

    protected $formBlock = null;

    protected $dataHelper = null;

    protected $result = array();

    /**
     * @param array $result
     */
    public function setResult(array $result)
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param null $checkOutStepHelper
     */
    public function setCheckoutStepHelper($checkOutStepHelper)
    {
        $this->checkoutStepHelper = $checkOutStepHelper;
    }

    /**
     * @return null
     */
    public function getCheckoutStepHelper()
    {
        if (null === $this->checkoutStepHelper) {
            $this->checkoutStepHelper = Mage::helper('ops/validation_checkout_step');
        }

        return $this->checkoutStepHelper;
    }

    /**
     * @param null $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return null
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $this->config = Mage::getModel('ops/config');
        }

        return $this->config;
    }

    /**
     * @param null $dataHelper
     */
    public function setDataHelper($dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return null
     */
    public function getDataHelper()
    {
        if (null === $this->dataHelper) {
            $this->dataHelper = Mage::helper('ops/data');
        }

        return $this->dataHelper;
    }

    /**
     * @param null $formBlock
     */
    public function setFormBlock($formBlock)
    {
        $this->formBlock = $formBlock;
    }

    /**
     * @return Netresearch_OPS_Block_Form
     */
    public function getFormBlock()
    {
        if (null === $this->formBlock) {
            $this->formBlock = Mage::getBlockSingleton('ops/form');
        }

        return $this->formBlock;
    }



    /**
     * @param array $messages
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return array
     */
    public function getValidationFailedResult($messages, $quote)
    {
        $gotoSection            = $this->getCheckoutStepHelper()->getStep(array_keys($messages));
        $this->setBaseErroneousFields($messages, $gotoSection);
        $this->getFields($messages);
        $this->addErrorToExistingAddress($quote, $gotoSection);
        $this->cleanResult();

        return $this->getResult();
    }

    /**
     * @param $messages
     *
     * @return mixed
     */
    protected function getFields($messages)
    {
        $mappedFields   = $this->getConfig()->getFrontendFieldMapping();
        $frontendFields = array();
        foreach ($messages as $key => $value) {
            if (array_key_exists($key, $mappedFields)) {
                $frontendFields = $this->getFormBlock()->getFrontendValidationFields(
                    $mappedFields,
                    $key,
                    $value,
                    $frontendFields
                );
            }
        }
        $this->result['fields'] = $frontendFields;

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param string $gotoSection
     *
     * @return $this
     */
    protected function addErrorToExistingAddress($quote, $gotoSection)
    {
        if ($gotoSection == 'billing' && 0 < $quote->getBillingAddress()->getId()) {
            $this->result['fields']['billing-address-select'] = $this->getDataHelper()->__(
                'Billing address contains invalid data'
            );
        }
        if ($gotoSection == 'shipping' && 0 < $quote->getShippingAddress()->getId()) {
            $this->result['fields']['shipping-address-select'] = $this->getDataHelper()->__(
                'Shipping address contains invalid data'
            );
        }

        return $this;
    }

    /**
     * @param string[] $messages
     * @param string $gotoSection
     *
     * @return mixed
     */
    protected function setBaseErroneousFields($messages, $gotoSection)
    {
        $this->result['error']        = implode(',', array_values($messages));
        $this->result['goto_section'] = $gotoSection;
        $this->result['opsError']     = true;

        return $this;
    }

    protected function cleanResult()
    {
        if (array_key_exists('update_section', $this->result)) {
            unset($this->result['update_section']);
        }

        return $this;
    }

} 