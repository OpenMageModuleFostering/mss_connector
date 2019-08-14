<?php
class Mss_Sociallogin_Helper_Data extends Mage_Core_Helper_Abstract
{

	private $sociallogin_support = 	array('facebook','google');
	private $f_url = 'https://graph.facebook.com/me?fields=first_name,last_name,gender,email&format=json&access_token=';
    private $g_url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&alt=json&access_token=';
	private $sociallogin_type;

	public function socialloginRequest($token,$sociallogintype){
		
		if($token && $sociallogintype):
			
			$this->sociallogin_type = $sociallogintype;

			$this->sociallogin_support($sociallogintype);
			$this->getSocialdetails($token,$sociallogintype);

		else:
			return true;
		endif;


	}

	private function sociallogin_support($sociallogintype)
	{
		if(!in_array($sociallogintype, $this->sociallogin_support)):
			echo json_encode(array('status'=>'error','message'=> $this->__('Social Login is not supported by Magentomobileshop.')));
			exit;
		else:
			return true;
		endif;
		return true;
	}

	private function getSocialdetails($token,$sociallogintype){
		switch($sociallogintype) 
		{
			case 'facebook':
				$this->getfacebookdetails($token);
			break;
			case 'google':
				$this->getgoogledetails($token);
			break;
			default:
				echo json_encode(array('status'=>'error','message'=> $this->__('Social Login is not supported by Magentomobileshop.')));
				exit;
		}
	}

	private function getFacebookdetails($token){

		$user_details = $this->f_url.$token;
		$response = file_get_contents($user_details);
		$response = json_decode($response);
		if($response->email):
			$this->checkuser($response);
		else:
			echo json_encode(array('status'=>'error','message'=> $this->__('Token is invalid.')));
			exit;
		endif;
	}

	private function getgoogledetails($token){

		$user_details = $this->g_url.$token;
		$response = file_get_contents($user_details);
		$response = json_decode($response);

		if($response->email):
			$this->checkuser($response);
		else:
			echo json_encode(array('status'=>'error','message'=> $this->__('Token is invalid.')));
			exit;
		endif;

	}

	private function checkuser($response){
		try{
			$customer = Mage::getModel("customer/customer")->setWebsiteId(1)->loadByEmail($response->email);
	 		
	 		if($customer->getId()):
	 			Mage::getSingleton('customer/session')->loginById($customer->getId());
	 			echo json_encode(array('status'=>'success','message'=>Mss_Connector_CustomerController::statusAction()));
	 			exit;

	 		else:
	 			$this->registerUser($response);
	 		endif;
	 	}
	 	catch(exception $e){
	 		echo json_encode(array('status'=>'error','message'=> $this->__($e->getMessage())));
	 			exit;
	 	}

	}

	private function registerUser($response){
		$session = Mage::getSingleton ( 'customer/session' );
		$customer = Mage::getModel ( 'customer/customer' )->setId(null);

			$customer->setData('email',$response->email);
			$customer->setData('firstname',$response->first_name);
			$customer->setData('lastname',$response->last_name);
			$customer->setData('gender',$response->gender);
			$customer->setData('sociallogin_type',$this->sociallogin_type);
			$customer->setData('password',$this->radPassoword());

			try{
				$customer->setConfirmation(null);
				$customer->save(); 
				if ($customer->isConfirmationRequired ()):
					$customer->sendNewAccountEmail ( 'confirmation', $session->getBeforeAuthUrl (), Mage::app ()->getStore ()->getId () );
					echo json_encode(array('status'=>'error','message'=> $this->__('Account confirmation required.')));
	 				exit;
				else:
					$session->setCustomerAsLoggedIn($customer);
					$customer->sendNewAccountEmail ('registered','', Mage::app ()->getStore ()->getId ());
				endif;

				echo json_encode(array('status'=>'success','message'=>Mss_Connector_CustomerController::statusAction()));
	 			exit;
				
			}
			catch(Exception $ex){
				echo json_encode(array('status'=>'error','message'=> $this->__('Error in creating user account.')));
 				exit;
			}
		

	}

	private function radPassoword()
	{
		return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,6))),1,6);
	}

}