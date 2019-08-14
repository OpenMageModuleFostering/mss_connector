<?php
class Mss_Connector_Helper_Data extends Mage_Core_Helper_Abstract
{

	/*resize image according to size*/

	public function Imageresize($_file_name,$type,$width,$height){
           try{

			    $_media_dir = Mage::getBaseDir('media') . DS . 'catalog' . DS . $type ;
			    $cache_dir =  $_media_dir . DS. 'cache' . DS;
			   	

			    if (file_exists($cache_dir . $_file_name)):
			        $real_path =  Mage::getBaseUrl('media')  . 'catalog' . DS . $type . DS . 'cache' .  $_file_name;
			   // elseif (file_exists($_media_dir . $_file_name)):
			    else:
			    	 if (!is_dir(Mage::getBaseDir('media') . DS . 'catalog' . DS . $type))
			          mkdir(Mage::getBaseDir('media') . DS . 'catalog' . DS . $type,0777);

			        if (!is_dir($cache_dir))
			            mkdir($cache_dir,0777);
			    endif;  
			  	
			    if($type == 'thumbnail'):
			    	$_media_dir =  Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' ;
			    endif;

			  if($type == 'product_main'):
			    	$_media_dir =  Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' ;
			    endif;

		        $_image = new Varien_Image($_media_dir . $_file_name);

		        $_image->constrainOnly(true);
				$_image->keepAspectRatio(true);
				$_image->backgroundColor(array(255,255,255));
				$_image->keepFrame(true);
		        $_image->resize($width, $height);
		        $_image->save($cache_dir . $_file_name);

		        return Mage::getBaseUrl('media') . 'catalog' . DS . $type . DS . 'cache' . $_file_name;
		    }
		    catch(exception $e){
		    	
		    	return Mage::getBaseUrl('media') . 'catalog' . DS . $type .$_file_name;
		    }		   		
	    }

	    /*check cache according to request*/

	    public function checkcache($key,$store = 1){
	    	try{	    	
		    	$cache = Mage::app()->getCache();
		        $cache_key = "mss_".$key."_store".$store;
				
				if($cache->load($cache_key)):
					
			        echo $cache->load($cache_key);
			    	exit;
			    endif;
			    return false;
			}
			catch(exception $e){
				return false;
			}
		    
												
	    }

	    /*create cache according to request*/
	    public function createNewcache($key,$store = 1,$data){

	    	try{
				$CACHE_EXPIRY = 300;
				$cache = Mage::app()->getCache();
				$cache_key = "mss_".$key."_store".$store;
				
				$cache->save($data, $cache_key, array("mss"), $CACHE_EXPIRY); 
				return true;
			}
			catch(exception $e){
				return false;
			}
	    }

	    public function loadParent($helper){
	        	
			if(Mage::helper('connector')->compareExp() > 4800):
				echo	json_encode(array('status'=>'error','code'=>'001'));
				exit;			
			endif;

			if(Mage::getStoreConfig('magentomobileshop/secure/token') != $helper):
			    echo  json_encode(array('status'=>'error','code'=>'002'));
				exit;
			endif;
			if(!Mage::getStoreConfig('magentomobileshop/key/status')):
			    echo  json_encode(array('status'=>'error','code'=>'003')); 
				exit;	
	    	endif;

	    	if(Mage::helper('connector')->compareExp() > 4800 || 
	    		Mage::getStoreConfig('magentomobileshop/secure/token') != isset($helper)
	    		|| !Mage::getStoreConfig('magentomobileshop/key/status') || !$helper):
	    				echo	json_encode(array('status'=>'error','code'=>'004'));
	    				exit;
	    		endif;
	    }
		

	    public function compareExp(){
			$saved_session = strtotime(Mage::getStoreConfig('secure/token/exp'));
			$current_session = strtotime(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
			return round(($current_session - $saved_session) / 3600);
		}

		// Functionality to check product is in wishlist or not
		
		public function check_wishlist($productId){

			$customer =  Mage::getSingleton("customer/session");

			if($customer->isLoggedIn()):

			    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer->getId(), true);
			    $wishListItemCollection = $wishlist->getItemCollection();
			    $wishlist_product_id = array();
			    foreach ($wishListItemCollection as $item){   

			         $wishlist_product_id[]=   $item->getProductId();
			     }
				if(in_array($productId,  $wishlist_product_id))
					return true;
				else
					return false; 
				
			else:
				return false;
			endif;
		}

		public function saveCc($data){
			
			$data['user_id'] =  Mage::getSingleton("customer/session")->getId();
			
			$collection =  Mage::getModel("connector/connector");
			try{
					$collection->setData($data)->save();
					return true;
				}
			catch(Exception $e){
				Mage::throwException(Mage::helper('payment')->__('Error in saving card.'));
			}
							
			return true;		
		}

		public function reigesterGuestUser($userdata){


			$customer = Mage::getModel("customer/customer"); 
			$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
 			$load_pre = $customer->loadByEmail($userdata['email']);

 			if($load_pre->getId())
 				return $load_pre->getId();

			$customer = $customer->setId(null);

			$customer->setData('email',$userdata['email']);
			$customer->setData('firstname',$userdata['firstname']);
			$customer->setData('lastname',$userdata['lastname']);
			$customer->setData('password',$this->radPassoword());

			try{
				$customer->setConfirmation(null);
				$customer->save(); 
				$customer->sendNewAccountEmail ('registered','', Mage::app ()->getStore ()->getId ());
	 			return $customer->getId();
				
			}
			catch(Exception $ex){
				echo json_encode(array('status'=>'error','message'=> $this->__($ex->getMessage())));
 				exit;
			}

		}

		private function radPassoword()
		{
			return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1,6))),1,6);
		}

		public function getProductStockInfoById($productId){

				$stock_product = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
         		$stock_data = $stock_product->getIsInStock();
         		return $stock_data;
		}

		public function getCurrencysymbolByCode($code){

				return Mage::app()->getLocale()->currency($code)->getSymbol()?:$code;
		}

		public function getSpecialPriceByProductId($productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
		    $specialprice = $product->getSpecialPrice(); 
		    $specialPriceFromDate = $product->getSpecialFromDate();
		    $specialPriceToDate = $product->getSpecialToDate();
		    
		    $today =  time();
		 
		    if ($specialprice):
		        if($today >= strtotime( $specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime( $specialPriceFromDate) && is_null($specialPriceToDate))
		        		return $specialprice;
		        else return '0.00';
		    else:
		    	return '0.00';
		   	endif;


		}

		public function getFinalPriceByProductId($productId)
		{
			$product = Mage::getModel('catalog/product')->load($productId);
		    $specialprice = $product->getSpecialPrice(); 
		    $specialPriceFromDate = $product->getSpecialFromDate();
		    $specialPriceToDate = $product->getSpecialToDate();
		    
		    $today =  time();
		 
		    if ($specialprice):
		        if($today >= strtotime( $specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime( $specialPriceFromDate) && is_null($specialPriceToDate))
		        		return $specialprice;
		        else return '';
		    else:
		    	return '';
		   	endif;


		}
}
