<?php

class Mss_Bannersliderapp_Block_Adminhtml_Bannersliderapp_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'bannersliderapp';
        $this->_controller = 'adminhtml_bannersliderapp';
        
        $this->_updateButton('save', 'label', Mage::helper('bannersliderapp')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('bannersliderapp')->__('Delete Banner'));
		
      
    }

    public function getHeaderText()
    {
        if( Mage::registry('banner_data') && Mage::registry('banner_data')->getId() ) {
            return Mage::helper('bannersliderapp')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('banner_data')->getName()));
        } else {
            return Mage::helper('bannersliderapp')->__('Add Banner');
        }
    }
   
  
}