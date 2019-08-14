<?php

class Mss_Bannersliderapp_Block_Adminhtml_Bannersliderapp_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
      $form = new Varien_Data_Form();
       $this->setForm($form);
       $fieldset = $form->addFieldset('banner_form',
                                       array('legend'=>'Banner Information'));
        $fieldset->addField('name', 'text',
                       array(
                          'label' => Mage::helper('bannersliderapp')->__('Title'),
                          'class' => 'required-entry',
                          'required' => true,
                           'name' => 'name',
                    ));
        $fieldset->addType('thumbnail','Mss_Bannersliderapp_Block_Adminhtml_Bannersliderapp_Helper_Image');
  
        $fieldset->addField('image', 'thumbnail', array(
                'label'     => Mage::helper('bannersliderapp')->__('Image'),
                'required'  => true,
                'name'      => 'image',
          ));
         $fieldset->addField('image_alt', 'textarea',
                       array(
                          'label' => Mage::helper('bannersliderapp')->__('Description'),
                         
                          
                           'name' => 'image_alt',
                    ));
         $fieldset->addField('order_banner', 'text',
                array(
                    'label' => Mage::helper('bannersliderapp')->__('Order'),
                    
                    'name' => 'order_banner',
             ));
      $fieldset->addField('url_type', 'select',
                         array(
                            'label' => Mage::helper('bannersliderapp')->__('Link To'),
                            'after_element_html' => '<small>Add catagory link section</small>',
                            'values'    => array(

                              array(
                                  'value'     => 'Category',
                                  'label'     => Mage::helper('core')->__('Category'),
                              ),
                              array(
                                  'value'     => 'Product',
                                  'label'     => Mage::helper('core')->__('Product'),
                              ),
                          ),
                          
                          'name' => 'url_type',
                      ));
/*custom field*/
           $fieldset->addField('check_type', 'select',
                         array(
                          'label' => Mage::helper('bannersliderapp')->__('Display on page'),
                           'values'    => array(
                              array(
                                  'value'     => 'home_view',
                                  'label'     => Mage::helper('core')->__('Home View'),
                              ),
                              array(
                                  'value'     => 'category_view',
                                  'label'     => Mage::helper('core')->__('Category View'),
                              ),
                          ),
                          
                          'name' => 'check_type',
                      ));
/*custom field*/

        $fieldset->addField('product_id', 'text',
                  array(
                      'label' => Mage::helper('bannersliderapp')->__('Product Id to Display'),
                     
                      
                      'name' => 'product_id',
               ));
       $fieldset->addField('category_id', 'text',
                array(
                    'label' => Mage::helper('bannersliderapp')->__('Category Id to Display'),
                    
                    
                    'name' => 'category_id',
             ));
       $fieldset->addField('status', 'select',
                array(
                    'label' => Mage::helper('bannersliderapp')->__('Status'),
                    
                    'values'    => array(
                             

                              array(
                                  'value'     => 0,
                                  'label'     => Mage::helper('core')->__('Disable'),
                              ),
                               array(
                                  'value'     => 1,
                                  'label'     => Mage::helper('core')->__('Enable'),
                              ),
                          ),
                    'name' => 'status',
             ));

 if ( Mage::registry('banner_data') )
 {
    $form->setValues(Mage::registry('banner_data')->getData());
  }
  return parent::_prepareForm();
 }
}