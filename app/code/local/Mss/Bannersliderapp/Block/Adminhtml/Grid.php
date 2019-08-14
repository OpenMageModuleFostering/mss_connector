<?php
class Mss_Bannersliderapp_Block_Adminhtml_Grid extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_bannersliderapp';
    $this->_blockGroup = 'bannersliderapp';
    $this->_headerText = Mage::helper('bannersliderapp')->__('Banner Manager');
    $this->_addButtonLabel = Mage::helper('bannersliderapp')->__('Add Banner');
    parent::__construct();
  }
}