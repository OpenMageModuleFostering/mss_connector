<?php
class Mss_Connector_CartController extends Mage_Core_Controller_Front_Action {

	public $errors = '';
	public $cc_type =''; 
	public $cc_number = '';
	public $cc_month = '';
	public $cc_year = '';
	public $cc_cid ='';

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
		
		Mage::app()->setCurrentStore(Mage::app()->getStore($this->viewId));
		
		parent::_construct();
		
	}
	public function getaddurlAction() {
		$productid = $this->getRequest ()->getParam ( 'productid' );
		$product = Mage::getModel ( "catalog/product" )->load ( $productid );
		$url = Mage::helper ( 'checkout/cart' )->getAddUrl ( $product );
		echo "{'url':'" . $url . "'}";
		$cart = Mage::helper ( 'checkout/cart' )->getCart ();
		$item_qty = $cart->getItemsQty ();
		echo "{'item_qty':'" . $item_qty . "'}";
		$summarycount = $cart->getSummaryCount ();
		echo "{'summarycount':'" . $summarycount . "'}";
	}

	public function getMinimumorderAction(){

		$cart_data = json_decode($this->getRequest ()->getParam('cart_data'),1);

		
		if(!sizeof($cart_data)):
			echo json_encode(array('status'=>'error','message'=> $this->__('Nothing to add in cart, cart is empty.')));
			exit;
		endif;

		$cart = Mage::helper ( 'checkout/cart' )->getCart ();
		$cart->truncate();

		
		$session = Mage::getSingleton ( 'core/session', array (
				'name' => 'frontend' 
		) );
	
		foreach($cart_data as $params):


			try {
			
				
				$product = Mage::getModel ('catalog/product')->load ($params['product']);
				
				if ($product->getData('has_options')):
					# validate options
					$options=$params['options'];		
					if(count($options)>0):
							$params['options']=$options;
					endif;
				endif;

				if (isset ($params ['super_attribute'] )) :
					
					if(isset($params['options'])):
					$data = array("product"=>$params['product'],"options"=>$params['options'],"super_attribute"=>$params['super_attribute'],
						'qty' => $params['qty']	
						);
					else:
						$data = array("product"=>$params ['product'],"super_attribute"=>$params['super_attribute'],
							'qty' => $params['qty']	
						);
					endif;	
					$cart->addProduct ( $product, $data );
				else:
					
					$cart->addProduct ( $product, $params );
				endif;
				$session->setLastAddedProductId ( $product->getId () );
				
			

			} catch ( Exception $e ) {


				$result = '{"status":"error"';
				$result .= ', "message": "' . str_replace("\"","||",$e->getMessage ()) . '"}';
				echo $result;
				exit;	
				
			}


		endforeach;
		try{
			$session->setCartWasUpdated ( true );
			
			$cart->save ();	
		}
		catch(Exception $e){
			$result = '{"status":"error"';
				$result .= ', "message": "' . str_replace("\"","||",$e->getMessage ()) . '"}';
				echo $result;
				exit;	
		}
		
				

			

		if(Mage::getStoreConfig('sales/minimum_order/active')):
			$check_grand_total = Mage::helper('checkout/cart')->getQuote()->getBaseSubtotalWithDiscount();
			
			$amount = Mage::getStoreConfig('sales/minimum_order/amount');
			if ($check_grand_total < $amount):
				$message = Mage::getStoreConfig('sales/minimum_order/error_message');
				if(!$message) $message = 'Minimum Order Limit is '.$amount;
			    	echo json_encode(array('status'=>'error','message'=> $this->__($message)));
				exit;
			endif;
		
		endif;
		echo json_encode(array('status'=>'success','message'=> 'true'));
		exit;

	}


	public function updateCartAction()
	{
		
		$id = $this->getRequest ()->getParam('cart_data');
	
		$cart = Mage::getSingleton ( 'checkout/cart' );

		if ($id):
			try {
				$cart->removeItem ( $id )->save ();
				echo json_encode(array("status"=>"success"));
			} catch ( Mage_Core_Exception $e ) {
				echo json_encode ( array ("status" =>"error","message"=> $this->__($e->getMessage ())));
				
			} catch ( Exception $e ) {
				echo json_encode ( array ("status" =>"error","message"=>$this->__($e->getMessage ())));
				
			}
		else:
			echo json_encode(array ("status" =>"error","message"=>$this->__("Param cart_item_id is empty.")));
			exit;
		endif;
	}

	
	public function clearcartAction()
	{

			$cart = Mage::helper ( 'checkout/cart' )->getCart ();
			if($cart->getQuote ()->getItemsCount ()){
				Mage::getSingleton('checkout/cart')->truncate()->save();
			}
			Mage::getSingleton('checkout/session')->clear();

			
			$result = '{"result":"success"';
			$result .= ', "message": "' .$this->__( 'cart is empty!' ). '"}';
			echo $result;	
			
		 
	}

	# Add to cart product start
	public function addAction() {
		try {
			
			$product_id = $this->getRequest ()->getParam ( 'product' );

			$params = $this->getRequest ()->getParams ();
			
			if (isset ( $params ['qty'] )) {
				$filter = new Zend_Filter_LocalizedToNormalized ( array (
						'locale' => Mage::app ()->getLocale ()->getLocaleCode () 
				) );
				$params ['qty'] = $filter->filter ( $params ['qty'] );
			} else if ($product_id == '') {
				$session->addError ($this->__("Product Not Added
					The SKU you entered %s was not found." ,$sku));
			}
			$request = Mage::app ()->getRequest ();
			$product = Mage::getModel ( 'catalog/product' )->load ( $product_id );
			
			if ($product->getData('has_options')):
				# validate options
				$options=json_decode($params['options'],true);		
				if(count($options)>0):
						$params['options']=$options;
				endif;
			endif;

			$session = Mage::getSingleton ( 'core/session', array (
					'name' => 'frontend' 
			) );
			$cart = Mage::helper ( 'checkout/cart' )->getCart ();
			
			
			if (isset ( $params ['super_attribute'] )) :

				if(isset($params['options'])):
					$params = array("product"=>$params ['product'],"options"=>$params['options'],"super_attribute"=>json_decode($params ['super_attribute'],1),
						'qty' => $params ['qty']	
					);
				else:
					$params = array("product"=>$params ['product'],"super_attribute"=>json_decode($params ['super_attribute'],1),
						'qty' => $params ['qty']	
					);
				endif;	

				$cart->addProduct ( $product,$params);
			
			else:
				
				$cart->addProduct ( $product, $params );
			endif;

			
				$session->setLastAddedProductId ( $product->getId () );
				$session->setCartWasUpdated ( true );
				$cart->save ();
			

			$cart = Mage::getSingleton ( 'checkout/cart' );
			$quote = $cart->getQuote ();

			/*get last inserted cart ID*/
			$items = $quote->getAllVisibleItems ();
			$cartItemArr='';
			foreach ( $items as $item )
				$cartItemArr= $item->getId ();
			
			

			$items_qty = floor ( $quote->getItemsQty () );
			$result = '{"result":"success"';
			$result .= ', "items_qty": "' . $items_qty . '"';
			$result .= ', "cart_item_id": "' . $cartItemArr . '"}';
			echo $result;

		} catch ( Exception $e ) {
			$result = '{"result":"error"';
			$result .= ', "message": "' . str_replace("\"","||",$e->getMessage ()) . '"}';
			echo $result;	
			
		}
	}


# End add to cart product
	public function getQtyAction() {			
			$items_qty = floor(Mage::getModel('checkout/cart')->getQuote()->getItemsQty());
			$result = '{"items_qty": "'  . $items_qty  . '"}';

			echo $result;
		}
		
	public function addpro($product_id,$qty) {
		try {
			$session = Mage::getSingleton ( 'core/session', array (
					'name' => 'frontend' 
			) );
			// $product_id = $this->getRequest ()->getParam ( 'product' );
			$params['qty'] = $qty;//;$this->getRequest ()->getParams ();
			if (isset ( $params ['qty'] )) {
				$filter = new Zend_Filter_LocalizedToNormalized ( array (
						'locale' => Mage::app ()->getLocale ()->getLocaleCode () 
				) );
				$params ['qty'] = $filter->filter ( $params ['qty'] );
			} else if ($product_id == '') {
				$session->addError ($this->__( "Product Not Added
					The SKU you entered %s was not found.", $sku ));
			}
			$request = Mage::app ()->getRequest ();
			$product = Mage::getModel ( 'catalog/product' )->load ( $product_id );
			$cart = Mage::helper ( 'checkout/cart' )->getCart ();
			$cart->addProduct ( $product, $params );
			$session->setLastAddedProductId ( $product->getId () );
			$session->setCartWasUpdated ( true );
			$cart->save ();
			return true;
		} catch ( Exception $e ) {
			return $e;		
		}
	}

	/*
	*	URL : baseurl/restapi/cart/getcartinfo/
	*	Controller : cart
	*	Action : getcartinfo
	*	Method : GET
	*	Request Parameters :  address_id , region_id  , country_id
	*	Parameter Type : 
	*	Response : JSON
	*
	*/

	public function getCartInfoAction() {

	     echo json_encode ( $this->_getCartInformation () );
 	}
	public function removeAction() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$id = ( int ) $this->getRequest ()->getParam ( 'cart_item_id', 0 );
		if ($id) {
			try {
				$cart->removeItem ( $id )->save ();
				echo json_encode(array('cart_info'=>$this->_getCartInformation(),'total'=>$this->_getCartTotal ()));
			} catch ( Mage_Core_Exception $e ) {
				echo json_encode ( $this->__($e->getMessage () ));
				
			} catch ( Exception $e ) {
				echo json_encode ( $this->__($e->getMessage () ));
				
			}
		} else {
			echo json_encode ( array (
					false,
					'0x5002',
					'Param cart_item_id is empty.' 
			) );
		}
	}

 
	public function updateAction() {
		$itemId = ( int ) $this->getRequest ()->getParam ( 'cart_item_id', 0 );
		$qty = ( int ) $this->getRequest ()->getParam ( 'qty', 0 );
		$oldQty = 0;
		$item = null;
		try {
			if ($itemId && $qty > 0) {
				$cartData = array ();
				$cartData [$itemId] ['qty'] = $qty;
				$cart = Mage::getSingleton ( 'checkout/cart' );
				/* * ****** if update fail rollback ********* */
				if ($cart->getQuote ()->getItemById ( $itemId )) {
					$item = $cart->getQuote ()->getItemById ( $itemId );
				} else {
					echo json_encode ( array (
							'status' => 'error',
							'message' => $this->__('a wrong cart_item_id was given.')
					));
					return false;
				}
				$oldQty = $item->getQty ();
				if (! $cart->getCustomerSession ()->getCustomer ()->getId () && $cart->getQuote ()->getCustomerId ()) {
					$cart->getQuote ()->setCustomerId ( null );
				}
				$cart->updateItems ( $cartData )->save ();
				if ($cart->getQuote ()->getHasError ()) { // apply for 1.7.0.2
					$mesg = current ( $cart->getQuote ()->getErrors () );
					Mage::throwException ( $mesg->getText () );
					return false;
				}
			}
			$session = Mage::getSingleton ( 'checkout/session' );
			$session->setCartWasUpdated ( true );
		} catch ( Mage_Core_Exception $e ) { // rollback $quote->collectTotals()->save();
			$item && $item->setData ( 'qty', $oldQty );
			$cart->getQuote ()->setTotalsCollectedFlag ( false ); // reflash price
			echo json_encode (array('status'=>'error','message'=> $this->__($e->getMessage ()) ));
			exit;
		} catch ( Exception $e ) {
			echo json_encode (array('status'=>'error','message'=> $this->__( $e->getMessage ()) ));
			exit;
		}
		echo json_encode(array('cart_info'=>$this->_getCartInformation(),'total'=>$this->_getCartTotal ()));
	}


	public function getTotalAction() {
		echo json_encode ( $this->_getCartTotal () );
	}


	public function postCouponAction() {
		$couponCode = ( string ) Mage::app ()->getRequest ()->getParam ( 'coupon_code' );
		$cart = Mage::helper ( 'checkout/cart' )->getCart ();

		$coupan_codes = array();  
			$rulesCollection = Mage::getModel('salesrule/rule')->getCollection();
			foreach($rulesCollection as $rule){
			    $coupan_codes[] = $rule->getCode();			    
			}

			if (!in_array($couponCode, $coupan_codes))
			  {
					  echo json_encode ( array (
							'status' => 'error',
							'message' => $this->__("Coupon code  is not Valid" ) 
					  ));
					return false;
			  }
		


		
		if (! $cart->getItemsCount ()) {
			echo json_encode ( array (
					'code' => '0X0001',
					'message' => $this->__("You can't use coupon code with an empty shopping cart" 
			) ));
			return false;
		}
		if (Mage::app ()->getRequest ()->getParam ( 'remove' ) == 1) {
			$couponCode = '';
		}
		$oldCouponCode = $cart->getQuote ()->getCouponCode ();
		if (! strlen ( $couponCode ) && ! strlen ( $oldCouponCode )) {
			echo json_encode ( array (
					'code' => '0X0002',
					'message' => "Emptyed." 
			) );
			return false;
		}
		try {
			$codeLength = strlen ( $couponCode );
			$isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
			
			$cart->getQuote ()->getShippingAddress ()->setCollectShippingRates ( true );
			$cart->getQuote ()->setCouponCode ( $isCodeLengthValid ? $couponCode : '' )->collectTotals ()->save ();
			
			if ($codeLength) {
				if ($isCodeLengthValid && $couponCode == $cart->getQuote ()->getCouponCode ()) {
					$messages = array (
							'code' => '0x0000',
							'message' => $this->__ ( 'Coupon code "%s" was applied.', Mage::helper ( 'core' )->escapeHtml ( $couponCode ) ) 
					);
				} else {
					$messages = array (
							'code' => '0x0001',
							'message' => $this->__ ( 'Coupon code "%s" is not valid.', Mage::helper ( 'core' )->escapeHtml ( $couponCode ) ) 
					);
				}
			} else {
				$messages = array (
						'code' => '0x0002',
						'message' => $this->__ ( 'Coupon code was canceled.' ) 
				);
			}
		} catch ( Mage_Core_Exception $e ) {
			$messages = array (
					'code' => '0x0003',
					'message' => $e->getMessage () 
			);
		} catch ( Exception $e ) {
			$messages = array (
					'code' => '0x0004',
					'message' => $this->__ ( 'Cannot apply the coupon code.' ) 
			);
		}
		
		echo json_encode ( $this->_getCartTotal ()  );
	}




	protected function _getShippingTotal(){

	 	$addressId = $this->getRequest()->getParam('address_id');
	 	$countryId = $this->getRequest()->getParam('country_id');
	 	$setRegionId = $this->getRequest()->getParam('region_id');
	 	$shipping_method = $this->getRequest()->getParam('shippingmethod');
			if (isset($addressId)){
				$customer = Mage::getModel('customer/address')
			    ->load($addressId);
			    $countryId = $customer['country_id'];
			    $setRegionId = $customer['region_id'];
			    $regionName = $customer['region'];
		        $quote=Mage::getSingleton ( 'checkout/cart' )->getQuote();

		        $shippingCheck = $quote->getShippingAddress()->getData();
		        if($shippingCheck['shipping_method'] != $shipping_method) {
		        	if (isset($setRegionId)){
			        	$quote->getShippingAddress()
			              ->setCountryId($countryId)
			              ->setRegionId($setRegionId)
			              ->setCollectShippingRates(true);
			        } else {
					$quote->getShippingAddress()
			              ->setCountryId($countryId)
			              ->setRegion($regionName)
			              ->setCollectShippingRates(true);	        	
			        }
			        $quote->save();
			        $quote->getShippingAddress()->setShippingMethod($shipping_method)->save();
		        }
        
		        $quote->collectTotals ()->save ();
		        $amount=$quote->getShippingAddress()->getData();
		        $shipping_amount = $amount['shipping_incl_tax'];
		        return  $shipping_amount;
	        } else {  
	        	$quote=Mage::getSingleton ( 'checkout/cart' )->getQuote();
	        	$shippingCheck = $quote->getShippingAddress()->getData();

			    if($shippingCheck['shipping_method'] != $shipping_method) {
		        	if (isset($setRegionId)){
			        	$quote->getShippingAddress()
			              ->setCountryId($countryId)
			              ->setRegionId($setRegionId)
			              ->setCollectShippingRates(true);
			        } else {  
			        $quote->getShippingAddress()
			              ->setCountryId($countryId)
			              ->setCollectShippingRates(true);	
			        }
			        $quote->save();
			        $quote->getShippingAddress()->setShippingMethod($shipping_method)->save();
		    	}
		        $quote->collectTotals ()->save ();
		        $amount=$quote->getShippingAddress();
		        $shipping_amount = $amount['shipping_incl_tax'];
		        return $shipping_amount;
	        }
	}

	protected function _getCartInformation() {

		$shipping_amount = $this->_getShippingTotal();
		$cart = Mage::getSingleton ( 'checkout/cart' );
		if ($cart->getQuote ()->getItemsCount ()) {
			$cart->init ();
			$cart->save ();
		}
	 	$cart->getQuote ()->collectTotals ()->save ();
		$cartInfo = array ();
		$cartInfo ['is_virtual'] = Mage::helper ( 'checkout/cart' )->getIsVirtualQuote ();
		$cartInfo ['cart_items'] = $this->_getCartItems ();
		$cartInfo ['messages'] = sizeof ( $this->errors ) ? $this->errors : $this->_getMessage ();
		$cartInfo ['cart_items_count'] = Mage::helper ( 'checkout/cart' )->getSummaryCount ();
		$cartInfo ['grand_total'] = number_format ( $cart->getQuote()->getGrandTotal(), 2, '.', '' );
		$cartInfo ['sub_total'] = number_format ( $cart->getQuote()->getSubtotal(), 2, '.', '' );
		$cartInfo ['allow_guest_checkout'] = Mage::helper ( 'checkout' )->isAllowedGuestCheckout ( $cart->getQuote () );
		$cartInfo ['shipping_amount'] = $shipping_amount;
		
		return $cartInfo;
	}


	protected function _getCartTotal() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$totalItemsInCart = Mage::helper ( 'checkout/cart' )->getItemsCount (); // total items in cart
		$totals = Mage::getSingleton ( 'checkout/session' )->getQuote ()->getTotals (); // Total object
		$oldCouponCode = $cart->getQuote ()->getCouponCode ();
		$oCoupon = Mage::getModel ( 'salesrule/coupon' )->load ( $oldCouponCode, 'code' );
		$oRule = Mage::getModel ( 'salesrule/rule' )->load ( $oCoupon->getRuleId () );
		
		$subtotal = round ( $totals ["subtotal"]->getValue () ); // Subtotal value
		$grandtotal = round ( $totals ["grand_total"]->getValue () ); // Grandtotal value
		if (isset ( $totals ['discount'] )) { // $totals['discount']->getValue()) {
			$discount = round ( $totals ['discount']->getValue () ); // Discount value if applied
		} else {
			$discount = '0';
		}
		if (isset ( $totals ['tax'] )) { // $totals['tax']->getValue()) {
			$tax = round ( $totals ['tax']->getValue () ); // Tax value if present
		} else {
			$tax = '';
		}
		return array (
				'subtotal' => $subtotal,
				'grandtotal' => $grandtotal,
				'discount' => str_replace('-','',$discount),
				'tax' => $tax,
				'coupon_code' => $oldCouponCode,
				'coupon_rule' => $oRule->getData () 
		);
	}


	protected function _getMessage() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		if (! Mage::getSingleton ( 'checkout/type_onepage' )->getQuote ()->hasItems ()) {
			$this->errors [] = $this->__('Cart is empty!');
			return $this->errors;
		}
		if (! $cart->getQuote ()->validateMinimumAmount ()) {
			$warning = Mage::getStoreConfig ( 'sales/minimum_order/description' );
			$this->errors [] = $warning;
		}
		
		if (($messages = $cart->getQuote ()->getErrors ())) {
			foreach ( $messages as $message ) {
				if ($message) {
					$message = str_replace("\"","||",$message);
					$this->errors [] = $this->__($message->getText ());
				}
			}
		}
		
		return $this->errors;
	}


	private function _getPaymentInfo() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$methods = $cart->getAvailablePayment ();



		foreach ( $methods as $method ) {
			if ($method->getCode () == 'paypal_express') {
				return array (
						'paypalec' 
				);
			}
		}
		
		return array ();
	}


	protected function _getCartItems() {
		$cartItemsArr = array ();
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$quote = $cart->getQuote ();
		/*$currency = $this->currency;*/
		$displayCartPriceInclTax = Mage::helper ( 'tax' )->displayCartPriceInclTax ();
		$displayCartPriceExclTax = Mage::helper ( 'tax' )->displayCartPriceExclTax ();
		$displayCartBothPrices = Mage::helper ( 'tax' )->displayCartBothPrices ();

		$items = $quote->getAllVisibleItems ();
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		$currentCurrency = $this->currency;

		$product_model = Mage::getModel ( 'catalog/product' );

		foreach ( $items as $item ) {
			$product = $product_model->load($item->getProduct()->getId());
			$cartItemArr = array ();
			$cartItemArr ['cart_item_id'] = $item->getId ();
			$cartItemArr ['currency'] = Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
			$cartItemArr ['entity_type'] = $item->getProductType ();
			$cartItemArr ['item_id'] = $item->getProduct ()->getId ();
			$cartItemArr ['item_title'] = strip_tags ( $item->getProduct ()->getName () );
			$cartItemArr ['qty'] = $item->getQty ();
			$cartItemArr ['thumbnail_pic_url'] = Mage::helper('connector')-> Imageresize($product_model->load($item->getProduct ()->getId ())->getImage()
                    ,'thumbnail','100','100');
			$cartItemArr ['custom_option'] = $this->_getCustomOptions ( $item );
			$cartItemArr ['item_price'] =  number_format ( $item->getPriceInclTax(), 2, '.', '' );
			array_push ( $cartItemsArr, $cartItemArr );
		}
		
		return $cartItemsArr;
	}


	protected function _getCustomOptions($item) {
		$session = Mage::getSingleton ( 'checkout/session' );
		$options = $item->getProduct ()->getTypeInstance ( true )->getOrderOptions ( $item->getProduct () );
		
		$result = array ();
		if ($options) {
			if (isset ( $options ['options'] )) {
				$result = array_merge ( $result, $options ['options'] );
			}
			if (isset ( $options ['additional_options'] )) {
				$result = $result = array_merge ( $result, $options ['additional_options'] );
			}
			if (! empty ( $options ['attributes_info'] )) {
				$result = $result = array_merge ( $result, $options ['attributes_info'] );
			}
		}
		
		return $result;
	}

	protected function _getCustomOption($item) {
		$session = Mage::getSingleton ( 'checkout/session' );
		$options = $item->getProduct ()->getTypeInstance ( true )->getOrderOptions ( $item->getProduct () );
		
		$result = array ();
		if (count($options['options'])) {
			if (isset ( $options ['options'] )) {
				$result = array_merge ( $result, $options ['options'] );
			}
			if (isset ( $options ['additional_options'] )) {
				$result = $result = array_merge ( $result, $options ['additional_options'] );
			}
			/*if (! empty ( $options ['attributes_info'] )) {
				$result = $result = array_merge ( $result, $options ['attributes_info'] );
			}*/
		}
		
		return $result;
	}

	protected function _getConfigurableOptions($item) {
		
		$options = $item->getProduct ()->getTypeInstance ( true )->getOrderOptions ( $item->getProduct () );
		
		$result = array ();
		if(count($options['info_buyRequest']['super_attribute'])):
			$configurable = array();
			$i = 0;
			foreach($options['info_buyRequest']['super_attribute'] as $key => $value):
				$configurable['attribute_id'] = $key;
				$configurable['option_id'] = $value;
				$configurable['attribute_name'] = $options['attributes_info'][$i]['label'];
				$configurable['option_name'] = $options['attributes_info'][$i]['value'];

				$i++;
				$result[]= $configurable;
			endforeach;

		endif;
		
		return $result;
	}


	public function _addToCart() {
		$cart = Mage::getSingleton ( 'checkout/cart' );
		$session = Mage::getSingleton ( 'core/session', array (
				'name' => 'frontend'
		) );
		$params = $this->getRequest ()->getParams ();
		if ($params ['isAjax'] == 1) {
			$response = array ();
			try {
				if (isset ( $params ['qty'] )) {
					$filter = new Zend_Filter_LocalizedToNormalized ( array (
							'locale' => Mage::app ()->getLocale ()->getLocaleCode () 
					) );
					$params ['qty'] = $filter->filter ( $params ['qty'] );
				}
				$product = Mage::getModel ( 'catalog/product' )->load ( $params['product_id'] );
				$related = $this->getRequest ()->getParam ( 'related_product' );
				/**
				 * Check product availability
				 */
				if (! $product) {
					$response ['status'] = 'ERROR';
					$response ['message'] = $this->__ ( 'Unable to find Product ID' );
				}
				$cart->addProduct ( $product, $params );
				if (! empty ( $related )) {
					$cart->addProductsByIds ( explode ( ',', $related ) );
				}
				$cart->save ();
				$session->setCartWasUpdated ( true );
				/**
				 *
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent ( 'checkout_cart_add_product_complete', array (
						'product' => $product,
						'request' => $this->getRequest (),
						'response' => $this->getResponse () 
				) );
				if (! $session->getNoCartRedirect ( true )) {
					if (! $cart->getQuote ()->getHasError ()) {
						$message = $this->__ ( '%s was added to your shopping cart.', Mage::helper ( 'core' )->htmlEscape ( $product->getName () ) );
						$response ['status'] = 'SUCCESS';
						$response ['message'] = $message;
					}
				}
			} catch ( Mage_Core_Exception $e ) {
				$msg = "";
				if ($session->getUseNotice ( true )) {
					$msg = $e->getMessage ();
				} else {
					$messages = array_unique ( explode ( "\n",$this->__( $e->getMessage () ) ));
					foreach ( $messages as $message ) {
						$msg .= $message . '<br>';
					}
				}
				$response ['status'] = 'ERROR';
				$response ['message'] = $msg;
			} catch ( Exception $e ) {
				$response ['status'] = 'ERROR';
				$response ['message'] = $this->__ ( 'Cannot add the item to shopping cart.' );
				Mage::logException ( $e );
			}
			$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $response ) );
			return;
		} else {
			return parent::addAction ();
		}
	}
	
	
	####get all enabled shipping methods
	public function getshippingmethodsAction(){ 

		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
		$shipMethods = array();
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		$currentCurrency = $this->currency;

		foreach ($methods as $shippigCode=>$shippingModel) 
		{

		   if( $carrierMethods = $shippingModel->getAllowedMethods() )
  		   {
	       foreach ($carrierMethods as $methodCode => $method)
	       {
	            $code= $shippigCode.'_'.$methodCode;
	  	
	  
				if($shippigCode == 'freeshipping'):
					if(Mage::getStoreConfig('carriers/'.$shippigCode.'/free_shipping_subtotal') < Mage::helper('checkout/cart')->getQuote()->getBaseSubtotalWithDiscount()):
						$shippingTitle = Mage::getStoreConfig('carriers/'.$shippigCode.'/title');
					    	$shippingPrice = Mage::getStoreConfig('carriers/'.$shippigCode.'/price');
					    	$shipMethods[]=array(
					    				'code'=>$shippigCode.'_'.$shippigCode,
					    				'value'=>$shippingTitle,
					    				'price'=>number_format ( Mage::helper ( 'directory' )
												->currencyConvert ( $shippingPrice, 
												$baseCurrency, 
												$currentCurrency ), 2, '.', '' )
					    		);
					endif;

				else:
					$shippingTitle = Mage::getStoreConfig('carriers/'.$shippigCode.'/title');
				    	$shippingPrice = Mage::getStoreConfig('carriers/'.$shippigCode.'/price');
				    	$shipMethods[]=array(
				    				'code'=>$code,
				    				'value'=>$shippingTitle,
				    				'price'=>number_format ( Mage::helper ( 'directory' )
												->currencyConvert ( $shippingPrice, 
												$baseCurrency, 
												$currentCurrency ), 2, '.', '' )
				    		);
				endif;
			     }

  			 }
		    
		    		
		}
		
		echo json_encode($shipMethods);
	}

	
	####get all payment methods  for now only paypal and cod
	public function getpaymentmethodsAction(){

		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array();
        foreach ($payments as $paymentCode=>$paymentModel) {
           if(Mage::getStoreConfig('magentomobileshop_payment/'.$paymentCode.'/'.$paymentCode.'_status')):
                $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            	if($paymentCode == 'authorizenet')
	                $methods[] = array(
	                    'value'   => $paymentTitle,
	                    'code' => $paymentCode,
	                    'cards' => array('Visa'=>'VI','Mastercard'=>'MC','American Express'=>'AE','Discover'=>'DI'),
	                );
	            else
	            	$methods[] = array(
	                    'value'   => $paymentTitle,
	                    'code' => $paymentCode,
	                );
           endif;
        }
       /* if(Mage::getStoreConfig('magentomobileshop_payment/paypal_express/paypal_express_status') &&
        	Mage::getStoreConfig('magentomobileshop_payment/paypal_express/paypal_express_email'))
        	$methods[] = array('value'=> 'PayPal Express Checkout','code'=> 'paypal_express');
*/
	
		echo json_encode($methods);
	}


	/*check for minium order*/
	public function checkMinimumorder($price){

		
		$amount = Mage::getStoreConfig('sales/minimum_order/amount');
		if ($price < $amount):
			$message = Mage::getStoreConfig('sales/minimum_order/error_message');
			if(!$message) $message = 'Minimum Order Limit is '.$amount;
		    	echo json_encode(array('status'=>'error','message'=> $this->__( $message)));
			exit;
		endif;

		return true;

	}
	
	####place order api
	public function placeOrderAction(){
			
			if(Mage::getStoreConfig('sales/minimum_order/active')):
					$check_grand_total = Mage::helper('checkout/cart')->getQuote()->getBaseSubtotalWithDiscount();
					$this->checkMinimumorder($check_grand_total);
			endif;

			if (Mage::getSingleton ( 'customer/session' )->isLoggedIn ()) {
					

					$session = Mage::getSingleton ( 'customer/session' );
					$customerId=$session->getId();

					
					
					##Get current quote 
					$totalItems = Mage::helper('checkout/cart')->getSummaryCount();
				
					if($totalItems > 0):
							#get the addressid
							$addressId=(int)$this->getRequest()->getParam('addressid');
							$shipping_method=$this->getRequest()->getParam('shippingmethod'); 
							$paymentmethod=$this->getRequest()->getParam('paymentmethod');
							$registration_id = $this->getRequest()->getParam('registration_id');
							$card_details = $this->getRequest()->getParam('cards_details');
							$save_cc = $this->getRequest()->getParam('save_cc');



							if($paymentmethod == 'authorizenet')
								$this->validateCarddtails(json_decode($card_details,1));

							if (!Zend_Validate::is($addressId, 'NotEmpty')):
								echo json_encode(array('Status'=>'error','message'=> $this->__('AddressId should not be empty')));
					    			exit;
							endif;
							if (!Zend_Validate::is($shipping_method, 'NotEmpty')):
								echo json_encode(array('Status'=>'error','message'=>$this->__('Shippingmethod should not be empty')));
					    			exit;
							endif;
							if (!Zend_Validate::is($paymentmethod, 'NotEmpty')):
								echo json_encode(array('Status'=>'error','message'=>$this->__('paymentmethod should not be empty')));
					    			exit;
							endif;
							if($addressId==''){
										$result=array(
										'message'=>$this->__('address is missing!!!!'),
										'status'=>'success'

								);
								echo json_encode($result);
								exit;

							}


							#load the customer 
							$customer = Mage::getModel('customer/customer')->load($customerId);

							#address load
							try {
								$addressData=Mage::getModel('customer/address')->load($addressId)->getData();
								$quote=Mage::getSingleton ( 'checkout/session' )->getQuote();
								//$quote->setMms_order_type('app')->save();

								$billingAddress = $quote->getBillingAddress()->addData($addressData);
								$shippingAddress = $quote->getShippingAddress()->addData($addressData);
								 
								$shippingAddress->setCollectShippingRates(true)->collectShippingRates()
								                ->setShippingMethod($shipping_method);

								if($paymentmethod != 'authorizenet'):
									$shippingAddress->setPaymentMethod($paymentmethod);
									$quote->getPayment()->importData(array('method' => $paymentmethod));
								
								endif;
	   
								 
								
								 
								$quote->collectTotals()->save();

								
								$transaction = Mage::getModel('core/resource_transaction');
								
								if ($quote->getCustomerId()) {
								    $transaction->addObject($quote->getCustomer());
								}
								$transaction->addObject($quote);
								$quote->reserveOrderId();
								 
								
								if($paymentmethod == 'authorizenet')
									$this->authorizePayment($quote,$transaction,$save_cc);


								$service = Mage::getModel('sales/service_quote', $quote);
								$service->submitAll();
								$order = $service->getOrder();
								$order->setMms_order_type('app')->save();
								$order->sendNewOrderEmail();
								$quote->delete();
								
								$cart = Mage::helper ( 'checkout/cart' )->getCart ();
								if($cart->getQuote ()->getItemsCount ()){
									Mage::getSingleton('checkout/cart')->truncate()->save();
								}
								Mage::getSingleton('checkout/session')->clear();

								 $result=array(	'message'=>$this->__('Order placed successfully.'),
								 				'orderid'=>$order->getIncrementId(),
															'result'=>'success'

													);
															
										

								echo json_encode($result);
							
							} catch (Exception $e) {

								echo json_encode(array('status'=>'error','message'=> $this->__($e->getMessage())));
								exit;
								
							}
					else:
							$result=array(
										'message'=> $this->__('cart is empty'),
										'result'=>'success'

								);
							echo json_encode($result);
					endif;

			}else{

					
					ini_set('memory_limit', '128M');

					$getParams = $this->getRequest()->getParams();
				
					$payment_method = $getParams['paymentmethod'];
					$shipping_method = $getParams['shippingmethod'];
					$registration_id = $this->getRequest()->getParam('registration_id');
					$card_details = $this->getRequest()->getParam('cards_details');
					$save_cc = $this->getRequest()->getParam('save_cc');

					if($payment_method == 'authorizenet')
						$this->validateCarddtails(json_decode($card_details,1));


					try{

						if(Mage::getStoreConfig('sales/minimum_order/active')):
							$check_grand_total = Mage::helper('checkout/cart')->getQuote()->getBaseSubtotalWithDiscount();
							$this->checkMinimumorder($check_grand_total);
						endif;

						$checkout_session =Mage::getModel ( 'checkout/session' )->getQuoteId();
						//Mage::getSingleton ( 'checkout/session' )->getQuote()->setMms_order_type('app')->save();

						$quote = Mage::getModel('sales/quote')->load($checkout_session);
						$quote->setStoreId(Mage::app()->getStore()->getId());
						
					
						
						
						$billingAddress = array(
						    'firstname' => $getParams['firstname'],
						    'lastname' => $getParams['lastname'],
						    
						    'email' =>  $getParams['email'],
						    'street' => array(
						       $getParams['street_line_1'],
						        @$getParams['street_line_2']
						    ),
						    'city' => $getParams['city'],
						    /*'region' => $getParams['region'],*/
						    'postcode' => $getParams['postcode'],
						    'country_id' => $getParams['country_id'],
						    'telephone' =>  $getParams['telephone'],
						    'customer_password' => '',
						    'confirm_password' =>  '',
						    'save_in_address_book' => '0',
						    'use_for_shipping' => '1',
						);
						
						if(isset($data['region']))
							$billingAddress['region']=$getParams['region'];
						else
							$billingAddress['region_id']=$getParams['region_id'];
						
						$quote->getBillingAddress()
						        ->addData($billingAddress);

						 $quote->getShippingAddress()
					            ->addData($billingAddress)
					            ->setShippingMethod($shipping_method);
					         							
						$quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
						$quote->collectTotals();
							 
					    if($payment_method != 'authorizenet'):
							$quote->setPaymentMethod($payment_method);
							$quote->getPayment()->importData( array('method' => $payment_method));
					
						endif;

						$customer_id = Mage::helper('connector')->reigesterGuestUser(array('firstname' => $getParams['firstname'],
						    'lastname' => $getParams['lastname'],'email'=>$getParams['email']));

						$quote->setCustomer(Mage::getSingleton('customer/customer')->load($customer_id));
					    
					    $quote->save();

				        $transaction = Mage::getModel('core/resource_transaction');
						
						if ($quote->getCustomerId())
							   $transaction->addObject($quote->getCustomer());
							
						if($payment_method == 'authorizenet')
								$this->authorizePayment($quote,$transaction,$save_cc);


				        $service = Mage::getModel('sales/service_quote', $quote);
				        $service->submitAll();
				        $order = $service->getOrder();
				        $order->setMms_order_type('app')->save();
				        $order->sendNewOrderEmail();
				     
     					$increment_id = $order->getRealOrderId();	 				
						$quote = $customer = $service = null;

						
						$cart = Mage::helper ( 'checkout/cart' )->getCart ();
						if($cart->getQuote ()->getItemsCount ()){
							Mage::getSingleton('checkout/cart')->truncate()->save();
						}
						Mage::getSingleton('checkout/session')->clear();
						echo json_encode(array('status' =>'success','orderid' => $increment_id));
						exit;
				}
				catch (Exception $e) 
				{
							echo json_encode(array('status' =>'error','message' => $this->__($e->getMessage())));
							exit;
							
				}
				
			}

	}

	public function authorizePayment($quoteObj,$transaction,$save_cc=false){

			$ccInfo = array();
		    $quotePaymentObj = $quoteObj->getPayment();
		    $quotePaymentObj->setMethod('authorizenet');
		    $quoteObj->setPayment($quotePaymentObj);
		    
		    $quoteObj->getPayment()->setCcNumber($this->cc_number);
		    $quoteObj->getPayment()->setCcType($this->cc_type);
		    $quoteObj->getPayment()->setCcExpMonth($this->cc_month);
		    $quoteObj->getPayment()->setCcExpYear($this->cc_year);
		    $quoteObj->getPayment()->setCcLast4(substr($this->cc_number,-4));
		    $quoteObj->getPayment()->setCcCid($this->cc_cid);
		
		 
		$convertQuoteObj = Mage::getSingleton('sales/convert_quote');
		if ($quoteObj->getIsVirtual())
		    $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getBillingAddress());
		else
		    $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
		
		$orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);
		 
		$orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
		$orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));

		if (!$quoteObj->getIsVirtual())
		    $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
		
		 
		// set payment options
		if (count($ccInfo) > 0):
		    $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
		     $orderObj->getPayment()->setCcNumber($this->cc_number);
		    $orderObj->getPayment()->setCcType($this->cc_type);
		    $orderObj->getPayment()->setCcExpMonth($this->cc_month);
		    $orderObj->getPayment()->setCcExpYear($this->cc_year);
		    $orderObj->getPayment()->setCcLast4(substr($this->cc_number,-4));
		    $orderObj->getPayment()->setCcCid($this->cc_cid);
		
		else:
		    $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
		endif;
		 
		$items=$quoteObj->getAllItems();
		 
		foreach ($items as $item):
		
		    $orderItem = $convertQuoteObj->itemToOrderItem($item);

		    if ($item->getParentItem())
		        $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
		    
		    $orderObj->addItem($orderItem);
		endforeach;
		 
		$orderObj->setCanShipPartiallyItem(false);
		 
		$totalDue = $orderObj->getTotalDue();
		
		$transaction->addObject($orderObj);
		$transaction->addCommitCallback(array($orderObj, 'place'));
		$transaction->addCommitCallback(array($orderObj, 'save'));
		 
		try {
		    $transaction->save();

		    $data = array('cc_number'=>(int)$this->cc_number,
		    	'cc_type'=>$this->cc_type,
		    	'cc_exp_month'=>(int)$this->cc_month,
		    	'cc_exp_year'=>(int)$this->cc_year,
		    	'cc_last4'=>(int)substr($this->cc_number,-4)
		    	);

		    if($save_cc =='true')Mage::helper('connector')->saveCc($data);

		} catch (Exception $e){
		    Mage::throwException('Order Cancelled Bad Response from Credit Authorization.');
		    exit;
		}
		 
		$orderObj->sendNewOrderEmail();
				
		try{
			$quoteObj->setIsActive(0);
			$quoteObj->save();
			$orderId =$orderObj->getRealOrderId();
			/*$quoteObj->delete();*/

			$cart = Mage::helper ( 'checkout/cart' )->getCart ();
			if($cart->getQuote ()->getItemsCount ())
				Mage::getSingleton('checkout/cart')->truncate()->save();
			
			Mage::getSingleton('checkout/session')->clear();
		}
		catch(exception $e){
			 //add mail function
		}
		
				

		 $result=array(	'message'=>$this->__('Order placed successfully.'),
		 				'orderid'=>$orderId,
									'result'=>'success'

							);
		
		echo json_encode($result);exit;

	}


	// Authorize.net payment Implementation

	public function validateCarddtails($card_details){
		
		if(!sizeof($card_details)):
			echo json_encode(array('status' =>'error','message' => $this->__('Card Information is required in case of authorize.net payment method.')));
			exit;
		endif;

		if(!$card_details['cc_type'] || !$card_details['cc_number'] || 
			!$card_details['cc_month'] || !$card_details['cc_year'] || !$card_details['cc_cid']):
			echo json_encode(array('status' =>'error','message' => $this->__('Some Required card information is missing.')));
			exit;
		endif;
		$this->cc_type = $card_details['cc_type'];
		$this->cc_number = $card_details['cc_number'];
		$this->cc_month = $card_details['cc_month'];
		$this->cc_year = $card_details['cc_year'];
		$this->cc_cid = $card_details['cc_cid'];


		return true;

			
	}

	/*	URL : /restapi/cart/getcheckoutcart/	
		Route: Connector
		Controller: cart
		Action: getcheckoutcart
		Request Parameter : None
		Method : POST
		Response : JSON
		{"product":[{"id":"3","sku":"test3","qty":7,"Name":"test3","Price":3500}],"subtotal":"3500.0000","grandtotal":"3500.0000","totalitems":"1","totalquantity":"3500.0000"}
	*/        

		public function getcheckoutcartAction(){
			 $customerId =(int)$this->getRequest()->getParam('customerid');

       if ($customerId) { 
        
          $customer = Mage::getModel('customer/customer')->load($customerId); 
          try{
          $cart = Mage::getModel('sales/quote') ->loadByCustomer($customer);
              
              if(!count($cart->getAllItems())):
                echo json_encode(array('status'=>'success','message'=>$product));
                exit;
              endif;
              $product_model = Mage::getModel ( 'catalog/product' );

            $baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
			$currentCurrency = $this->currency;

                foreach ($cart->getAllVisibleItems() as $item) {

                  $productName= array();
                  $productName['cart_item_id'] = $item->getId();
                  $productName['id'] = $item->getProductId();
                  $productName['sku'] = $item->getSku();
                  $productName['qty'] = $item->getQty();              
                  $productName['Name'] = $item->getProduct()->getName();
                  /*$productName['Price'] = $item->getPrice()* $item->getQty();*/
                  $productName['Price'] = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $item->getPriceInclTax(), 			$baseCurrency, $currentCurrency ), 2, '.', '' );

                  $productName['image'] =Mage::helper('connector')-> Imageresize($product_model->load($item->getProductId())->getImage()
                    ,'thumbnail','100','100');
			$productName['wishlist'] =  Mage::helper('connector')->check_wishlist($item->getProductId());    
                          
                  if($product_model->load($item->getProductId())->isConfigurable())
                  	$productName['configurable'] = $this->_getConfigurableOptions( $item );

                  if($product_model->load($item->getProductId())->getData('has_options'))
                  	($this->_getCustomOption( $item ))?$productName['custom_option'] = $this->_getCustomOption( $item ):'';
                  		

                  $product['product'][] = $productName;
                  
              }        
              $product['subtotal'] = $cart->getSubtotal();
              $product['grandtotal'] = $cart->getGrandTotal();
              $product['totalitems'] = $cart->getItemsCount();
              $product['symbol'] = Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
              
              echo json_encode(array('status'=>'success','message'=>$product));

              }
            catch(exception $e)
            {
              echo json_encode(array('status'=>'error','message'=> $this->__($e->getMessage())));
            } 
          }
          else {
          		try{
		          	$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
					$currentCurrency = $this->currency;
					$product_model = Mage::getModel ( 'catalog/product' );
					
		          	$cart = Mage::getSingleton ( 'checkout/cart' );
					$quote = $cart->getQuote ();

					/*get last inserted cart ID*/
					$items = $quote->getAllVisibleItems ();
					$cartItemArr='';
					foreach ( $items as $item ){
					  $productName= array();
	                  $productName['cart_item_id'] = $item->getId();
	                  $productName['id'] = $item->getProductId();
	                  $productName['sku'] = $item->getSku();
	                  $productName['qty'] = $item->getQty();              
	                  $productName['Name'] = $item->getProduct()->getName();
	                  /*$productName['Price'] = $item->getPrice()* $item->getQty();*/
	                  $productName['Price'] = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $item->getPriceInclTax(), 			$baseCurrency, $currentCurrency ), 2, '.', '' );


	                  $productName['image'] =Mage::helper('connector')-> Imageresize($product_model->load($item->getProductId())->getImage()
	                    ,'product','100','100');  
	                          
	                  if($product_model->load($item->getProductId())->isConfigurable())
	                  	$productName['configurable'] = $this->_getConfigurableOptions( $item );

	                  if($product_model->load($item->getProductId())->getData('has_options'))
	                  	($this->_getCustomOption( $item ))?$productName['custom_option'] = $this->_getCustomOption( $item ):'';
	                  		

	                  $product['product'][] = $productName;

					}
					$product['subtotal'] = $cart->getSubtotal();
	              	$product['grandtotal'] = $cart->getGrandTotal();
	              	$product['totalitems'] = $cart->getItemsCount();
	            	$product['symbol'] = Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
	              
	              echo json_encode(array('status'=>'success','message'=>$product));
	            }
				catch(exception $e)
	            {
	              echo json_encode(array('status'=>'error','message'=>$e->getMessage()));
	            } 	
			}


            //echo json_encode(array('status'=>'error','message'=>'Please Login.'));
    }
	 /*	Cancel Order API :

		URL : /restapi/cart/cancelOrder/	
		Route: Connector
		Controller: cart
		Action: cancelOrder
		Request Parameter orderId
		Method : POST
		Response : status(error,success),message
	
	*/   

	public function cancelOrderAction(){
		$orderId =(int)$this->getRequest()->getParam('orderId');
	
		if($orderId):
			if(Mage::getSingleton ( 'customer/session' )->isLoggedIn()):
				try{
					$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
					if(!$order->getId()):
						echo json_encode(array('status'=>'error','message'=>$this->__('Invalid Order Id.')));
			    		exit;
					endif;

					$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();

					$history = $order->addStatusHistoryComment('Order marked as cancelled by User.', false);
	    			$history->setIsCustomerNotified(true);

					if($order->canCancel())
						$order->cancel()->save();
				}
				catch(Exceptio $e)
				{
					echo json_encode(array('status'=>'error','message'=> $this->__($e->getMessage())));
			    	exit;
				}
				
				echo json_encode(array('status'=>'success','message'=> $this->__('Order marked as cancelled by User.')));
			    exit;
			else:
				echo json_encode(array('status'=>'error','message'=> $this->__('Login first tio cancel Order.')));
			    exit;
			endif;		

		else:
			  echo json_encode(array('status'=>'error','message'=> $this->__('Please send Order Id to cancel.')));
			  exit;
		endif;

	}

	/*	Get Saved Cards API :

		URL : /restapi/cart/getsavedCc/	
		Route: Connector
		Controller: cart
		Action: getsavedCc
		Method : POST
	
	*/   

	public function getsavedCcAction(){

		$user_id =  Mage::getSingleton("customer/session")->getId();
		
		$collection =  Mage::getModel("connector/connector")->getCollection();
			$collection->addFieldToFilter('user_id',array('eq'=>$user_id))
			->addFieldToSelect(array('id','cc_number','cc_last4','cc_type','cc_exp_month','cc_exp_year'));

			if($collection->getSize()):
				echo json_encode($collection->getData());
				exit;
			else:
				echo json_encode(array());
				exit;

			endif;
		echo json_encode(array()); exit;
		

	}

	/*	Remove Saved Cards API :

		URL : /restapi/cart/removesavedCc/	
		Route: Connector
		Controller: cart
		Action: removesavedCc
		Parameter : id*
		Method : POST
	
	*/   

	public function removesavedCcAction(){

		$cc_id  =(int)$this->getRequest()->getParam('id');


		$user_id =  Mage::getSingleton("customer/session")->getId();
		
		if($cc_id && $user_id):

			$collection =  Mage::getModel("connector/connector")->getCollection();		

			$collection->addFieldToFilter(array('user_id', 'id'),											    array(											        							array('eq'=>$user_id), 
											        array('eq'=>$cc_id)
											    ));
				
			if($collection->getSize()):
           		foreach ($collection as $cc_card):
           			try{$cc_card->delete();}
           			catch(expection $e){
           					echo json_encode( array('status' => 'error' , 'massage'=> $this->__($e->getMessage() )));
		  				    exit;
           			}			
				endforeach;
			endif;				
		    echo json_encode( array('status' => 'success' , 'massage'=> $this->__('Card remove successfully' )));
		    exit;
		else:
			echo json_encode( array('status' => 'error' , 'massage'=>$this->__('error in removing card' )));
		    exit;
		endif;	
	}


} 
