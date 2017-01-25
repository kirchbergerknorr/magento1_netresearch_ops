<?php

/**
 * Template.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */
class Netresearch_OPS_Model_System_Config_Template extends Mage_Core_Model_Config_Data
{
    protected function getConfig()
    {
        return Mage::getModel('ops/config');

    }

    public function getCommentText(Mage_Core_Model_Config_Element $element, $currentValue)
    {
        $paypageUrl = $this->getConfig()->getPayPageTemplate();
        $paypageInfo = Mage::helper('ops')->__(
            'With this setting the customer will be redirected to the Ingenico ePayments paypage with the look and feel of your shop. ' .
            '</br> The template used can be seen here: </br>'
        );
        $paypageInfo .= "<a href=\"" . $paypageUrl . "\">" . $paypageUrl . "</a>";

        $result = "<p class=\"note\"><span id=\"ops_template_comment\"></span></p>";
        $result .= "
            <script type=\"text/javascript\">
                Translator.add(
                '" . Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_MAGENTO_INTERNAL . "',
                '" . $paypageInfo . "'
                );
                Translator.add(
                '" . Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_TEMPLATE . "',
                '" . Mage::helper('ops')->__('With this setting the customer will be redirected to the Ingenico ePayments paypage. The look and feel of that page will be defined by a dynamically loaded template file whose origin you can define below.') . "'
                );
                Translator.add(
                '" . Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_IFRAME . "',
                '" . Mage::helper('ops')->__('With this setting the customer will enter the payment details on a page in your shop that hosts the Ingenico ePayments paypage in an iFrame. You can style the paypage through the parameters below.') . "'
                );
                Translator.add(
                '" . Netresearch_OPS_Model_Payment_Abstract::TEMPLATE_OPS_REDIRECT . "',
                '" . Mage::helper('ops')->__('With this setting the customer will get redirected to Ingenico ePayments to enter his payment details. You can style the page through the parameters below.') . "'
                );
                selectElement = $('payment_services_ops_template');

                function updateComment(value){
                    var comment = $('ops_template_comment');
                    comment.innerHTML = Translator.translate(value);
                }

                Event.observe(window, 'load', function(){
                    updateComment('" . $currentValue . "');
                    Event.observe(selectElement, 'change', function(){
                        updateComment(selectElement.value);
                    });
                });
            </script>";

        return $result;
    }

}
