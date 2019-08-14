<?php
class Mss_Connector_TokenController extends Mage_Core_Controller_Front_Action {

	const XML_SECURE_KEY_STATUS = 'magentomobileshop/key/status';
	const XML_SECURE_KEY = 'magentomobileshop/secure/key';
	const XML_SECURE_TOKEN = 'magentomobileshop/secure/token';
	const XML_SECURE_TOKEN_EXP = 'secure/token/exp';


	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		parent::_construct();
		
	}

	/*
		
		URL : baseurl/restapi/token/setToken/
		Name : setToken
		Method : GET
		Parameters : secure_key*,status*
		Response : JSON
		Return Response :
		{
		  "status": "success",
		  "message": "return message"
		}
	*/

	public function setTokenAction(){ 

		try{
			$params = $this->getRequest ()->getParams ();
			//$params = apache_request_headers();


			if(isset($params['secure_key']) && isset($params['status'])):

				$configuration = array(
							self::XML_SECURE_KEY_STATUS=>$params['status'],
							self::XML_SECURE_KEY =>$params['secure_key']
							);

				foreach($configuration as $path => $value){
					$this->saveConfig($path,$value);
				}
				
				$tags = array("CONFIG");
				Mage::app ()->cleanCache($tags);

				echo json_encode(array('status'=>'success','message'=>'Data updated.'));
			else:

				echo json_encode(array('status'=>'error','message'=>'Required parameters are missing.'));

			endif;

		}
		catch(exception $e){

			echo json_encode(array('status'=>'error','message'=>$e->getMessage()));

		}
	}

	/*
		
		URL : baseurl/restapi/token/getToken/
		Name : getToken
		Method : Header
		Parameters : secure_key*,status*
		Response : JSON
		Return Response :
		{
		  "status": "error"/"success",
		  "message"/"token": "return message"
		}
	*/

	public function getTokenAction(){

		try{
			if(Mage::getStoreConfig(self::XML_SECURE_KEY_STATUS)):
				
				
				$params = Mage::app()->getFrontController()->getRequest()->getHeader('token');
				
				if(isset($params)):
					
					

					if($params == Mage::getStoreConfig(self::XML_SECURE_KEY)):

						if(Mage::getStoreConfig(self::XML_SECURE_TOKEN_EXP) && 
							Mage::helper('connector')->compareExp() < 24):
							
								echo json_encode(array('status'=>'success','token'=> Mage::getStoreConfig(self::XML_SECURE_TOKEN)));
								exit;
						endif;

						$token = $this->radToken();
						$current_session = Mage::getModel('core/date')->date('Y-m-d H:i:s');

						$configuration = array(
							self::XML_SECURE_TOKEN=>$token,
							self::XML_SECURE_TOKEN_EXP =>$current_session
							);
						foreach($configuration as $path => $value){
							$this->saveConfig($path,$value);
						}
						
						//clearing cache
						$tags = array("CONFIG");
						Mage::app ()->cleanCache($tags);
						
						$this->getSession();

						if(Mage::app()->getFrontController()->getRequest()->getHeader('username') && Mage::app()->getFrontController()->getRequest()->getHeader('password')):
							echo json_encode(array('status'=>'success','token'=>$token,'user'=>$this->usersession(Mage::app()->getFrontController()->getRequest()->getHeader('username'),Mage::app()->getFrontController()->getRequest()->getHeader('username'))));
							exit;
						else:
							echo json_encode(array('status'=>'success','token'=>$token));
							exit;
						endif;
					else:
						echo json_encode(array('status'=>'error','message'=> 'Invalid secure key.'));
					endif;
				else:

					echo json_encode(array('status'=>'error','message'=>'Secure key is required.'));

				endif;

			else:
					echo json_encode(array('status'=>'error','message'=>'App is disabled by magentomobileshop admin.'));
			endif;

		}
		catch(exception $e){

			echo json_encode(array('status'=>'error','message'=>$e->getMessage()));

		}
	}

	

	private function radToken()
	{
		return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,25))),1,25);
	}

	private function saveConfig($path,$value){

		Mage::getModel('core/config')->saveConfig($path,$value);
		return true;
	}

	public function getSession(){

		  $adminSessionLifetime = (int)Mage::getStoreConfig('admin/security/session_cookie_lifetime'); 
		  if($adminSessionLifetime < 86400)
		  	$this->saveConfig('admin/security/session_cookie_lifetime','86400');

		  return true;
	}

	private function usersession($username,$password){
		$session = Mage::getSingleton ( 'customer/session' );

		if (Mage::getSingleton ( 'customer/session' )->isLoggedIn ())
			return true;
		
		try 
		{
			if (!$session->login ( $username, $password ))
				return false;
			else
				return true;
		} 
		catch ( Mage_Core_Exception $e ) 
		{
			return false;
		}

	}
}