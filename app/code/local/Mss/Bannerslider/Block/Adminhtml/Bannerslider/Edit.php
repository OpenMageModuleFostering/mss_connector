<?php

class Mss_Bannerslider_Block_Adminhtml_Bannerslider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'bannerslider';
        $this->_controller = 'adminhtml_bannerslider';
        
        $this->_updateButton('save', 'label', Mage::helper('bannerslider')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('bannerslider')->__('Delete Banner'));
		
      
    }

    public function getHeaderText()
    {
        if( Mage::registry('banner_data') && Mage::registry('banner_data')->getId() ) {
            return Mage::helper('bannerslider')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('banner_data')->getName()));
        } else {
            return Mage::helper('bannerslider')->__('Add Banner');
        }
    }
   
  
}