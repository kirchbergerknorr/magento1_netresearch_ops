<?php
/**
 * Update the brand value for Sofort Uberweisung to DirectEbanking if it is present
 */
$stores = Mage::app()->getStores();
foreach ($stores as $store) {
    $activatedBrands = Mage::getStoreConfig('payment/ops_directEbanking/brands', $store->getId());
    if (0 < strlen(trim($activatedBrands))) {
        $activatedBrands = str_replace('Sofort Uberweisung', 'DirectEbanking', $activatedBrands);
        Mage::getConfig()->saveConfig('payment/ops_directEbanking/brands', $activatedBrands, 'stores', $store->getId());
    }
}