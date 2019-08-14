<?php
class Mss_Connector_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{

	const XML_SECURE_KEY = 'magentomobileshop/secure/key';
	const ACTIVATION_URL = 'https://www.magentomobileshop.com/mobile-connect';
	const TRNS_EMAIL = 'trans_email/ident_general/email';

	public function getMessage()
	{
	  	$href ='';
		if(!Mage::getStoreConfig(self::XML_SECURE_KEY))

        	$href = '<strong class="label">Magentomobileshop</strong> extension is not activated yet, <a href="'.self::ACTIVATION_URL.'?email='.Mage::getStoreConfig(self::TRNS_EMAIL).'&url='.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
        		.'" target="_blank">Click here</a> to activate your extension.';
              
        return $href;

      
	}
   
}