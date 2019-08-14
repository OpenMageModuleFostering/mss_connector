<?php
class Mss_Connector_StaticpagesController extends Mage_Core_Controller_Front_Action {
	
      public function _construct(){
        header('content-type: application/json; charset=utf-8');
        header("access-control-allow-origin: *");
        Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
        parent::_construct();
            }
    public function getPagesAction()
    {    
        try{
            $data = array();
            $helper = Mage::helper('cms');
            $processor = $helper->getPageTemplateProcessor();
            $identifier = Mage::getStoreConfig('mss/mss_config_group/about_us_page');
            $pages =    explode(',', $identifier);
            foreach($pages as $page):
                 if($page):
                    $page_model = Mage::getModel('cms/page')->load($page, 'identifier');
                    $data [] = array('page_title'=>$page_model->getTitle(),
                                'page_content'=> preg_replace('/<\/?a[^>]*>/','',$processor->filter($page_model->getContent())),
                                'identifier'=>$page_model->getIdentifier());
                endif;
            endforeach;
             if(sizeof($data)):
                echo json_encode(array('status'=>'success','count'=>COUNT($data),'data'=>$data));
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