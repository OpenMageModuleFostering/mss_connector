<?php

class Mss_Bannerslider_Block_Adminhtml_Bannerslider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
      $form = new Varien_Data_Form();
       $this->setForm($form);
       $fieldset = $form->addFieldset('banner_form',
                                       array('legend'=>'Banner Information'));
        $fieldset->addField('name', 'text',
                       array(
                          'label' => Mage::helper('bannerslider')->__('Title'),
                          'class' => 'required-entry',
                          'required' => true,
                           'name' => 'name',
                    ));
        $fieldset->addType('thumbnail','Mss_Bannerslider_Block_Adminhtml_Bannerslider_Helper_Image');
  
        $fieldset->addField('image', 'thumbnail', array(
                'label'     => Mage::helper('bannerslider')->__('Image'),
                'required'  => true,
                'name'      => 'image',
          ));
         $fieldset->addField('image_alt', 'textarea',
                       array(
                          'label' => Mage::helper('bannerslider')->__('Description'),
                         
                          
                           'name' => 'image_alt',
                    ));
         $fieldset->addField('order_banner', 'text',
                array(
                    'label' => Mage::helper('bannerslider')->__('Order'),
                   
                    
                    
                    'name' => 'order_banner',
             ));
        $fieldset->addField('url_type', 'select',
                         array(
                          'label' => Mage::helper('bannerslider')->__('Link With Type'),
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

        $fieldset->addField('product_id', 'text',
                  array(
                      'label' => Mage::helper('bannerslider')->__('Product Id to Display'),
                     
                      
                      'name' => 'product_id',
               ));
       $fieldset->addField('category_id', 'text',
                array(
                    'label' => Mage::helper('bannerslider')->__('Category Id to Display'),
                    
                    
                    'name' => 'category_id',
             ));
       $fieldset->addField('status', 'select',
                array(
                    'label' => Mage::helper('bannerslider')->__('Status'),
                    
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