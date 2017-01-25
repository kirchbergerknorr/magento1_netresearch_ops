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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * OPS payment DirectLink Model
 */
class Netresearch_OPS_Model_Api_DirectLink extends Mage_Core_Model_Abstract
{
    
    const MAX_RETRY_COUNT = 3;
    
    /**
     * Perform a CURL call and log request end response to logfile
     *
     * @param array $params
     * @param string $url
     *
     * @return mixed
     */
     public function call($params, $url)
     {
         try {
             $http = new Varien_Http_Adapter_Curl();
             $config = array('timeout' => 30);
             $http->setConfig($config);
             $http->write(Zend_Http_Client::POST, $url, '1.1', array(), http_build_query($params));
             $response = $http->read();
             $response = substr($response, strpos($response, "<?xml"), strlen($response));
         } catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException(
                Mage::helper('ops')->__('Ingenico ePayments server is temporarily not available, please try again later.')
            );
         }

         return $response;
     }

    /**
     * Performs a POST request to the Direct Link Gateway with the given
     * parameters and returns the result parameters as array
     *
     * @param array $requestParams
     * @param string $url
     * @param int $storeId
     *
     * @return array
     */
     public function performRequest($requestParams, $url, $storeId = 0)
     {
        $helper = Mage::helper('ops');
        $params = $this->getEncodedParametersWithHash(
            array_merge(
                $requestParams,
                $this->buildAuthenticationParams($storeId)
            ),//Merge Logic Operation Data with Authentication Data
            null,
            $storeId
        );
        $responseParams = $this->getResponseParams($params, $url);
        $helper->log(
            $helper->__(
                "Direct Link Request/Response in Ingenico ePayments \n\nRequest: %s\nResponse: %s\nMagento-URL: %s\nAPI-URL: %s",
                serialize($params),
                serialize($responseParams),
                Mage::helper('core/url')->getCurrentUrl(),
                $url
            )
        );
        
        $this->checkResponse($responseParams);

        return $responseParams;

     }

     public function getEncodedParametersWithHash($params, $shaCode=null, $storeId)
     {
         $hash = Mage::helper('ops/payment')->getSHASign($params, $shaCode, $storeId);
         $params['SHASIGN'] = Mage::helper('ops/payment')->shaCrypt(iconv('iso-8859-1', 'utf-8', $hash));

        return $params;
     }

    
     /**
      * 
      * wraps the request and response handling and repeats request/response 
      * if there are errors
      * 
      * @param array $params - request params
      * @param string $url - the url for the request
      * @param int $retryCount - current request count
      * @return array | null - null if requests were not successful, array containing Ingenico ePayments payment data otherwise
      * 
      */
     protected function getResponseParams($params, $url, $retryCount = 0)
     {
         $responseParams = null;
         $responseXml = null;
         if ($retryCount < self::MAX_RETRY_COUNT) {
            try {
               $responseXml = $this->call($params, $url);
               $responseParams = $this->getParamArrFromXmlString($responseXml);
            } catch (Exception $e) {
                try {
                    $responseParams = $this->getParamArrFromXmlString(utf8_encode($responseXml));
                } catch (Exception $e) {
                    $ref = '';
                    if (array_key_exists('ORDERID', $params)) {
                        $ref = $params['ORDERID'];
                    } elseif (array_key_exists('PAYID', $params)) {
                        $ref = $params['PAYID'];
                    }
                    Mage::helper('ops')->log(
                        'DirectLink::getResponseParams failed: ' .
                        $e->getMessage() . ' current retry count: ' . $retryCount . ' for quote ' . $ref
                    );
                    $responseParams = $this->getResponseParams($params, $url, ++$retryCount);
                }
            }
         } else {
             Mage::throwException(Mage::helper('ops')->__('An error occured during the Ingenico ePayments request. Your action could not be executed.'));
         }
         return $responseParams;
     }
     
    /**
     * Return Authentication Params for OPS Call
     *
     * @param int $storeId
     *
     * @return array
     */
     protected function buildAuthenticationParams($storeId = 0)
     {
         return array(
             'PSPID' => Mage::getModel('ops/config')->getPSPID($storeId),
             'USERID' => Mage::getModel('ops/config')->getApiUserId($storeId),
             'PSWD' => Mage::getModel('ops/config')->getApiPswd($storeId),
         );
     }

    /**
     * Parses the XML-String to an array with the result data
     *
     * @param $xmlString
     * @return mixed
     * @throws Exception
     */
     public function getParamArrFromXmlString($xmlString)
     {
         try {
             $xml = new SimpleXMLElement($xmlString);
             foreach ($xml->attributes() as $key => $value) {
                 $arrAttr[$key] = (string)$value;
             }
             foreach ($xml->children() as $child) {
                 $arrAttr[$child->getName()] = (string) $child;
             }
             return $arrAttr;
         } catch (Exception $e) {
             Mage::log('Could not convert string to xml in ' . __FILE__ . '::' . __METHOD__ . ': ' . $xmlString);
             Mage::logException($e);
             throw $e;
         }
     }

    /**
     * Check if the Response from OPS reports Errors
     *
     * @param $responseParams
     */
     public function checkResponse($responseParams)
     {
         if (false === is_array($responseParams)
             || false === array_key_exists('NCERROR', $responseParams)
             || $responseParams['NCERROR'] > 0
         )
         {
            if (empty($responseParams['NCERRORPLUS'])) {
                $responseParams['NCERRORPLUS'] = Mage::helper('ops')->__('Invalid payment information')." Errorcode:".$responseParams['NCERROR'];
            }
            
            //avoid exception if STATUS is set with special values
            if (isset($responseParams['STATUS']) && is_numeric($responseParams['STATUS'])) {
                return;
            }
            
            Mage::throwException(
                Mage::helper('ops')->__("An error occured during the Ingenico ePayments request. Your action could not be executed. Message: '%s.'", $responseParams['NCERRORPLUS'])
            );
         }
     }
}
