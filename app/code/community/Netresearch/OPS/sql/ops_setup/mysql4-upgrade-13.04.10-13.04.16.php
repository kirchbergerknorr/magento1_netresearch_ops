<?php
$installer = $this;
$installer->startSetup();

// delete obsolete credit card brands
$obsoleteCCTypes = array(
    'billy',
    'solo',
    'aurora',
    'netreserve',

);

// remove obsolete credit card brands from the configured types array
$ccTypes = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_cc/types')
    ->load();
foreach ($ccTypes as $ccType) {
    $newCcTypes = array();
    $values = explode(',', $ccType->getValue());
    foreach ($values as $value) {
        if (in_array(strToLower($value), $obsoleteCCTypes)) {
            continue;
        }
        $newCcTypes[] = $value;
    }
    $ccType->setValue(implode(',', $newCcTypes));
    $ccType->save();
}

// remove obsolete credit card brands from available types array
$ccTypes = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_cc/availableTypes')
    ->load();
foreach ($ccTypes as $ccType) {
    $newCcTypes = array();
    $values = explode(',', $ccType->getValue());
    foreach ($values as $value) {
        if (in_array(strToLower($value), $obsoleteCCTypes)) {
            continue;
        }
        $newCcTypes[] = $value;
    }
    $ccType->setValue(implode(',', $newCcTypes));
    $ccType->save();
}

// remove obsolete credit card brands from the configured inline types array
$ccTypes = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_cc/inline_types')
    ->load();
foreach ($ccTypes as $ccType) {
    $newCcTypes = array();
    $values = explode(',', $ccType->getValue());
    foreach ($values as $value) {
        if (in_array(strToLower($value), $obsoleteCCTypes)) {
            continue;
        }
        $newCcTypes[] = $value;
    }
    $ccType->setValue(implode(',', $newCcTypes));
    $ccType->save();
}

// delete obsolete payment method config acceptgiro and centea online
$installer->run(
    "
    DELETE FROM {$this->getTable('core_config_data')}
    WHERE 'path' IN
    ('payment/ops_acceptgiro/active',
     'payment/ops_acceptgiro/title',
     'payment/ops_acceptgiro/sort_order'
    );
    DELETE FROM {$this->getTable('core_config_data')}
    WHERE `path` IN
    ('payment/ops_centeaonline/active',
     'payment/ops_centeaonline/title',
     'payment/ops_centeaonline/sort_order'
    );
"
);


// update dexiaOnline to belfiusOnline
$belfiusActive = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_belfiusDirectNet/active')
    ->load();
if (0 === $belfiusActive->count()) {
    $installer->run(
        "
        UPDATE {$this->getTable('core_config_data')}
        SET path = 'payment/ops_belfiusDirectNet/active'
        WHERE path = 'payment/ops_dexiaDirectNet/active';
    "
    );
}

$belfiusTitle = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_belfiusDirectNet/title')
    ->load();
if (0 == $belfiusTitle->count()) {
    $installer->run(
        "
        UPDATE {$this->getTable('core_config_data')}
        SET path = 'payment/ops_belfiusDirectNet/title'
        WHERE path = 'payment/ops_dexiaDirectNet/title';
    "
    );
}

$belfiusSortOrder = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'payment/ops_belfiusDirectNet/sort_order')
    ->load();
if (0 === $belfiusSortOrder->count()) {
    $installer->run(
        "
        UPDATE {$this->getTable('core_config_data')}
        SET path = 'payment/ops_belfiusDirectNet/sort_order'
        WHERE path = 'payment/ops_dexiaDirectNet/sort_order';
    "
    );
}
/*
 *
 */
$installer->endSetup();