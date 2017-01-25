<?php
/**
 * @author      Michael Lühr <michael.luehr@netresearch.de>
 * @category    Netresearch
 * @copyright   Copyright (c) 2014 Netresearch GmbH & Co. KG (http://www.netresearch.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/*
 * @var $installer Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$regions = array(
    array("CN",11,"Beijing"),
    array("CN",50,"Chongqing"),
    array("CN",31,"Shanghai"),
    array("CN",12,"Tianjin"),
    array("CN",34,"Anhui"),
    array("CN",35,"Fujian"),
    array("CN",62,"Gansu"),
    array("CN",44,"Guangdong"),
    array("CN",52,"Guizhou"),
    array("CN",46,"Hainan"),
    array("CN",13,"Hebei"),
    array("CN",23,"Heilongjiang"),
    array("CN",41,"Henan"),
    array("CN",42,"Hubei"),
    array("CN",43,"Hunan"),
    array("CN",32,"Jiangsu"),
    array("CN",36,"Jiangxi"),
    array("CN",22,"Jilin"),
    array("CN",21,"Liaoning"),
    array("CN",63,"Qinghai"),
    array("CN",61,"Shaanxi"),
    array("CN",37,"Shandong"),
    array("CN",14,"Shanxi"),
    array("CN",51,"Sichuan"),
    array("CN",71,"Taiwan"),
    array("CN",53,"Yunnan"),
    array("CN",33,"Zhejiang"),
    array("CN",45,"Guangxi"),
    array("CN",15,"Nei Mongol"),
    array("CN",64,"Ningxia"),
    array("CN",65,"Xinjiang"),
    array("CN",54,"Xizang"),
    array("CN",91,"Xianggang"),
    array("CN",92,"Aomen"),
    array("JP",1,"Hokkaidô"),
    array("JP",2,"Aomori"),
    array("JP",3,"Iwate"),
    array("JP",4,"Miyagi"),
    array("JP",5,"Akita"),
    array("JP",6,"Yamagata"),
    array("JP",7,"Hukusima"),
    array("JP",8,"Ibaraki"),
    array("JP",9,"Totigi"),
    array("JP",10,"Gunma"),
    array("JP",11,"Saitama"),
    array("JP",12,"Tiba"),
    array("JP",13,"Tôkyô"),
    array("JP",14,"Kanagawa"),
    array("JP",15,"Niigata"),
    array("JP",16,"Toyama"),
    array("JP",17,"Isikawa"),
    array("JP",18,"Hukui"),
    array("JP",19,"Yamanasi"),
    array("JP",20,"Nagano"),
    array("JP",21,"Gihu"),
    array("JP",22,"Sizuoka"),
    array("JP",23,"Aiti"),
    array("JP",24,"Mie"),
    array("JP",25,"Siga"),
    array("JP",26,"Kyôto"),
    array("JP",27,"Ôsaka"),
    array("JP",28,"Hyôgo"),
    array("JP",29,"Nara"),
    array("JP",30,"Wakayama"),
    array("JP",31,"Tottori"),
    array("JP",32,"Simane"),
    array("JP",33,"Okayama"),
    array("JP",34,"Hirosima"),
    array("JP",35,"Yamaguti"),
    array("JP",36,"Tokusima"),
    array("JP",37,"Kagawa"),
    array("JP",38,"Ehime"),
    array("JP",39,"Kôti"),
    array("JP",40,"Hukuoka"),
    array("JP",41,"Saga"),
    array("JP",42,"Nagasaki"),
    array("JP",43,"Kumamoto"),
    array("JP",44,"Ôita"),
    array("JP",45,"Miyazaki"),
    array("JP",46,"Kagosima"),
    array("JP",47,"Okinawa"),
    array("MX","DIF","Distrito Federal"),
    array("MX","AGU","Aguascalientes"),
    array("MX","BCN","Baja California"),
    array("MX","BCS","Baja California Sur"),
    array("MX","CAM","Campeche"),
    array("MX","COA","Coahuila"),
    array("MX","COL","Colima"),
    array("MX","CHP","Chiapas"),
    array("MX","CHH","Chihuahua"),
    array("MX","DUR","Durango"),
    array("MX","GUA","Guanajuato"),
    array("MX","GRO","Guerrero"),
    array("MX","HID","Hidalgo"),
    array("MX","JAL","Jalisco"),
    array("MX","MEX","México"),
    array("MX","MIC","Michoacán"),
    array("MX","MOR","Morelos"),
    array("MX","NAY","Nayarit"),
    array("MX","NLE","Nuevo León"),
    array("MX","OAX","Oaxaca"),
    array("MX","PUE","Puebla"),
    array("MX","QUE","Querétaro"),
    array("MX","ROO","Quintana Roo"),
    array("MX","SLP","San Luis Potosí"),
    array("MX","SIN","Sinaloa"),
    array("MX","SON","Sonora"),
    array("MX","TAB","Tabasco"),
    array("MX","TAM","Tamaulipas"),
    array("MX","TLA","Tlaxcala"),
    array("MX","VER","Veracruz"),
    array("MX","YUC","Yucatán"),
    array("MX","ZAC","Zacatecas")
);

foreach ($regions as $row) {
    $bind = array(
        'country_id'    => $row[0],
        'code'          => $row[1],
        'default_name'  => $row[2],
    );
    $installer->getConnection()->insert($installer->getTable('directory/country_region'), $bind);
    $regionId = $installer->getConnection()->lastInsertId($installer->getTable('directory/country_region'));

    $bind = array(
        'locale'    => 'en_US',
        'region_id' => $regionId,
        'name'      => $row[2]
    );
    $installer->getConnection()->insert($installer->getTable('directory/country_region_name'), $bind);
}

$countries = explode(',', Mage::getStoreConfig(Mage_Directory_Helper_Data::XML_PATH_STATES_REQUIRED));
$countries[] = 'CN';
$countries[] = 'JP';
$countries[] = 'MX';
Mage::getConfig()->saveConfig(Mage_Directory_Helper_Data::XML_PATH_STATES_REQUIRED, implode(',', $countries));