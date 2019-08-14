<?php
$installer = $this;

$installer->startSetup();

/*Create table script*/

$installer->run("DROP TABLE IF EXISTS {$this->getTable('magentomobilecc')};
CREATE TABLE {$this->getTable('magentomobilecc')} (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `user_id` int(11),
	  `cc_number` varchar(50),
	  `cc_last4` varchar(50),
	  `cc_type` varchar(10),
	  `cc_exp_month` varchar(10),
	  `cc_exp_year` varchar(10),
	   PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
	$installer->endSetup();

