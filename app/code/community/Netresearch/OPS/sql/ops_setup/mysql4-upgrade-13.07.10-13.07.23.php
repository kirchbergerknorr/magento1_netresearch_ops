<?php
$installer = $this;
$installer->startSetup();
$installer->run(
    "
    CREATE TABLE IF NOT EXISTS {$this->getTable('ops/kwixo_category_mapping')} (
        `id` int(11) unsigned NOT NULL auto_increment,
        `kwixo_category_id` int(10),
        `category_id` int(10),
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    "
);
$installer->endSetup();