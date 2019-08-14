<?php

class Mss_Connector_MenuController extends Mage_Core_Controller_Front_Action {
	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
		parent::_construct();
		
	}

	public function suggestAction() {

		
	}

	public function getproductinfoAction() {

		 $productid=$this->getRequest ()->getParam ( 'productid' );

		//$productid = '1'; // test sentence

		$product = Mage::getModel ( 'catalog/product' )->load ( $productid );

		

		foreach ( $product->getAttributes () as $att ) {

			$group_id = $att->getData ( 'attribute_group_id' );

			$group = Mage::getModel ( 'eav/entity_attribute_group' )->load ( $group_id );

			var_dump ( $group );

		}

		$attrSetName = 'my_custom_attribute';

		$attributeSetId = Mage::getModel ( 'eav/entity_attribute_set' )->load ( $attrSetName, 'attribute_set_name' )->getAttributeSetId ();

		// get a drop down lists options for a mulit-select attribute

		$attribute = Mage::getModel ( 'eav/config' )->getAttribute ( 'catalog_product', 'attribute_id' );

		foreach ( $attribute->getSource ()->getAllOptions ( true, true ) as $option ) {

			$attributeArray [$option ['value']] = $option ['label'];

		}

		var_dump ( $$attributeArray );

		

		$sets = Mage::getResourceModel ( 'eav/entity_attribute_set_collection' )->setEntityTypeFilter ( Mage::getModel ( 'catalog/product' )->getResource ()->getTypeId () )->load ()->toOptionHash ();

	}

	public function getcustomeroptionAction(){

		$productid=$this->getRequest ()->getParam ( 'productid' );

		$product = Mage::getModel("catalog/product")->load($productid);

		$i = 1;		 

		echo "<pre>";

		foreach ($product->getOptions() as $o) {			 

			echo "<strong>Custom Option:" . $i . "</strong><br/>";

			echo "Custom Option TYPE: " . $o->getType() . "<br/>";

			echo "Custom Option TITLE: " . $o->getTitle() . "<br/>";

			echo "Custom Option Values: <br/>";

			// Getting Values if it has option values, case of select,dropdown,radio,multiselect		 

			$values = $o->getValues();

			foreach ($values as $v) {				 

				print_r($v->getData());

			}			 

			$i++;			 

			echo "----------------------------------<br/>";

		}

	}

} 
