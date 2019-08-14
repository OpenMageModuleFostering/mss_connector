<?php
$installer = $this;
$installer->startSetup();
$sql="CREATE TABLE {$this->getTable('magentomobile_bannerslider')} (
  `banner_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `order_banner` int(11) NULL default '0',
  `status` smallint(6) NOT NULL default '0',
  `url_type` varchar(20) NULL default '',
  `product_id` int(11) NULL,
  `category_id` int(11) NULL,
  `image` varchar(255) NULL,
  `image_alt` varchar(255) NULL,

  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$installer->run($sql);

$installer->endSetup();
   