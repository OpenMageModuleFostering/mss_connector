<?php
class Mss_Connector_CustomerController extends Mage_Core_Controller_Front_Action {


	const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'customer/create_account/email_template';
	const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';
	const XML_PATH_REMIND_EMAIL_TEMPLATE = 'customer/password/remind_email_template';
	const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/forgot_email_template';
	const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';
	const XML_PATH_DEFAULT_EMAIL_DOMAIN         = 'customer/create_account/email_domain';
	const XML_PATH_IS_CONFIRM                   = 'customer/create_account/confirm';
	const XML_PATH_CONFIRM_EMAIL_TEMPLATE       = 'customer/create_account/email_confirmation_template';
	const XML_PATH_CONFIRMED_EMAIL_TEMPLATE     = 'customer/create_account/email_confirmed_template';
	const XML_PATH_GENERATE_HUMAN_FRIENDLY_ID   = 'customer/create_account/generate_human_friendly_id';

	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
		parent::_construct();
		
	}
	
	/*confirm Login API
	* Endpoint : baseurl/restapi/customer/loginStatus
	* Return : json
	* Return Parameters : status : true/false 
	*/

	public function loginStatusAction(){
		if (Mage::getSingleton ( 'customer/session' )->isLoggedIn()):
			echo json_encode(array('status'=>true));
			exit;
		else:
			echo  json_encode(array('status'=>false));
			exit;
		endif;
	}
	
	public function statusAction() {

		$customerinfo = array ();

		if (Mage::getSingleton ( 'customer/session' )->isLoggedIn()) {
			$customer = Mage::getSingleton ( 'customer/session' )->getCustomer ();
			$storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA); 
			
			$customerinfo = array (
					'id'=>$customer->getId(),
					'name' => $customer->getFirstname () .$customer->getLastname (),
					'email' => $customer->getEmail (),
					);
				
			return $customerinfo;
		} else
			
			return false;
	}


	public function loginAction() {

		if(Mage::app()->getRequest()->getParam('login_token') && Mage::app()->getRequest()->getParam ('sociallogintype'))
			Mage::helper('sociallogin')->socialloginRequest(Mage::app()->getRequest()->getParam('login_token'),Mage::app()->getRequest()->getParam('sociallogintype'));

			
		$session = Mage::getSingleton ( 'customer/session' );
		if (Mage::getSingleton ( 'customer/session' )->isLoggedIn ()) {
			$session->logout ();
		}
		$username = Mage::app ()->getRequest ()->getParam ( 'username' );
		 $password = Mage::app ()->getRequest ()->getParam ( 'password' );
		 
		try {
			if (!$session->login ( $username, $password )) {
				echo json_encode(array('status' => 'error','message'=>'wrong username or password.'));
				exit;
			} else {

				echo json_encode(array('status' => 'success','message'=>$this->statusAction ()));
				exit;
			}
		} catch ( Mage_Core_Exception $e ) {
			switch ($e->getCode ()) {
				case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED :
					$value = Mage::helper ( 'customer' )->getEmailConfirmationUrl ( $uname );
					$message = Mage::helper ( 'customer' )->__ ( 'This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value );
					echo json_encode ( array (
							'status' => 'error',
							'message' => $message 
					) );
					break;
				case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD :
					$message = $e->getMessage ();
					echo json_encode ( array (
							'status' => 'error',
							'message' => $message 
					) );
					break;
				default :
					$message = $e->getMessage ();
					echo json_encode ( array (
							'status' => 'error',
							'message' => $message 
					) );
			}
		}
	}


	public function registerAction() {
		$params = Mage::app ()->getRequest ()->getParams ();
		
		$session = Mage::getSingleton ( 'customer/session' );
		$session->setEscapeMessages ( true );
		
		$customer = Mage::registry ( 'current_customer' );
			header('content-type: application/json; charset=utf-8');
					header("access-control-allow-origin: *");
		$errors = array ();
		if (is_null ( $customer )) {
			$customer = Mage::getModel ( 'customer/customer' )->setId ( null );
		}
		if (isset ( $params ['isSubscribed'] )) {
			$customer->setIsSubscribed ( 1 );
		}
		if( (null==Mage::app ()->getRequest ()->getParam ('password') ) || (null==Mage::app ()->getRequest ()->getParam ('email')) ){
			echo json_encode ( array (
					false,
					'0x1100',
					'empty password or email.'
			) );
			return ;
		}
		$customer->getGroupId ();
		try {
			$customer->setPassword ( $params ['password'] );
			$customer->setConfirmation ( $this->getRequest ()->getPost ( 'confirmation', $params ['password'] ) );
			$customer->setData ( 'email', $params ['email'] );
			$customer->setData ( 'firstname', $params ['firstname'] );
			$customer->setData ( 'lastname', $params ['lastname'] );
			$customer->setData ( 'gender', $params ['gender'] );
			$customer->setData ( 'default_mobile_number', $params ['default_mobile_number'] );
			$validationResult = count ( $errors ) == 0;
			if (true === $validationResult) {
				$customer->save ();
				if ($customer->isConfirmationRequired ()) {
					$customer->sendNewAccountEmail ( 'confirmation', $session->getBeforeAuthUrl (), Mage::app ()->getStore ()->getId () );
				} else {
					$session->setCustomerAsLoggedIn ( $customer );
					$customer->sendNewAccountEmail ( 'registered', '', Mage::app ()->getStore ()->getId () );
				}
				
				$addressData = $session->getGuestAddress ();
				if ($addressData && $customer->getId ()) {
					$address = Mage::getModel ( 'customer/address' );
					$address->setData ( $addressData );
					$address->setCustomerId ( $customer->getId () );
					$address->save ();
					$session->unsGuestAddress ();
				}
				
				echo json_encode ( array (
						true,
						'0x0000',
						array () 
				) );
			} else {
				echo json_encode ( array (
						false,
						'0x1000',
						$errors 
				) );
			}
		} catch ( Mage_Core_Exception $e ) {
			if ($e->getCode () === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
				$url = Mage::getUrl ( 'customer/account/forgotpassword' );
				$message = $this->__ ( 'There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url );
				$session->setEscapeMessages ( false );
			} else {
				$message = $e->getMessage ();
			}
			echo json_encode ( array (
					false,
					'0x1000',
					array (
							$message 
					) 
			) );
		} catch ( Exception $e ) {
			echo json_encode ( array (
					false,
					'0x1000',
					$e->getMessage () 
			) );
		}
	}


	public function forgotpwdAction() {
		$email = Mage::app ()->getRequest ()->getParam ( 'email' );
		$session = Mage::getSingleton ( 'customer/session' );
		$customer = Mage::registry ( 'current_customer' );
		if (is_null ( $customer )) {
			$customer = Mage::getModel ( 'customer/customer' )->setId ( null );
		}
 		if ($this->_user_isexists ( $email )) {
			$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( Mage::app ()->getStore ()->getWebsiteId () )->loadByEmail ( $email );
			$this->_sendEmailTemplate ( $customer,self::XML_PATH_FORGOT_EMAIL_TEMPLATE, self::XML_PATH_FORGOT_EMAIL_IDENTITY, array (
					'customer' => $customer 
			), $storeId );
			echo json_encode ( array (
					'status' => 'error',
					'message' => 'Request has sent to your Email.'
			) );
		} else
			echo json_encode ( array (
					'status' => 'error',
					'message' => 'No matched email data.' 
			) );
	}
	public function logoutAction() {
		header('content-type: application/json; charset=utf-8');
					header("access-control-allow-origin: *");
		try {
			Mage::getSingleton ( 'customer/session' )->logout();
			echo json_encode(array(true, '0x0000', null));
		} catch (Exception $e) {
			echo json_encode(array(false, '0x1000', $e->getMessage()));
		}
	}
	protected function _user_isexists($email) {
		$info = array ();
		$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( Mage::app ()->getStore ()->getWebsiteId () )->loadByEmail ( $email );
		$info ['uname_is_exist'] = $customer->getId () > 0;
		$result = array (
				true,
				'0x0000',
				$info 
		);
		return $customer->getId () > 0;
	}


	protected function _sendEmailTemplate($customer,$template, $sender, $templateParams = array(), $storeId = null)
	{
		/** @var $mailer Mage_Core_Model_Email_Template_Mailer */
		$mailer = Mage::getModel('core/email_template_mailer');
		$emailInfo = Mage::getModel('core/email_info');
		$emailInfo->addTo($customer->getEmail(), $customer->getName());
		$mailer->addEmailInfo($emailInfo);
	
		// Set all required params and send emails
		$mailer->setSender(Mage::getStoreConfig($sender, $storeId));
		$mailer->setStoreId($storeId);
		$mailer->setTemplateId(Mage::getStoreConfig($template, $storeId));
		$mailer->setTemplateParams($templateParams);
		$mailer->send();
		return $this;
	}


	# set shipping Address and billing Address for customer
	public function setAddressAction()
 	{
		
 		try {
 			$userid = Mage::app ()->getRequest ()->getParam ( 'userid' );
 			//$session = Mage::getSingleton ( 'customer/session' );
			//if (Mage::getSingleton ( 'customer/session' )->isLoggedIn ()) {
 			if($userid){
				$customerId = $userid;
				$data=$this->getRequest()->getPost();
				
				
				if (!Zend_Validate::is($data['firstname'], 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=>'Firstname should not be empty'));
		    			exit;
				endif;
				if (!Zend_Validate::is($data['lastname'], 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=>'Lastname should not be empty'));
		    			exit;
				endif;
				if (!Zend_Validate::is($data['street'], 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=>'Street should not be empty'));
		    			exit;
				endif;
				if (!Zend_Validate::is($data['city'], 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=>'City should not be empty'));
		    			exit;
				endif;
				if (!Zend_Validate::is($data['country_id'], 'NotEmpty') || $data['country_id'] == 'undefined'):
					echo json_encode(array('status'=>'error','message'=>'Country_id should not be empty'));
		    			exit;
				endif;
				if (!Zend_Validate::is($data['region'], 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=>'Region should not be empty'));
		    			exit;
				endif;
				if (!Zend_Validate::is($data['postcode'], 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=>'Postcode should not be empty'));
		    			exit;
				endif;
				if (!Zend_Validate::is($data['telephone'], 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=>'Telephone should not be empty'));
		    			exit;
				endif;
				
				if($data['firstname']==null):
					echo json_encode ( array (
						'status' => 'error',
						'message' => 'please enter the firstname,'));
				endif;
					
				
				$addressData =  array (                   
                    'firstname' => $data['firstname'],                   
                    'lastname'=> $data['lastname'],                  
                    'street' => $data['street'],
                    'city' =>  $data['city'],
                    'country_id' =>  $data['country_id'],
                    'region' =>  $data['region'],                   
                    'postcode' =>  $data['postcode'],
                    'telephone' =>  $data['telephone'],
                    'fax' => @$data['fax'],
                    'is_default_billing' => '1',
                    'is_default_shipping' => '1',
                );

				$address = Mage::getModel("customer/address");
			    $address->addData($addressData);
				$address->setCustomerId($customerId);
				 
				try{
				    $address->save();
				    $result['id']=$address->getId();
				    $result['message']='Address added successfully.';
				    $result['status']='success';
						
				    echo json_encode($result);
				}
				catch (Exception $e) {
				    
				    echo json_encode ( array (
						'status' => 'error',
						'message' => $e->getMessage() 
				) );
				}

 
				
			}
			else{

				echo json_encode ( array (
						'status' => 'error',
						'message' => 'No matched email data.' 
				) );
				$session->logout(); 
			}
 		
 		} catch (Exception $e) {
 			echo $e->getMessage();
 		}

 		
 	}

 	public function getAddressbyidAction()
 	{
 		$id=(int)$this->getRequest()->getParam('addressid');
 		
 		
 			$address=Mage::getModel('customer/address')->load($id);
			
 			if($address->getId()):


 			$result=array(

				   		'id'=>$address->getId(),
				   		'firstname'=>$address->getFirstname(),
				   		'lastname'=>$address->getLastname(),
				   		'street'=>$address->getStreet1().''.$address->getStreet2(),
				   		'city'=>$address->getCity(),
				   		'country_id'=>Mage::getModel('directory/country')->loadByCode($address->getCountryId())->getName(),
				   		'region'=>$address->getRegion(),
				   		'postcode'=>$address->getPostcode(),
				   		'telephone'=>$address->getTelephone(),
				   		'fax'=>$address->getFax(),
				   		


				   );
 			 echo json_encode($result);

 		else:
				echo json_encode ( array (
										'code' => '0x0001',
										'message' => 'No matched email data.' 
								) );
 			endif;

 		
 	}

 	# get shipping Address listing of customer
 	public function getAddressAction()
 	{
 		
 		try {
 			$session = Mage::getSingleton ( 'customer/session' );
 			$userid = Mage::app ()->getRequest ()->getParam ( 'userid' );
 			
			
 			if($userid){
 				
				$customerId=$session->getId();

				$customer = Mage::getModel('customer/customer')->load($userid); //insert cust ID
				
				#create customer address array
				$customerAddress = array();
				#loop to create the array
				foreach ($customer->getAddresses() as $address)
				{
				   $customerAddress[] =array(

				   		'id'=>$address->getId(),
				   		'firstname'=>$address->getFirstname(),
				   		'lastname'=>$address->getLastname(),
				   		'street'=>$address->getStreet1().''.$address->getStreet2(),
				   		'city'=>$address->getCity(),
				   		// 'country_id'=>$address->getCountryId(),
				   			'country_id'=>Mage::getModel('directory/country')->loadByCode($address->getCountryId())->getName(),
				   		'region'=>$address->getRegion(),
				   		'postcode'=>$address->getPostcode(),
				   		'telephone'=>$address->getTelephone(),
				   		'fax'=>$address->getFax(),
				   		'email'=>$customer->getEmail(),


				   );
				}
				
				echo json_encode($customerAddress);
			}
			else{

				echo json_encode ( array (
						'code' => '0x0001',
						'message' => 'No matched email data.' 
				) );
				$session->logout(); 
			}
 		
 		} catch (Exception $e) {
 			
 			echo $e->getMessage();
 		}

 		
 	}


 	###Fetch all country and code
 	public function getcountriesAction()
 	{

	   $collection = Mage::getModel('directory/country')->getResourceCollection()
                            ->loadByStore()
                            ->toOptionArray(true); 

                

        $countriesArray=array();
        foreach ($collection as $country) 
        {
          
             if($country['value']):
           		$states = Mage::getModel('directory/country')->load($country['value'])->getRegions();

           	
           		if($states->getData()):
					$countriesArray[]=['value'=>$country['value'],'name'=>$country['label'],'state'=>$states->getData()];
				else:
					$countriesArray[]=['value'=>$country['value'],'name'=>$country['label'],'state'=>[]];
				endif;

				
			endif;
                                   
        }

        echo json_encode($countriesArray);


 	}

 	/*register device */

 	public function registerdeviceAction()
 	{
 		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");

		$data = $this->getRequest()->getParams();
		
		if($data):
		
			Mage::helper('pushnotification')->registerdevice($data);
			echo json_encode(array('status'=>'success','message'=>'successfully registered.'));
			exit;
		else:
			echo json_encode(array('status'=>'error','message'=>'Error in data format.'));
		endif;
 		
 	}

 	# Start Get My Orders listing 
 	 /***Convert Currency***/
	public function convert_currency($price,$from,$to) {
			$newPrice = Mage::helper('directory')->currencyConvert($price, $from, $to);
			return $newPrice;
	} 


 	public function getMyOrdersAction()
 	{


 		$session = Mage::getSingleton ( 'customer/session' );
 		if (Mage::getSingleton ( 'customer/session' )->isLoggedIn ()) {
			 $cust_id=$session->getId();

			 $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
			 $res = array();
	           $totorders = Mage::getResourceModel('sales/order_collection')
                     ->addFieldToSelect('*')
                     ->addFieldToFilter('customer_id', $cust_id);
             $res["total"] = count($totorders);
           $orders = Mage::getResourceModel('sales/order_collection')
                     ->addFieldToSelect('*')
                     ->addFieldToFilter('customer_id', $cust_id)
                     ->setOrder('created_at', 'desc')
                       ->setPage($curr_page,$page_size);
                    //$this->setOrders($orders); 
		 	# start order  loop                      
           foreach ($orders as $order) {
          
                $shippingAddress = $order->getShippingAddress();
                if(is_object($shippingAddress)) {
					$shippadd = array();
					$flag = 0;
					if(count($orderData)>0)
					$flag = 1;
					$shippadd = array(
						 "firstname" => $shippingAddress->getFirstname(),
						 "lastname" => $shippingAddress->getLastname(),
						 "company" => $shippingAddress->getCompany(),
						 "street" => $shippingAddress->getStreetFull(),
						 "region" => $shippingAddress->getRegion(),
						 "city" => $shippingAddress->getCity(),
						 "pincode" => $shippingAddress->getPostcode(),
						 "countryid" => $shippingAddress->getCountry_id(),
						 "contactno" => $shippingAddress->getTelephone(),
						 "shipmyid" => $flag
					); 
                }
                $billingAddress = $order->getBillingAddress();
                if(is_object($billingAddress)) {
					$billadd = array();
					$billadd = array(
						 "firstname" => $billingAddress->getFirstname(),
						 "lastname" => $billingAddress->getLastname(),
						 "company" => $billingAddress->getCompany(),
						 "street" => $billingAddress->getStreetFull(),
						 "region" => $billingAddress->getRegion(),
						 "city" => $billingAddress->getCity(),
						 "pincode" => $billingAddress->getPostcode(),
						 "countryid" => $billingAddress->getCountry_id(),
						 "contactno" => $billingAddress->getTelephone()
					);
                }
                $payment = array();
                $payment = $order->getPayment();
                


			try {
	            $payment_result = array (
	                      "payment_method_title" => $payment->getMethodInstance()->getTitle(),
	                      "payment_method_code" => $payment->getMethodInstance()->getCode(),
	            );
	            if($payment->getMethodInstance()->getCode()=="banktransfer") {

				$payment_result["payment_method_description"] = $payment->getMethodInstance()->getInstructions();
				}
	                }
	        catch(Exception $ex2) {

	                        }

                $items = $order->getAllItems(); 
                $itemcount=count($items);
                $name=array();
                $unitPrice=array();
                $sku=array();
                $ids=array();
                $qty=array();
                $images = array();
                 $test_p  = array();
                 $itemsExcludingConfigurables = array();
                 $productlist = array();
                   foreach ($items as $itemId => $item) {
                     $name= $item->getName();
                     //echo $item->getName();
                     if($item->getOriginalPrice() > 0) {
                     	$unitPrice =  number_format($this->convert_currency(floatval($item->getOriginalPrice()),$basecurrencycode,$currency), 2, '.', '');
                     }
                     else {
                     	$unitPrice =   number_format($this->convert_currency(floatval($item->getPrice()),$basecurrencycode,$currency), 2, '.', '');
                     }
                     
                     $sku=$item->getSku();
                     $ids=$item->getProductId();
                     //$qty[]=$item->getQtyToInvoice();
                     $qty= $item->getQtyOrdered();
                     $products = Mage::getModel('catalog/product')->load($item->getProductId());
                     $images= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/media/catalog/product'.$products->getThumbnail();
               		 
               		 $productlist[] = array (
		                     "name" => $name,
		                     "sku" => $sku,
		                     "id" => $ids,
		                     "quantity" => $qty,
		                     "unitprice" => $unitPrice,
		                     "image" => $images,
		                     "total_item_count" => $itemcount,
		                     "price_org" =>  $test_p,
		                     "price_based_curr" => 1,
		                );

                }  # item foreach close
               
              
               
                $order_date = $order->getCreatedAtStoreDate().'';
                $orderData = array(
                     "id" => $order->getId(),
                     "order_id" => $order->getRealOrderId(),
                     "status" => $order->getStatus(),
                     "order_date" => $order_date,
                     "grand_total" => number_format($this->convert_currency(floatval($order->getGrandTotal()),$basecurrencycode,$currency), 2, '.', ''),
                     "shipping_address" => $shippadd,
                     "billing_address" => $billadd,
                     "shipping_message" => $order->getShippingDescription(),
                     "shipping_amount" => number_format($this->convert_currency(floatval($order->getShippingAmount()),$basecurrencycode,$currency), 2, '.', ''),
                     "payment_method" => $payment_result,
                     "tax_amount" => number_format($this->convert_currency(floatval($order->getTaxAmount()),$basecurrencycode,$currency), 2, '.', ''),
                     "products" => $productlist,
                     "order_currency" => $order->getOrderCurrencyCode(),
                     "order_currency_symbol" => Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol(),
                     "currency" => $currency,
                     "couponUsed" => 0
                );
                $couponCode = $order->getCouponCode();
                if($couponCode!="") {					
					$orderData["couponUsed"] =  1;
					$orderData["couponCode"] =  $couponCode;
				    $orderData["discount_amount"] =  floatval(number_format($this->convert_currency(floatval($order->getDiscountAmount()),$basecurrencycode,$currency), 2, '.', ''))*-1;
				}
				
				$res["data"][] = $orderData;
           } # end foreach
          echo json_encode($res);



		} 
		else{

				echo json_encode(array('status'=>'error','message'=>'Please Login to see the Orders'));

		}

 	} # end my orders
 	

	 /*
		URL : baseurl/restapi/customer/getuserinfo/
		Controller : customer
		Action : getuserinfo
		Method : POST
		Request Parameters : 
		Parameter Type :
		Response : JSON
	 
	 */
		public function getuserinfoAction(){
			
			if(Mage::getSingleton('customer/session')->isLoggedIn()):		 
			    $info=array();
			    $customer = Mage::getSingleton('customer/session')->getCustomer();			   
			    $info['firstname'] =  $customer->getFirstname(); 		  
			    $info['lastname'] = $customer->getLastname();
			    $customerAddressId =$customer->getDefaultBilling(); 
			  
				   if ($customerAddressId):
					    $address = Mage::getModel('customer/address')->load($customerAddressId);
					   
	                   if(sizeof( $address)){				 
						    $info['postcode'] = $address->getPostcode();
						    $info['city'] = $address->getCity();
						    $street = $address->getStreet();
						    $info['street'] = $street[0];
						    $info['telephone'] = $address->getTelephone();
						    $info['fax'] = $address->getFax();
						    $info['country'] = $address->getCountry();
						    $info['region'] = $address->getRegion();
					 	}	
			  
					echo json_encode(array('status' => 'success','data'=>$info));
					exit;
				else:
						
					echo json_encode(array('status' => 'success','data'=>$info));
					exit;
				endif;

		else:
			echo json_encode(array('status' => 'error','message'=>'Login First.'));
			exit;

		endif;
	}

		/*
		URL : baseurl/restapi/customer/setuserinfo/
		Controller : customer
		Action : setuserinfo
		Method : POST
		Request Parameters :Data* 
				Dummy Data:  {
							    "firstname": "abc",
							    "lastname": "def",
							    "postcode": "123456",
							    "city": "delhi",
							    "street": "chandigarh",
							    "telephone": "9888898888",
							    "fax": null,
							    "country": "IN",
							    "region": null
							  } 
		Parameter Type:
		Response : JSON
		 */

	public function setuserinfoAction(){

		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getModel('customer/customer')
						->load(Mage::getSingleton('customer/session')->getId());
			$data = Mage::app()->getRequest ()->getParam ('data');

			
			$customer_info = json_decode($data, true);		
		    if(isset($customer_info)){ 		            
		         	
		            
		            $customer->setFirstname($customer_info['firstname']);
		         	$customer->setLastname($customer_info['lastname']);

		         	$address = $customer->getPrimaryBillingAddress();	         

		            $address->setFirstname($customer_info['firstname']);
		         	$address->setLastname($customer_info['lastname']);		         	
		          	$address->setPhoneno($customer_info['telephone']);
		            $address->setCity($customer_info['city']);
		            $address->setState($customer_info['region']);
		            $address->setCountry($customer_info['country']);
		            $address->setPostcode($customer_info['postcode']);
				try{
					$address->save();
				    $customer->save();
				    echo json_encode(array('status' => 'success','message'=>'Data Updated successfully'));	
				}
				catch(exception $e){
					echo json_encode(array('status' => 'error','message'=>'Data Not Updated'));				  
				}
	        }else{
	        		echo json_encode(array('status' => 'error','message'=>'Data Not Updated'));
	     	}
		} 
		else {
			echo json_encode(array('status' => 'error','message'=>'Login First.'));
		}

	}

} 
