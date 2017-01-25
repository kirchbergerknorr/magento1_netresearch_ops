<?php
$installer = $this;
$installer->startSetup();

$installer->run(
    "
        UPDATE {$this->getTable('core_config_data')}
        SET value = '-100'
        WHERE path = 'payment/ops_cc/sort_order'
        AND value is NULL
    "
);

$installer->endSetup();

