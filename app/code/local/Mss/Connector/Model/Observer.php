<?php
class Mss_Connector_Model_Observer
{
	const XML_SECURE_KEY = 'magentomobileshop/secure/key';
	const ACTIVATION_URL = 'https://www.magentomobileshop.com/user/mss_verifiy';
	const TRNS_EMAIL = 'trans_email/ident_general/email';
	const XML_SECURE_KEY_STATUS = 'magentomobileshop/key/status'; 

	public function notificationMessage()
	{
	    $adminsession = Mage::getSingleton('admin/session', array('name'=>'adminhtml'));
		$allStores = Mage::app()->getStores();
		$_storeId = count($allStores);
		if($_storeId>1){
		  	if(!Mage::getStoreConfig('web/url/use_store')):
		  		$mssSwitch = new Mage_Core_Model_Config();
		  		$mssSwitch->saveConfig('web/url/use_store', 1);
		  	endif;
		}

	  	$url =  Mage::helper('core/url')->getCurrentUrl('key');
     	$url_path = parse_url($url, PHP_URL_PATH);
		$token = pathinfo($url_path, PATHINFO_BASENAME);

		$decode =  Mage::app()->getRequest()->getParam('mms_id');

		$mssAppData = '';

		if($decode AND !Mage::registry('mms_app_data')) {
			$param =  base64_decode($decode);
			Mage::register('mms_app_data', $param);
			$mssAppData = Mage::registry('mms_app_data');
	
		}
		$current = Mage::getStoreConfig('magentomobileshop/secure/key');
		$array = array();
		$third_party = array();
		foreach(Mage::getConfig()->getNode('modules')->children() as $item){

			    $name = $item->getName();
			        if(strrpos($name,"Mage_") === false ):
			        (array_push($third_party,$name));
			        endif;   
			}
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
		$payMethods = array();
			foreach ($payments as $paymentCode=>$paymentModel) 
			{
			    $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			    $payMethods[$paymentCode] = $paymentTitle;
			}
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
		$shipMethods = array();
			foreach ($methods as $shippigCode=>$shippingModel) 
			{
			    $shippingTitle = Mage::getStoreConfig('carriers/'.$shippigCode.'/title');
			    $shipMethods[$shippigCode] = $shippingTitle;
			}		
		if((!$current)  AND $adminsession->isLoggedIn() AND $mssAppData != '' ) { 
				
           	$str = self::ACTIVATION_URL;
			$url = $str.'?mms_id=';
 		    $final_url =  $url.''.$mssAppData;
		    $final_urls =  $str;
			$mssSwitch = new Mage_Core_Model_Config();
			$mssSwitch->saveConfig(self::XML_SECURE_KEY, $mssAppData);
			$mssSwitch->saveConfig(self::XML_SECURE_KEY_STATUS, '1');
			$locale = Mage::app()->getLocale()->getLocaleCode();
			$lang = explode("_",$locale);

	    	$mssData = array();
	    	$mssData[0]['mms_id'] = base64_encode($mssAppData);
			$mssData[0]['default_store_name'] = Mage::app()->getDefaultStoreView()->getCode();
			$mssData[0]['default_store_id'] = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
			$mssData[0]['default_view_id'] = Mage::app()->getDefaultStoreView()->getId();
			$mssData[0]['default_store_currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();
			$mssData[0]['language'] = $lang[0];
			$mssData[0]['status'] = 'true';
			$mssData[0]['Installed Active Extensions'] = $third_party;
			$mssData[0]['Active Payment Methods'] = $payMethods;
			$mssData[0]['Active Shipping Methods'] = $shipMethods;
		
			Mage::app()->getCacheInstance()->cleanType('config');
			Mage::getSingleton('core/session')->setAppDatas($mssData[0]);
			Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("connector/adminhtml_support/landing/"))->sendResponse();
		    	exit;
		} elseif($current != ''  AND $adminsession->isLoggedIn() AND $decode != '') { 

			$str = self::ACTIVATION_URL;
			$url = $str.'?mms_id=';
 		    $final_url =  $url.''.$mssAppData;
		    $final_urls =  $str;
			$mssSwitch = new Mage_Core_Model_Config();
			$mssSwitch->saveConfig(self::XML_SECURE_KEY, $mssAppData);
			$mssSwitch->saveConfig(self::XML_SECURE_KEY_STATUS, '1');
			$locale = Mage::app()->getLocale()->getLocaleCode();
			$lang = explode("_",$locale);

	    	$mssData[0]['mms_id'] = base64_encode($mssAppData);
			$mssData[0]['default_store_name'] = Mage::app()->getDefaultStoreView()->getCode();
			$mssData[0]['default_store_id'] = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
			$mssData[0]['default_view_id'] = Mage::app()->getDefaultStoreView()->getId();
			$mssData[0]['default_store_currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();
			$mssData[0]['language'] = $lang[0];
			$mssData[0]['status'] = 'true';
			$mssData[0]['Installed Active Extensions'] = $third_party;
			$mssData[0]['Active Payment Methods'] = $payMethods;
			$mssData[0]['Active Shipping Methods'] = $shipMethods;

			Mage::app()->getCacheInstance()->cleanType('config');
			Mage::getSingleton('core/session')->setAppDatas($mssData[0]);
			Mage::unregister('mms_app_data');
			
			Mage::app()->getResponse()->setRedirect(Mage::helper("adminhtml")->getUrl("connector/adminhtml_support/landing/"))->sendResponse();
		    	exit;
		}
		if(!Mage::getStoreConfig(self::XML_SECURE_KEY) AND $adminsession->isLoggedIn()):
			$static_url  = 'https://www.magentomobileshop.com/user/buildApp?key_info=';

			$email =      base64_encode(Mage::getStoreConfig(self::TRNS_EMAIL));
			$url =  base64_encode(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
			$key = base64_encode('email='.$email.'&url='.$url);
    	    $href = $static_url.$key;
    	
    	Mage::getSingleton('core/session')->addError('Magentomobileshop extension is not activated yet, <a href="'.$href.'">Click here</a> to activate your extension.');
        endif;
    	
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
				$mssSwitch->saveConfig('mss/connector/email', 1);										    
			endif;
			return true;
		
     }


}


