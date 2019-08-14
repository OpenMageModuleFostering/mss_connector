<?php

class Mss_Mpaypal_Model_Observer {
 
    public function paymentMethodIsActive(Varien_Event_Observer $observer) {
        $event           = $observer->getEvent();
        $method          = $event->getMethodInstance();
        $result          = $event->getResult();
        
        $quote=Mage::getSingleton ( 'checkout/session' )->getQuote();
        if($method->getCode() == 'mpaypal'):
              if($quote->getMms_order_type() == 'app')
              	$result->isAvailable = true;
              else
              	$result->isAvailable = false;
        	
        endif;    
        return true;
    }
 
}