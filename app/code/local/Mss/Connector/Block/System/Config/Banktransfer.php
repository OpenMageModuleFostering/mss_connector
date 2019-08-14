<?php

class Mss_Connector_Block_System_Config_Banktransfer
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
    	$html = '';
        if(!Mage::getStoreConfig('payment/banktransfer/active')):
			$html = "<div id='messages'><ul class='messages'><li class='error-msg'>Bank Transfer Payment method is Disabled, Kindly Enable the Bank Transfer Payment method to make it work with mobile app.<span></span></li></ul></div>";
    	endif;
        return $html;
    }
}