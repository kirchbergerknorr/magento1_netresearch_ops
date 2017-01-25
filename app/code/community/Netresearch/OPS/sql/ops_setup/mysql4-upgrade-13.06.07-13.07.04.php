<?php
$installer = $this;
$installer->startSetup();



$installer->run(
    "
   UPDATE {$this->getTable('core_config_data')}
   SET value = 'Ingenico ePayments Belfius Direct Net'
   WHERE path = 'payment/ops_belfiusDirectNet/title'
   AND value = 'Ingenico ePayments BelfiusDirectNet';
 "
);

$installer->endSetup();

