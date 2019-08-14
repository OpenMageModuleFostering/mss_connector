<?php

class Mss_Connector_Block_System_Config_About
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

	
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = file_get_contents('http://magentomobileshop.com/wp-content/magentomobiledata/about_us.html');
        return $html;
    }
}
