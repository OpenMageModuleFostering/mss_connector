<?php
class Mss_Connector_Adminhtml_SupportController extends Mage_Adminhtml_Controller_Action
{
   
	public function supportAction()
	{		
		$this->loadLayout();
		$this->_title($this->__("MagentoMobileShop Support"));
		$this->renderLayout();
	}

	public function supportmailAction()
	{		    		
		$to_email = "contact@magentomobileshop.com";               
		$subject = $this->getRequest()->getParam("subject");
		$Body=$this->getRequest()->getParam("message");
		$sender_email = $this->getRequest()->getParam("email"); 
		$mail = new Zend_Mail(); 
		$mail->setBodyHtml($Body);
		$mail->setFrom($sender_email);
		$mail->addTo($to_email);
		$mail->setSubject($subject);
		$msg  = '';
		try {
		      if ($mail->send())
		         $msg = 'Request successfully sent.';
		      
		} catch (Exception $e) {
			$msg = $e->getMessage();                        
		}
		return  $msg;
	}
  
	public function landingAction() {    
  	
  	    $this->loadLayout();
  	    $this->_title($this->__("Verification"));
		$this->renderLayout();
  	}
}

