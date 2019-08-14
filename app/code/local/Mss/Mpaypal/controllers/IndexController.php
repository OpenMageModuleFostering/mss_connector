<?php
class Mss_Mpaypal_IndexController extends Mage_Core_Controller_Front_Action{

    public function _construct(){

     
      parent::_construct();
    
    }
/*
    public function IndexAction() {
      
	    $this->loadLayout();   
	    $this->renderLayout(); 
	  
    }
*/
    public function PaypalAction() {

        echo $block = $this->getLayout()->createBlock('core/template')->setTemplate('mpaypal/index.phtml')->toHtml();

    }

    public function successAction(){

      $result = $this->getRequest()->getParams();
     
      if($result):
        $orderIncrementId = $result['item_name'];

        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
        

        if($order->getId()):
                    $payment = $order->getPayment();
                  $payment->setTransactionId($result['txn_id'])
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

   /* public function validate_ipn($paypal_url, $postdata) 
    {
          $ipn_response;
          $log_ipn_results;
          // parse the paypal URL
          $url_parsed=parse_url($paypal_url);
          $post_string = '';

          foreach ($postdata as $field=>$value):
           $ipn_data["$field"] = $value;
           $post_string .= $field.'='.urlencode(stripslashes($value)).'&';
         endforeach;

          $post_string.="cmd=_notify-validate"; // append ipn command
          $fp = fsockopen("ssl://" . $url_parsed['host'],"443",$err_num,$err_str,30);
          if(!$fp) {
           $last_error = "fsockopen error no. $errnum: $errstr";
           return false;
          }
          else {
           fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n");
           fputs($fp, "Host: $url_parsed[host]\r\n");
           fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
           fputs($fp, "Content-length: ".strlen($post_string)."\r\n");
           fputs($fp, "Connection: close\r\n\r\n");
           fputs($fp, $post_string . "\r\n\r\n");
           while(!feof($fp)) {
            $ipn_response .= fgets($fp, 1024);
             }
           fclose($fp); // close connection
              }

          if (eregi("VERIFIED",$ipn_response)):
                return true;
          
          else
                return false;
          

    }*/


}
