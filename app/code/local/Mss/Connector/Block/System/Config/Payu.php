<?php

class Mss_Connector_Block_System_Config_Payu
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {   
        $html = '';
        if(Mage::getStoreConfig('payment/payucheckout_shared/active') == ''):
        	$html = "<div id='messages'><ul class='messages'><li class='error-msg'>PayU extension is missing, Kindly install the PayU extension to make it work with mobile app.<span> <a href='https://github.com/PayU/plugin_magento/archive/master.zip'>Download Extension </a></span></li></ul></div>";
    	endif;

    	if(!Mage::getStoreConfig('payment/payucheckout_shared/active')):
			$html = "<div id='messages'><ul class='messages'><li class='error-msg'>PayU extension is Disabled, Kindly Enable the PayU extension to make it work with mobile app.<span></span></li></ul></div>";
    	endif;
        return $html;
    }
}