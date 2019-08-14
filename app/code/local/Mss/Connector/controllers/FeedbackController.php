<?php
class Mss_Connector_FeedbackController extends Mage_Core_Controller_Front_Action {

	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
		parent::_construct();
		
	}


	public function sendFeedbackAction()
	{
		
			$email = Mage::app()->getRequest()->getParams('email');
			$message = Mage::app()->getRequest()->getParams('message');

			if($email):
				  $result['message']= $this->__('Thanks for your valuable feedback.');
				    $result['status']='success';
						
						 echo json_encode($result);
						 exit;
			endif;


		 echo json_encode ( array (
						'status' => 'error',
						'message' => $this->__('please enter the email !!' )
				) );

	}


}
