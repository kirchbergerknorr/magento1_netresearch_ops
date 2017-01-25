<?php
$installer = $this;
$installer->startSetup();
$conn = $installer->getConnection();
$conn->addColumn(
    $this->getTable('ops/alias'),
    'card_holder',
    'VARCHAR(255) NULL DEFAULT NULL AFTER `alias`'
);
$conn->addColumn(
    $this->getTable('ops/alias'),
    'state',
    "VARCHAR(100) NULL DEFAULT '". Netresearch_OPS_Model_Alias_State::PENDING ."' AFTER `payment_method`"
);

$conn->addColumn(
    $this->getTable('ops/alias'),
    'store_id',
    "smallint(5) NULL DEFAULT NULL AFTER `state`"
);

$installer->run(
    "
    UPDATE {$this->getTable('ops_alias')}
    SET state = '". Netresearch_OPS_Model_Alias_State::ACTIVE ."'
    WHERE alias IS NOT NULL;

"
);

$installer->run(
    "
    DELETE FROM {$this->getTable('ops_alias')}
    WHERE id NOT in (
    SELECT alias.id FROM (
    SELECT * FROM {$this->getTable('ops_alias')}
    ORDER BY `created_at` desc
    ) as alias
    GROUP BY alias.customer_id, alias.billing_address_hash, alias.shipping_address_hash
    )
"
);

$aliasActive = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_cc/active_alias')
    ->load();
if (0 === $aliasActive->count()) {
    $installer->run(
        "
        UPDATE {$this->getTable('core_config_data')}
        SET path = 'payment/ops_cc/active_alias'
        WHERE path = 'payment/ops_alias/active';
    "
    );
}
$hintForGuestsActive = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_cc/show_alias_manager_info_for_guests')
    ->load();
if (0 == $hintForGuestsActive->count()) {
        $installer->run(
            "
        UPDATE {$this->getTable('core_config_data')}
        SET path = 'payment/ops_cc/show_alias_manager_info_for_guests'
        WHERE path = 'payment/ops_alias/show_info_for_guests';
    "
        );
}

$installer->endSetup();

