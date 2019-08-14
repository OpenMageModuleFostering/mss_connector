<?php

class Mss_Bannerslider_BannerController extends Mage_Core_Controller_Front_Action {

	private $key = 'bannerslider';
	private $store = '1';

	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
		parent::_construct();
		
	}
	
	public function bannerAction(){
		$bannerCollection = Mage::getModel('bannerslider/bannerslider')->getCollection();
		$bannerCollection->addFieldToFilter( 'status', '1' );

			/*check for cache*/
			Mage::helper('connector')->checkcache($this->key,$this->store);
			
			$alldata = array(); 
			foreach($bannerCollection->getData() as $bannerdata){

				 				
					$path = Mage::helper('bannerslider')->reImageName( $bannerdata['image']);

					$imgeurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'media/bannerslider/'.$path;
					
					$data['name'] = $bannerdata['name'];
					
					$data['image_description'] = $bannerdata['image_alt'];
					$data['image_url'] = $imgeurl;
					$data['category_name'] = Mage::getModel('catalog/category')->load($bannerdata['category_id'])->getName();
					$data['link_type'] = $bannerdata['url_type'];
					$data['product_id'] = $bannerdata['product_id'];
					$data['category_id'] = $bannerdata['category_id'];
						
                    $alldata[] = $data; 
			
			}
			if(Mage::getStoreConfig('mss/connector/email') != '1'):
			    $event_data_array  =  array(true);
				Mage::dispatchEvent('send_email_store_data', $event_data_array);
			endif;

			if(sizeof($alldata)):
				/*create new cache*/
				Mage::helper('connector')->createNewcache($this->key,$this->store,json_encode(array('status'=>'success','data'=>$alldata)));
				echo json_encode(array('status'=>'success','data'=>$alldata));
				exit;
			else:
				echo json_encode(array('status'=>'error','message'=> $this->__('No banner uploaded')));
			endif;
	}
}

?>