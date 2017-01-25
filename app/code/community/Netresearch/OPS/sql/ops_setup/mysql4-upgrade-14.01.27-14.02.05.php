<?php
$installer = $this;
$installer->startSetup();

$tableName = $installer->getTable('ops/kwixo_shipping_setting');
$table     = $installer->getConnection()
    ->newTable($tableName)
    ->addColumn(

        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                                                      'unsigned' => true,
                                                                      'nullable' => false,
                                                                      'primary'  => true,
                                                                      'identity' => true,
                                                                 ),
        'id'
    )
    ->addColumn(
        'shipping_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                                                                      'nullable' => false,
                                                                 ),
        'shipping code'
    )
    ->addColumn(

        'kwixo_shipping_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                                                                              'unsigned' => true,
                                                                              'nullable' => false,
                                                                         ),
        'kwixo shipping type'
    )
    ->addColumn(
        'kwixo_shipping_speed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
                                                                               'unsigned' => true,
                                                                               'nullable' => false,
                                                                               'primary'  => false,
                                                                               'identity' => false,
                                                                          ),
        'kwixo shipping type'
    )
    ->addColumn(

        'kwixo_shipping_details', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
                                                                              'unsigned' => true,
                                                                              'nullable' => true,
                                                                         ),
        'kwixo shipping details'
    )
    ->addIndex('unique', 'shipping_code');
$installer->getConnection()->createTable($table);
$installer->endSetup();

