<?php
	class Mss_Connector_StoreinfoController extends Mage_Core_Controller_Front_Action {

		const MSS_STORE_EMAIL = 'mss/mss_info_group/store_email';
		const MSS_STORE_PHONENO = 'mss/mss_info_group/store_phoneno';
		

		public function _construct(){

			header('content-type: application/json; charset=utf-8');
			header("access-control-allow-origin: *");
			Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
			parent::_construct();
			
		}

		/*

			
			Working url : baseURL/restapi/storeinfo/getstoreinfo/
			URL : baseurl/restapi/storeinfo/getstoreinfo/
			Name : getstoreinfo
			Method : GET
			Response : JSON
			Return Response :
			{
			  "status": "success",
			  "data": {
			    "store_phoneno": "dummy text",
			    "store_email": "dummy text",
			    "store_weburl": "dummy text"
			  }
			}
		*/

		public function getstoreinfoAction(){
			try{
				$recipient_email = Mage::getStoreConfig('contacts/email/recipient_email');
				$store_name = Mage::getBaseUrl(); 
				$store_phone = Mage::getStoreConfig('general/store_information/phone'); 


					$storeinfo = array();
					if(Mage::getStoreConfig(self::MSS_STORE_PHONENO)):
						$result['store_phoneno'] = Mage::getStoreConfig(self::MSS_STORE_PHONENO);
					else:
						$result['store_phoneno'] = $store_phone;
					endif;

					if(Mage::getStoreConfig(self::MSS_STORE_EMAIL)):
						$result['store_email'] = Mage::getStoreConfig(self::MSS_STORE_EMAIL);
					else:
						$result['store_email'] = $recipient_email; 
					endif;  
					
						$result['store_weburl'] = $store_name;
					
					$storeinfo = $result;

					echo json_encode(array('status'=>'success','data'=>$storeinfo));
			}
			catch(exception $e){

					echo json_encode(array('status'=>'error','message'=>'Problem in loading data.'));
					exit;
			}
		
		}	
	}	