<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
    DROP TABLE IF EXISTS {$this->getTable('ops_alias')};
    CREATE TABLE {$this->getTable('ops_alias')} (
        `id` int(11) unsigned NOT NULL auto_increment,
        `customer_id` int(10),
        `alias` varchar(32), 
        `brand` varchar(50),
        `billing_address_hash` varchar(255),
        `shipping_address_hash` varchar(255),
        `pseudo_account_or_cc_no` varchar(255), 
        `expiration_date` varchar(10),
        `payment_method` varchar(50),
        `created_at` timestamp default CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

$installer->endSetup();