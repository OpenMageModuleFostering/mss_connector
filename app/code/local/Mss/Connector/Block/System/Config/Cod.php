<?php

class Mss_Connector_Block_System_Config_Cod
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
    	$html = '';
        if(!Mage::getStoreConfig('payment/cashondelivery/active')):
			$html = "<div id='messages'><ul class='messages'><li class='error-msg'>Cashondelivery method is Disabled, Kindly Enable the cashondelivery Payment method to make it work with mobile app.<span></span></li></ul></div>";
    	endif;
        return $html;
    }
}