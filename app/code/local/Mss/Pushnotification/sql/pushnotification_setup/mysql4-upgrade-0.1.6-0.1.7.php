<?php

$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('notification')}
	ADD COLUMN `create_date` DATE NOT NULL AFTER `device_type`,
	ADD COLUMN `update_date` DATE NOT NULL AFTER `create_date`,
	ADD COLUMN `app_status` INT NOT NULL AFTER `update_date`;
	");
$installer->endSetup();