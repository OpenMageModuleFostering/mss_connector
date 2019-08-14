<?php
class Mss_Connector_StoreinfoController extends Mage_Core_Controller_Front_Action {

	const MSS_STORE_EMAIL = 'mss/mss_info_group/store_email';
	const MSS_STORE_PHONENO = 'mss/mss_info_group/store_phoneno';
	const XML_DEFAULT_STORE_LANG ='general/locale/code';

	
	public $storeId = "1";
	public $viewId = "";
	public $currency = "";

	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));

		$this->storeId = Mage::app()->getFrontController()->getRequest()->getHeader('storeId');
		$this->viewId = Mage::app()->getFrontController()->getRequest()->getHeader('viewId');
		$this->currency = Mage::app()->getFrontController()->getRequest()->getHeader('currency');
		Mage::app()->setCurrentStore($this->storeId);
		
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
					//$result['store_phoneno'] = $store_phone;
					$result['store_phoneno'] = str_replace('-', '', $store_phone);
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

				echo json_encode(array('status'=>'error','message'=>$this->__('Problem in loading data.')));
				exit;
		}
	
	}	

	/*

		
		Working url : baseURL/restapi/storeinfo/getstoredata/
		URL : baseurl/restapi/storeinfo/getstoredata/
		Name : getstoreinfo
		Method : GET
		Response : JSON
		Return Response :
		
	*/

		public function getstoredataAction() {
		
		$basicinfo = array ();
		$website_id = Mage::app()->getStore()->getWebsiteId();
		$website = Mage::app ()->getWebsite($website_id);

		foreach($website->getGroups() as $key=> $group):

			$stores = $group->getStores();
			
			foreach ( $stores as $key =>$view)
				$store_view[]= [
						'name' => $view->getName(),
						'view_id' => $view->getStoreId(),
						'store_url' => $view->getUrl(),
						'store_code'=> Mage::getStoreConfig(self::XML_DEFAULT_STORE_LANG, $view->getStoreId()),
						'store_name'=> $view->getName(),
						'sort_order' => $view->getSortOrder(),
						'is_active' => $view->getIsActive()
				];

			$basicinfo[]=[
						'store' => $group->getName(),
						'store_id' => $group->getGroupId(),
						'root_category_id' => $group->getRootCategoryId(),
						'view'=>$store_view
						];

			$store_view ='';

		endforeach;
	echo json_encode($basicinfo);
		
	}

	/*

		
		Working url : baseURL/restapi/storeinfo/getstoredata/
		URL : baseurl/restapi/storeinfo/getstoredata/
		Name : getstoreinfo
		Method : GET
		Response : JSON
		Return Response :

		
	*/

	public function getCurrentCurrencyAction() {
		
		$codes = Mage::app()->getStore()->getAvailableCurrencyCodes(true);
		$currencies = array();

            if (is_array($codes) && count($codes) > 1):
                $rates = Mage::getModel('directory/currency')->getCurrencyRates(
                    Mage::app()->getStore()->getBaseCurrency(),
                    $codes
                );
       


                foreach ($codes as $code):
                    if(isset($rates[$code]))
                        $currencies[] =['name'=> Mage::app()->getLocale()
                            ->getTranslation($code, 'nametocurrency'),'code'=>$code,
                            'symbol'=>Mage::app()->getLocale()->currency($code)->getSymbol()];
                        
                    
                endforeach;
            endif;
       
		echo json_encode($currencies);
		
	}

	/*

		
		Working url : baseURL/restapi/storeinfo/storelocator/
		URL : baseurl/restapi/storeinfo/storelocator/
		Name : getstoreinfo
		Method : GET
		Response : JSON
		Return Response :
		
	*/

	public function storelocatorAction(){

		$Info = array('phone','name','hours','address','longitude','latitude');

		$website_id = Mage::app()->getStore()->getWebsiteId();
		$website = Mage::app ()->getWebsite($website_id);
		$storedata = '';
		foreach($website->getGroups() as $key=> $group):

			$stores = $group->getStores();

			$store_Id = '';
			$view_Id = '';
			foreach ( $stores as $key =>$view):
				$store_Id = $view->getStoreId();
				if($view->getStoreId()):
					$v_stores = $group->getStores();
					foreach ( $v_stores as $v_key =>$v_view):
							$view_Id = $v_view->getStoreId();
							break;
					endforeach;
					break;
				endif;
			endforeach;


			foreach($Info as $detail)
				$storedetails[$detail] = Mage::getStoreConfig('general/store_information/'.$detail,$store_Id); 

			$storedetails['store_id'] = $store_Id;
			$storedetails['view_id'] = $view_Id;
			$storedetails['store_code'] = Mage::app()->getStore($store_Id)->getCode();
			$storedata[] = $storedetails;
		endforeach;
		echo json_encode($storedata);
	
	}

	/*

		
		Working url : baseURL/restapi/storeinfo/clearCaches/
		URL : baseurl/restapi/storeinfo/clearCaches/
		Name : getstoreinfo
		Method : GET
		Response : JSON
		Return Response :
		
	*/

	public function clearCachesAction(){
		Mage::app()->getCache()->remove("mss_dashboard_store1");
		Mage::app()->getCache()->remove("mss_menu_store1");

		/*change store currency*/
		$parameter = $this->getRequest ()->getParam ('parameter');
		if( isset($parameter) && $parameter == 'currency') {

    		try {
	    		
	          	Mage::app()->getStore()->setCurrentCurrencyCode($this->currency);
		      
		        if (Mage::getSingleton('checkout/session')->getQuote()) {
		            Mage::getSingleton('checkout/session')->getQuote()
		                ->collectTotals()
		                ->save();
		        }	
		    	echo json_encode(array('status'=>'success','message'=>'Currency changed.'));
		    	exit;
	    	} catch (Exception $e){
	    		echo json_encode(array('status'=>'error','message'=>$e->getMessage()));
	    		exit;
	    	}
    	}

    	echo json_encode(array('status'=>'success'));
		
	}
}	
