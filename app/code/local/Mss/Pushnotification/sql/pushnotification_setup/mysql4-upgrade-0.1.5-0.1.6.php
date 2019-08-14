<?php
$installer = $this;

$installer->startSetup();


/*create user Attribute*/

	$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

	$entityTypeId     = $setup->getEntityTypeId('customer');
	$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
	$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

	$installer->addAttribute("customer", "pushnotification",  array(
	    "type"     => "int",
	    "backend"  => "",
	    "label"    => "Push Notification",
	    "input"    => "select",
	    "source"   => "pushnotification/entity_resource",
	    "visible"  => true,
	    "required" => false,
	    "default" => "",
	    "frontend" => "",
	    "unique"     => false,
	    "note"       => "Push Notification"

	));

	$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "pushnotification");


	$setup->addAttributeToGroup(
	    $entityTypeId,
	    $attributeSetId,
	    $attributeGroupId,
	    'module',
	    '999'  //sort_order
	);

	$used_in_forms=array();

	$used_in_forms[]="adminhtml_customer";

	$attribute->setData("used_in_forms", $used_in_forms)
	    ->setData("is_used_for_customer_segment", true)
	    ->setData("is_system", 0)
	    ->setData("is_user_defined", 1)
	    ->setData("is_visible", 1)
	    ->setData("sort_order", 100)
	;
	$attribute->save();

/*Create table script*/


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

