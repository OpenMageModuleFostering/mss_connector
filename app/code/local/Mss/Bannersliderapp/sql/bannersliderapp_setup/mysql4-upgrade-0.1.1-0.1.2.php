<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()
->addColumn($installer->getTable('magentomobile_bannersliderapp'),'check_type', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => false,
    'length'    => 255,
    'after'     => null, // column name to insert new column after
    'comment'   => 'Title'
    ));   
$installer->endSetup();
