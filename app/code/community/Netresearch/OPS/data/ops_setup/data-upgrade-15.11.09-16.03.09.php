<?php
/**
 * data-upgrade-14.12.03-15.02.25.php
 * @author  paul.siedler@netresearch.de
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License
 */

$aliasUrlPath = 'payment_services/ops/ops_alias_gateway';

$oldAliasUrl = Mage::getStoreConfig($aliasUrlPath, 0);
$newAliasUrl = str_replace('ncol/prod/alias_gateway_utf8.asp', 'Tokenization/HostedPage', $oldAliasUrl);
Mage::getConfig()->saveConfig($aliasUrlPath, $newAliasUrl);

$websites = Mage::app()->getWebsites();

/** @var Mage_Core_Model_Website $website */
foreach($websites as $website){
    $oldWebsiteAliasUrl = $website->getConfig($aliasUrlPath);
    if(strlen($oldWebsiteAliasUrl) > 0 && $oldWebsiteAliasUrl != $oldAliasUrl){

        $newWebsiteAliasUrl = str_replace('ncol/prod/alias_gateway_utf8.asp', 'Tokenization/HostedPage', $oldWebsiteAliasUrl);
        Mage::getConfig()->saveConfig($aliasUrlPath, $newWebsiteAliasUrl, 'websites', $website->getId());
    }
    /** @var Mage_Core_Model_Store $store */
    foreach($website->getStores() as $store){
        $oldStoreAliasUrl = Mage::getStoreConfig($aliasUrlPath, $store->getId());
        if(strlen($oldStoreAliasUrl) > 0 && $oldStoreAliasUrl != $oldAliasUrl) {
            $newStoreAliasUrl = str_replace('ncol/prod/alias_gateway_utf8.asp', 'Tokenization/HostedPage', $oldStoreAliasUrl);
            Mage::getConfig()->saveConfig($aliasUrlPath, $newStoreAliasUrl, 'stores', $store->getId());
        }
    }
}

Mage::getConfig()->cleanCache();


