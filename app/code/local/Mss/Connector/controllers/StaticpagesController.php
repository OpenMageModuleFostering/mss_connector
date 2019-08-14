<?php
class Mss_Connector_StaticpagesController extends Mage_Core_Controller_Front_Action {

	
	const MSS_ABOUT_US_PAGE = 'mss/mss_config_group/about_us_page';
	const MSS_TERM_CONDITION_PAGE = 'mss/mss_config_group/term_condition_page';
	const MSS_PRIVACY_POLICY_PAGE = 'mss/mss_config_group/privacy_policy_page';
	const MSS_RETURN_PRIVACY_POLICY_PAGE = 'mss/mss_config_group/return_privacy_policy_page';

	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
		parent::_construct();
		
	}
	

	/*
		URL : baseurl/restapi/staticpages/getPages/
		Name : getCmspages
		Method : GET
		Response : JSON
		Return Response : {
				  "status": "success",
				  "data": [
					    {
					      "page_title": "About Us",
					      "page_content": "content"
					    },
				    ]
				}
	*/
	public function getPagesAction()
	{	
		echo json_encode(array('status'=>'success','data'=>array()));
		exit;
		try{
			$pages = array(
							
							self::MSS_ABOUT_US_PAGE,
							self::MSS_TERM_CONDITION_PAGE,
							self::MSS_PRIVACY_POLICY_PAGE,
							self::MSS_RETURN_PRIVACY_POLICY_PAGE
							);
			$data = array();

			foreach($pages as $page):

				if($page):
					$identifier = Mage::getStoreConfig($page);
					$page_model = Mage::getModel('cms/page')->load($identifier, 'identifier');
					
					$data [] = array('page_title'=>$page_model->getTitle(),
								'page_content'=>$page_model->getContent());
				endif;
			endforeach;
			
			if(sizeof($data)):
				echo json_encode(array('status'=>'success','data'=>$data));
				exit;
			else:
				echo json_encode(array('status'=>'error','message'=> $this->__('No page configured, please configure page first')));
				exit;
			endif;
		}
		catch(exception $e){

			echo json_encode(array('status'=>'error','message'=> $this->__('Problem in loading data.')));
			exit;

		}


	}

}