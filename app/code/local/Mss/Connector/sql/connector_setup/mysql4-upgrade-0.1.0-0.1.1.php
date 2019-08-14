<?php

$installer = Mage::getResourceModel('sales/setup', 'sales_setup');

$installer->startSetup();


$attribute  = array(
        'type'          => 'text',
        'backend_type'  => 'text',
        'frontend_input' => 'text',
        'is_user_defined' => true,
        'label'         => 'mms_order_type',
        'visible'       => true,
        'required'      => false,
        'user_defined'  => false,   
        'searchable'    => false,
        'filterable'    => false,
        'comparable'    => false,
        'default'       => ''
	);
    $installer->addAttribute("order", "mms_order_type",$attribute);
    $installer->addAttribute("quote", "mms_order_type",$attribute);

	$installer->endSetup();

