<?php
class Mss_Connector_WishlistController extends Mage_Core_Controller_Front_Action {

	public $storeId = "1";
	public $viewId = "";
	public $currency = "";
	
	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));

		$this->storeId = Mage::app()->getFrontController()->getRequest()->getHeader('storeId');
		$this->viewId = Mage::app()->getFrontController()->getRequest()->getHeader('viewId');
		$this->currency = Mage::app()->getFrontController()->getRequest()->getHeader('currency');
		Mage::app()->setCurrentStore($this->storeId);
		/*Mage::app()->getStore($this->storeId)->setCurrentCurrency($this->currency);*/

		parent::_construct();
		
	}
	
	public function addToWishlistAction() {
		$response = array ();
		if (! Mage::getStoreConfigFlag ( 'wishlist/general/active' )) {
			$response ['status'] = 'error';
			$response ['message'] = $this->__ ( 'Wishlist Has Been Disabled By Admin' );
		}
		if (! Mage::getSingleton ( 'customer/session' )->isLoggedIn ()) {
			$response ['status'] = 'error';
			$response ['message'] = $this->__ ( 'Please Login First' );
		}
		
		if (empty ( $response )) {
			

			$session = Mage::getSingleton ( 'customer/session' );
			$customer=Mage::getModel("customer/customer")->load($session->getCustomer()->getId());
			$wishlist=Mage::getModel("wishlist/wishlist")->loadByCustomer($customer,true);

			if (! $wishlist) {
				$response ['status'] = 'error';
				$response ['message'] = $this->__ ( 'Unable to Create Wishlist' );
			} else {
				
				$productId = ( int ) $this->getRequest ()->getParam ( 'product' );
				if (! $productId) {
					$response ['status'] = 'error';
					$response ['message'] = $this->__ ( 'Product Not Found' );
				} else {
					
					$product = Mage::getModel ( 'catalog/product' )->load ( $productId );
					if (! $product->getId () || ! $product->isVisibleInCatalog ()) {
						$response ['status'] = 'error';
						$response ['message'] = $this->__ ( 'Cannot specify product.' );
					} else {
						
						try {
							//$requestParams = $this->getRequest ()->getParams ();
							$requestParams = array();
							$buyRequest = new Varien_Object ( $requestParams );
							
							$result = $wishlist->addNewItem ( $product, $buyRequest );
							if (is_string ( $result )) {
								Mage::throwException ( $result );
							}
							$wishlist->save ();
							
							Mage::dispatchEvent ( 'wishlist_add_product', array (
									'wishlist' => $wishlist,
									'product' => $product,
									'item' => $result 
							) );
							
							Mage::helper ( 'wishlist' )->calculate ();
							
							$message = $this->__ ( '%1$s has been added to your wishlist.', $product->getName (), $referer );
							$response ['status'] = 'success';
							$response ['message'] = $message;
							
							Mage::unregister ( 'wishlist' );
						} catch ( Mage_Core_Exception $e ) {
							$response ['status'] = 'error';
							$response ['message'] = $this->__ ( 'An error occurred while adding item to wishlist: %s', $e->getMessage () );
						} catch ( Exception $e ) {
							
							$response ['status'] = 'error';
							$response ['message'] = $this->__ ( 'An error occurred while adding item to wishlist.' );
						}
					}
				}
			}
		}
		
		echo json_encode ( $response );
		return;
	}

	public function getWishlistAction() {
		echo json_encode ( $this->_getWishlist () );
	}


	protected function _getWishlist() {
		$wishlist = Mage::registry ( 'wishlist' );
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		$currentCurrency = $this->currency;
		if ($wishlist) {
			return $wishlist;
		}
		
		try {
			$wishlist = Mage::getModel ( 'wishlist/wishlist' )->loadByCustomer ( Mage::getSingleton ( 'customer/session' )->getCustomer (), true );
			Mage::register ( 'wishlist', $wishlist );
		} catch ( Mage_Core_Exception $e ) {
			Mage::getSingleton ( 'wishlist/session' )->addError ( $this->__($e->getMessage () ));
		} catch ( Exception $e ) {
			Mage::getSingleton ( 'wishlist/session' )->addException ( $e, Mage::helper ( 'wishlist' )->__ ( 'Cannot create wishlist.' ) );
			return array();
		}
		$items = array ();
		foreach ( $wishlist->getItemCollection () as $item ) {
			$item = Mage::getModel ( 'catalog/product' )->setStoreId ( $item->getStoreId () )->load ( $item->getProductId () );
			if ($item->getId ()) {
				$items [] = array (
						'name' => $item->getName (),
						'entity_id' => $item->getId (),
						'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $item->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
						'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $item->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
						'sku' => $item->getSku () ,
						'symbol' => Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
						'image_url' => Mage::helper('connector')-> Imageresize($item->getImage(),'product','100','100'),
						'wishlist' =>  Mage::helper('connector')->check_wishlist($item->getId ())

				);
			}
		}
		return array (
				'wishlist' => $wishlist->getData (),
				'items' => $items 
		);
	}


	/*
		
		URL : baseurl/restapi/Wishlist/getWishlist
		Name : removeWishlist
		Method : GET
		Parameters : product_id*
		Response : JSON
		Return Response :
		{
		  "status": "success"/"error",
		  "message": "return message"
		}
	*/
	public function removeWishlistAction(){

		$params = $this->getRequest()->getParam('product_id');

		if(!$params){
			echo json_encode(array('status'=>'error','message'=> $this->__('Product Id is missing.')));
			exit;
		}
		$session = Mage::getSingleton ( 'customer/session' );

		if($session->getCustomer()->getId()):
			$itemCollection = Mage::getModel('wishlist/item')->getCollection()
			    ->addCustomerIdFilter($session->getCustomer()->getId());

			foreach($itemCollection as $item):
				if($item->getProduct()->getId() == $params):
			    	try{
			    		$item->delete();
			    		$response['status'] = 'success';
						$response['message'] = $this->__('item removed from wishlist.');
			    	}
			    	catch(exception $e){
			    		$response['status'] = 'error';
						$response['message'] = $this->__($e->getMessage());
			    	}
			    endif;
			endforeach;
		else:
			$response['status'] = 'error';
			$response['message'] = $this->__ ( 'Unable to Create Wishlist' );
		endif;

		echo json_encode($response);
	} 

	/*Clear wishList API*/
	/*
	 URL : baseurl/restapi/wishlist/clearWishlist
	 Name : clearWishlist
	 Method : GET
	 Response : JSON
	*/

	 public function clearWishlistAction()
	 {

	 	if (Mage::getSingleton ( 'customer/session' )->isLoggedIn()):
	 		$customer = Mage::getSingleton ( 'customer/session' )->getCustomer();
			$customerId = $customer->getId();

			$wishlistItems = Mage::getModel('wishlist/item')->getCollection()
			    				->addCustomerIdFilter($customerId);

			foreach($wishlistItems as $item)
				    $item->delete();
				
			echo  json_encode(array('status'=>'success','message'=>'All wishlist items removed.'));
			exit;


		else:
			echo  json_encode(array('status'=>'error','message'=>'Kindly Signin first.'));
			exit;
		endif;

	 }
} 
