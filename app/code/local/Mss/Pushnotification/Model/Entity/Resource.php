<?php

class Mss_Pushnotification_Model_Entity_Resource  extends Mage_Eav_Model_Entity_Attribute_Source_Abstract

{
	 public function getAllOptions()
    {
      if ($this->_options === null) {
         $this->_options = array(
            
            array(
                'value' => '1',
                'label' => 'yes',
            ),
            array(
                'value' => '0',
                'label' => 'no',
            ),

        );
    }
      return $this->_options;
   }
}