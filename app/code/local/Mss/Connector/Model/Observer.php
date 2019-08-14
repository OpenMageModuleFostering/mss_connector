<?php
class Mss_Connector_Model_Observer
{
	const XML_SECURE_KEY = 'magentomobileshop/secure/key';
	const ACTIVATION_URL = 'https://www.magentomobileshop.com/mobile-connect';
	const TRNS_EMAIL = 'trans_email/ident_general/email';

	public function notificationMessage()
	{
	  $adminsession = Mage::getSingleton('admin/session', array('name'=>'adminhtml'));

		if(!Mage::getStoreConfig(self::XML_SECURE_KEY) AND $adminsession->isLoggedIn()):

        	$href = self::ACTIVATION_URL.'?email='.Mage::getStoreConfig(self::TRNS_EMAIL).'&url='.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        	
        	Mage::getSingleton('core/session')->addError('Magentomobileshop extension is not activated yet, <a href="'.$href.'" target="_blank">Click here</a> to activate your extension.');
             	
        endif;

        $configValue = Mage::getStoreConfig('mss/connector/email');
        
		//if($configValue =='' AND $adminsession->isLoggedIn())
			//$this->sendemail();


     	
	}

	public function sendemail(){
		
		
			$current_store_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);;
		    $current_store_name = Mage::getStoreConfig('general/store_information/name');
		    $current_store_phone =Mage::getStoreConfig('general/store_information/phone');
		    $current_store_address = Mage::getStoreConfig('general/store_information/address');
		    $current_store_email = Mage::getStoreConfig('trans_email/ident_general/email');
		    $message = <<<MESSAGE
				Hello
				My Store name is : $current_store_name 
				My Store URl is : $current_store_url 
				My Store Contact Number is : $current_store_phone 
				My Store Address is : $current_store_address 
				My Store Email is : $current_store_email 
				Thank you,
				MagentoMobileshop Dev Tem
MESSAGE;
		  

			$to = "contact@magentomobileshop.com";
			
			$subject = "New Connector Installation ";		
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";		
			$headers .= 'From: <contact@magentomobileshop.com>' . "\r\n";
			$headers .= 'Cc: mss.yogendra@gmail.com' . "\r\n";
			$email = mail($to,$subject,$message,$headers);
		  
			if($email):
				$mssSwitch = new Mage_Core_Model_Config();
				$mssSwitch ->saveConfig('mss/connector/email', 1);										    
			endif;

			return true;
		
     }

}
