<?php
$installer = $this;

$installer->startSetup();

$installer->run("DROP TABLE IF EXISTS {$this->getTable('notification')};
CREATE TABLE {$this->getTable('notification')} (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `user_id` int(11),
	  `registration_id` varchar(250),
	  `device_type` int(11),
	   PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
	$installer->endSetup();

