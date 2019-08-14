<?php

class Mss_Bannersliderapp_Block_Adminhtml_Bannersliderapp_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('banner_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('bannersliderapp')->__('Banner Manager'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('bannersliderapp')->__('Banner Information'),
          'title'     => Mage::helper('bannersliderapp')->__('Banner Information'),
          'content'   => $this->getLayout()->createBlock('bannersliderapp/adminhtml_bannersliderapp_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}