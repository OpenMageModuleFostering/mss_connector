<?php

class Mss_Connector_Block_System_Config_Checkmo
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
    	$html = '';
        if(!Mage::getStoreConfig('payment/checkmo/active')):
			$html = "<div id='messages'><ul class='messages'><li class='error-msg'>Check / Money Order method is Disabled, Kindly Enable the Check / Money Order method to make it work with mobile app.<span></span></li></ul></div>";
    	endif;
        return $html;
    }
}