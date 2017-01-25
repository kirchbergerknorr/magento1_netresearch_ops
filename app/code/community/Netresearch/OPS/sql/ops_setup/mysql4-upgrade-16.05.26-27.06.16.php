<?php
/** @var Mage_Core_Model_Resource_Setup $installer */

$installer = $this;

$installer->getConnection()->modifyColumn(
    $installer->getTable('ops/alias'), 'alias', "varchar(255) NULL DEFAULT NULL"
);
