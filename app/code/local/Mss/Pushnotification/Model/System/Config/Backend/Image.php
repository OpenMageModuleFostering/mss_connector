<?php
class Mss_Pushnotification_Model_System_Config_Backend_Image extends Mage_Adminhtml_Model_System_Config_Backend_File
	{
		protected function _getAllowedExtensions()
	    	{
		$extension=array('png','jpeg','gif');
		return $extension;
	    	}
	}
?>
