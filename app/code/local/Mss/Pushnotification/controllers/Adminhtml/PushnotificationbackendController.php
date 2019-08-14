<?php
class Mss_Pushnotification_Adminhtml_PushnotificationbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		$this->_title($this->__("pushnotification"));
		$this->renderLayout();
	}
	public function notificationsendAction()
	{
		$params= $this->getRequest()->getParams();
		$result=Mage::helper('pushnotification')->sendnotification($params['message'],$params['notification_type']);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($result);
		return;
	}
}
