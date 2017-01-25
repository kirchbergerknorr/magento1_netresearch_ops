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
 * @category    Mage
 * @package     Netresearch_OPS
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc.
 *              DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Model_Payment_Kwixo_Abstract
    extends Netresearch_OPS_Model_Payment_Abstract
{

    protected $kwixoShippingModel = null;

    protected $shippingSettings = null;

    /**
     * @param Mage_Sales_Model_Order $order
     * @param null $requestParams
     * @return string[]
     */
    public function getMethodDependendFormFields($order, $requestParams = null)
    {
        $formFields = parent::getMethodDependendFormFields(
            $order, $requestParams
        );
        unset($formFields['OWNERADDRESS']);
        unset($formFields['OWNERTELNO']);
        unset($formFields['ECOM_SHIPTO_POSTAL_STREET_LINE1']);
        $shippingMethod = 'none';
        $isVirtual      = true;
        if ($order->getShippingAddress()) {
            $isVirtual   = false;
            $carrierCode = $order->getShippingCarrier()->getCarrierCode();
            $this->loadShippingSettingForCarrierCode($carrierCode);
            $shippingMethod = $carrierCode;
        }

        $formFields['ECOM_ESTIMATEDELIVERYDATE']
                                            = $this->getEstimatedDeliveryDate(
                                                $this->getCode(), $order->getStoreId()
                                            );
        $formFields['RNPOFFERT']            = $this->getRnpFee(
            $this->getCode(), $order->getStoreId()
        );
        $formFields['ECOM_SHIPMETHODTYPE']  = $this->getShippingMethodType(
            $this->getCode(), $order->getStoreId(), $isVirtual
        );
        $formFields['ECOM_SHIPMETHODSPEED'] = $this->getShippingMethodSpeed(
            $this->getCode(), $order->getStoreId()
        );
        $shipMethodDetails                  = $this->getShippingMethodDetails(
            $this->getCode(), $order->getStoreId()
        );
        if (0 < strlen(trim($shipMethodDetails))) {
            $formFields['ECOM_SHIPMETHODDETAILS'] = $shipMethodDetails;
        }
        if (4 == $formFields['ECOM_SHIPMETHODTYPE']
            && !array_key_exists(
                'ECOM_SHIPMETHODDETAILS', $formFields
            )
        ) {
            $address                              = $order->getShippingAddress()
                ? $order->getShippingAddress()->toString()
                : $order->getBillingAddress()->toString();
            $formFields['ECOM_SHIPMETHODDETAILS'] = substr($address, 0, 50);
        }

        $formFields['ORDERSHIPMETH'] = $shippingMethod;

        $formFields['CIVILITY']
                    = $this->getGender($order) == 'Male' ? 'Mr' : 'Mrs';
        $formFields = array_merge(
            $formFields, $this->getKwixoBillToParams($order)
        );
        $formFields = array_merge(
            $formFields, $this->getKwixoShipToParams($order)
        );
        $formFields = array_merge(
            $formFields, $this->getItemParams($order)
        );

        $formFields['ORDERID'] = Mage::helper('ops/order')->getOpsOrderId(
            $order, false
        );
        $formFields            = $this->populateFromArray(
            $formFields, $requestParams, $order
        );

        return $formFields;
    }


    protected function getKwixoCategoryFromOrderItem(
        Mage_Sales_Model_Order_Item $item
    )
    {
        $product         = Mage::getModel('catalog/product')->load(
            $item->getProductId()
        );
        $kwixoCategoryId = null;
        foreach ($product->getCategoryIds() as $categoryId) {
            $kwixoCategory = Mage::getModel('ops/kwixo_category_mapping')
                ->loadByCategoryId($categoryId);
            if (null != $kwixoCategory->getId()) {
                $kwixoCategoryId = $kwixoCategory->getKwixoCategoryId();
                break;
            }
        }

        return $kwixoCategoryId;
    }

    /**
     *
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function getKwixoBillToParams(Mage_Sales_Model_Order $order)
    {
        $formFields    = array();
        $billingAddress = $order->getBillingAddress();

        $billingStreet         = str_replace("\n", ' ', $billingAddress->getStreet(-1));
        $splittedBillingStreet = Mage::Helper('ops/address')->splitStreet($billingStreet);

        $formFields['ECOM_BILLTO_POSTAL_NAME_FIRST']    = $billingAddress->getFirstname();
        $formFields['ECOM_BILLTO_POSTAL_NAME_LAST']     = $billingAddress->getLastname();
        $formFields['OWNERADDRESS']                     = $splittedBillingStreet['street_name'];
        $formFields['OWNERADDRESS2']                    = $splittedBillingStreet['supplement'];
        $formFields['ECOM_BILLTO_POSTAL_STREET_NUMBER'] = $splittedBillingStreet['street_number'];
        $formFields['OWNERTELNO']                       = $billingAddress->getTelephone();

        return $formFields;
    }

    /**
     * return the shipping parameters as array based on shipping method type
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function getKwixoShipToParams(Mage_Sales_Model_Order $order)
    {
        $formFields      = array();
        $shippingAddress = $order->getShippingAddress();

        if ($shippingAddress === false) {
            $shippingAddress = $order->getBillingAddress();
        }

        $shippingStreet         = str_replace("\n", ' ', $shippingAddress->getStreet(-1));
        $splittedShippingStreet = Mage::Helper('ops/address')->splitStreet($shippingStreet);
        $shippingMethodType     = (int)$this->getShippingMethodType($this->getCode(), $order->getStoreId());

        if (in_array($shippingMethodType, $this->getShippingMethodTypeValues())) {
            if (4 === $shippingMethodType) {
                $formFields['ECOM_SHIPTO_POSTAL_NAME_PREFIX'] = $shippingAddress->getPrefix();
            }

            $company = trim($shippingAddress->getCompany());
            if (0 < strlen($company)) {
                $formFields['ECOM_SHIPTO_COMPANY'] = $company;
            }

            $fax = trim($shippingAddress->getFax());
            if (0 < strlen($fax)) {
                $formFields['ECOM_SHIPTO_TELECOM_FAX_NUMBER'] = $fax;
            }

            $formFields['ECOM_SHIPTO_POSTAL_STREET_LINE1']  = $shippingAddress->getStreet1();
            $formFields['ECOM_SHIPTO_POSTAL_STREET_LINE1']  = $splittedShippingStreet['street_name'];
            $formFields['ECOM_SHIPTO_POSTAL_STREET_LINE2']  = $splittedShippingStreet['supplement'];
            $formFields['ECOM_SHIPTO_POSTAL_STREET_NUMBER'] = $splittedShippingStreet['street_number'];
            $formFields['ECOM_SHIPTO_POSTAL_POSTALCODE']    = $shippingAddress->getPostcode();
            $formFields['ECOM_SHIPTO_POSTAL_CITY']          = $shippingAddress->getCity();
            $formFields['ECOM_SHIPTO_POSTAL_COUNTRYCODE']   = $shippingAddress->getCountryId();
        }

        $formFields['ECOM_SHIPTO_POSTAL_NAME_FIRST']    = $shippingAddress->getFirstname();
        $formFields['ECOM_SHIPTO_POSTAL_NAME_LAST']     = $shippingAddress->getLastname();
        $formFields['ECOM_SHIPTO_TELECOM_PHONE_NUMBER'] = $shippingAddress->getTelephone();

        return $formFields;
    }

    /**
     * return item params for the order
     * for each item a ascending number will be added to the parameter name
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return array
     */
    public function getItemParams(Mage_Sales_Model_Order $order)
    {
        $formFields = array();
        $items      = $order->getAllItems();
        $subtotal   = 0;
        if (is_array($items)) {
            $itemCounter = 1;
            foreach ($items as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }

                $subtotal += $item->getBasePriceInclTax(
                ) * $item->getQtyOrdered();
                $formFields['ITEMFDMPRODUCTCATEG' . $itemCounter]
                                                        = $this->getKwixoCategoryFromOrderItem(
                                                            $item
                                                        );
                $formFields['ITEMID' . $itemCounter]    = $item->getItemId();
                $formFields['ITEMNAME' . $itemCounter]  = substr(
                    $item->getName(), 0, 40
                );
                $formFields['ITEMPRICE' . $itemCounter] = number_format(
                    $item->getBasePriceInclTax(), 2, '.', ''
                );
                $formFields['ITEMQUANT' . $itemCounter]
                                                          = (int)$item->getQtyOrdered(
                                                          );
                $formFields['ITEMVAT' . $itemCounter]     = str_replace(
                    ',', '.', (string)(float)$item->getBaseTaxAmount()
                );
                $formFields['TAXINCLUDED' . $itemCounter] = 1;
                $itemCounter++;
            }
            $shippingPrice        = $order->getBaseShippingAmount();
            $shippingPriceInclTax = $order->getBaseShippingInclTax();
            $subtotal += $shippingPriceInclTax;
            $shippingTaxAmount = $shippingPriceInclTax - $shippingPrice;

            $roundingError = $order->getBaseGrandTotal() - $subtotal;
            $shippingPrice += $roundingError;
            /* add shipping item */
            $formFields['ITEMFDMPRODUCTCATEG' . $itemCounter] = 1;
            $formFields['ITEMID' . $itemCounter]              = 'SHIPPING';
            $shippingDescription
                                                              =
                0 < strlen(trim($order->getShippingDescription()))
                    ? $order->getShippingDescription() : 'shipping';
            $formFields['ITEMNAME' . $itemCounter]            = substr(
                $shippingDescription, 0, 30
            );
            $formFields['ITEMPRICE' . $itemCounter]           = number_format(
                $shippingPrice, 2, '.', ''
            );
            $formFields['ITEMQUANT' . $itemCounter]           = 1;
            $formFields['ITEMVAT' . $itemCounter]             = number_format(
                $shippingTaxAmount, 2, '.', ''
            );
        }

        return $formFields;
    }


    /**
     * returns the delivery date as date based on actual date and adding
     * the configurated value as days to it
     *
     * @param string $code
     * @param string $storeId
     *
     * @return bool|string
     */
    public function getEstimatedDeliveryDate($code, $storeId = null)
    {
        $dateNow      = date("Y-m-d");
        $dayValue     = (string)Mage::getStoreConfig(
            'payment/' . $code . "/delivery_date", $storeId
        );
        $deliveryDate = strtotime($dateNow . "+" . $dayValue . "days");

        return date("Y-m-d", $deliveryDate);
    }

    /**
     * return the RNP Fee value
     *
     * @param string $code
     * @param int    $storeId
     *
     * @return boolean
     */
    public function getRnpFee($code, $storeId = null)
    {
        return (int)(bool)Mage::getStoreConfig(
            "payment/" . $code . "/rnp_fee", $storeId
        );
    }

    /**
     * returns the Shipping Method Type configured in backend
     *
     * @param $code
     * @param null $storeId
     * @param bool $isVirtual
     * @return int
     */
    public function getShippingMethodType(
        $code, $storeId = null, $isVirtual = false
    )
    {
        // use download type for orders containing virtual products only
        if ($isVirtual) {
            return Netresearch_OPS_Model_Source_Kwixo_ShipMethodType::DOWNLOAD;
        }
        $shippingMethodType = $this->getKwixoShippingModel()
            ->getKwixoShippingType();
        if (null === $shippingMethodType) {
            $shippingMethodType = Mage::getStoreConfig(
                "payment/" . $code . "/ecom_shipMethodType", $storeId
            );
        }

        return $shippingMethodType;
    }

    /**
     * return the shipping method speed configured in backend
     *
     * @param string $code
     * @param int    $storeId
     *
     * @return int
     */
    public function getShippingMethodSpeed($code, $storeId = null)
    {
        $shippingMethodSpeed = $this->getKwixoShippingModel()
            ->getKwixoShippingMethodSpeed();
        if (null === $shippingMethodSpeed) {
            $shippingMethodSpeed = Mage::getStoreConfig(
                "payment/" . $code . "/ecom_shipMethodSpeed", $storeId
            );
        }

        return (int)$shippingMethodSpeed;
    }

    /**
     * return the item product categories configured in backend as array
     *
     * @param string $code
     * @param int    $storeId
     *
     * @return array
     */
    public function getItemFmdProductCateg($code, $storeId = null)
    {
        return explode(
            ",", Mage::getStoreConfig(
                "payment/" . $code . "/product_categories", $storeId
            )
        );
    }

    /**
     * return the shipping method detail text
     *
     * @param string $code
     * @param int    $storeId
     *
     * @return string
     */
    public function getShippingMethodDetails($code, $storeId = null)
    {
        $shippingMethodDetails = $this->getKwixoShippingModel()
            ->getKwixoShippingDetails();
        if (null === $shippingMethodDetails) {
            $shippingMethodDetails = Mage::getStoreConfig(
                "payment/" . $code . "/shiping_method_details", $storeId
            );
        }

        return $shippingMethodDetails;
    }

    /**
     * get question for fields with disputable value
     * users are asked to correct the values before redirect to Ingenico ePayments
     *
     *
     * @return string
     */
    public function getQuestion()
    {
        return Mage::helper('ops/data')->__(
            'Please make sure that the displayed data is correct.'
        );
    }

    /**
     * get an array of fields with disputable value
     * users are asked to correct the values before redirect to Ingenico ePayments
     *
     * @param Mage_Sales_Model_Order $order         Current order
     *
     * @return array
     */
    public function getQuestionedFormFields($order)
    {

        $questionedFormFields = array(
            'CIVILITY',
            'OWNERADDRESS',
            'ECOM_BILLTO_POSTAL_STREET_NUMBER',

        );
        $storeId              = null;
        if ($order instanceof Mage_Sales_Model_Order) {
            $storeId = $order->getStoreId();
        }
        $shippingMethodType = (int)$this->getShippingMethodType(
            $this->getCode(), $storeId
        );
        if (in_array($shippingMethodType, $this->getShippingMethodTypeValues())) {
            $questionedFormFields [] = 'ECOM_SHIPTO_POSTAL_STREET_NUMBER';
            $questionedFormFields [] = 'ECOM_SHIPTO_POSTAL_STREET_LINE1';
        }

        if ($shippingMethodType === 4) {
            $questionedFormFields [] = 'ECOM_SHIPTO_TELECOM_PHONE_NUMBER';
            $questionedFormFields [] = 'ECOM_SHIPTO_POSTAL_NAME_PREFIX';
        }

        return $questionedFormFields;
    }

    /**
     * return shipping method values except for the type download
     *
     * @return array
     */
    public function getShippingMethodTypeValues()
    {
        return array(1, 2, 3, 4);
    }

    /**
     * populates an array with the values from another if the keys are matching
     *
     * @param array $formFields - the array to populate
     * @param null  $dataArray  - the array containing the data
     * @param Mage_Sales_Model_Order  $order
     *
     * @return array - the populated array
     */
    protected function populateFromArray(
        array $formFields, $dataArray = null, $order
    )
    {
        // copy some already known values, but only the ones from the questioned
        // form fields
        if (is_array($dataArray)) {
            foreach ($dataArray as $key => $value) {
                if (array_key_exists($key, $formFields)
                    && in_array(
                        $key,
                        $this->getQuestionedFormFields($order), true
                    )
                    || $key == 'CIVILITY'
                ) {
                    $formFields[$key] = $value;
                }
            }
        }

        return $formFields;
    }

    /**
     * get gender text for customer
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function getGender(Mage_Sales_Model_Order $order)
    {
        $gender = Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'gender')
            ->getSource()
            ->getOptionText($order->getCustomerGender());

        return $gender;
    }

    /**
     * sets the kwixo shipping setting model
     *
     * @param Netresearch_OPS_Model_Kwixo_Shipping_Setting $kwixoShippingModel
     */
    public function setKwixoShippingModel(
        Netresearch_OPS_Model_Kwixo_Shipping_Setting $kwixoShippingModel
    )
    {
        $this->kwixoShippingModel = $kwixoShippingModel;
    }

    /**
     * returns the kwixo shipping setting model
     *
     * @return Netresearch_OPS_Model_Kwixo_Shipping_Setting
     */
    public function getKwixoShippingModel()
    {
        if (null === $this->kwixoShippingModel) {
            $this->kwixoShippingModel = Mage::getModel(
                'ops/kwixo_shipping_setting'
            );
        }

        return $this->kwixoShippingModel;
    }

    /**
     * @param $carrierCode
     * @return null
     */
    protected function loadShippingSettingForCarrierCode($carrierCode)
    {
        $this->shippingSettings = $this->getKwixoShippingModel()->load(
            $carrierCode, 'shipping_code'
        );

        return $this->shippingSettings;
    }
}
