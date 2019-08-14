<?php
$installer = $this;

$installer->startSetup();


/*create user Attribute*/

	$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

	$entityTypeId     = $setup->getEntityTypeId('customer');
	$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
	$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

	$installer->addAttribute("customer", "sociallogin_type",  array(
	    "type"     => "varchar",
	    "backend"  => "",
	    "label"    => "Social Login Type",
	    "input"    => "text",
	    "visible"  => true,
	    "required" => false,
	    "default" => "",
	    "frontend" => "",
	    "unique"     => false,
	    "note"       => "Social Login Type"

	));

	$attribute   = Mage::getSingleton("eav/config")->getAttribute("customer", "sociallogin_type");


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