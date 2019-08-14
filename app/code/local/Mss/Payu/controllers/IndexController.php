<?php
class Mss_Payu_IndexController extends Mage_Core_Controller_Front_Action{

    public function _construct(){

   
      parent::_construct();
    
    }

    public function IndexAction() {
      
	    $this->loadLayout();   
	    $this->renderLayout(); 
	  
    }

    public function payuAction() {

        echo $block = $this->getLayout()->createBlock('core/template')->setTemplate('payu/index.phtml')->toHtml();

    }

    public function successAction(){

      $result = $this->getRequest()->getParams();
      
      if($result):
        $orderIncrementId = $result['productinfo'];

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
        

        if($order->getId()):
                    $payment = $order->getPayment();
                  $payment->setTransactionId($result['txnid'])
                      ->setCurrencyCode()
                      ->setPreparedMessage("Payment Done")
                      ->setShouldCloseParentTransaction(true)
                      ->setIsTransactionClosed(1)
                      ->registerCaptureNotification();
    
        endif;

        $order->save();
        

        echo  $this->__("Thank You !"); 
      else:
        echo  $this->__("No data found");
      endif;

    }
      public function failureAction(){

      $result = $this->getRequest()->getParams();
      
      if($result):
        $orderIncrementId = $result['productinfo'];
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED);
        if($order->getId()):
            $payment = $order->getPayment();
            $payment->setTransactionId($result['txnid'])
              ->setCurrencyCode()
              ->setPreparedMessage("Payment Error")
              ->setShouldCloseParentTransaction(true)
              ->setIsTransactionClosed(1)
              ->registerCaptureNotification();

        endif;

        $order->save();

        echo  $this->__("Found Some Problem! Try Again"); 
      else:
        echo  $this->__("Found Some Problem! Try Again");
      endif;
     

    }
     public function testAction(){
      echo $block = $this->getLayout()->createBlock('core/template')->setTemplate('payu/test.phtml')->toHtml();

    }
}