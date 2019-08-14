<?php
class Mss_Connector_ProductsController extends Mage_Core_Controller_Front_Action {

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
	
	public function getcustomoptionAction() {
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		//$currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();
		$currentCurrency = $this->currency;
		$productid = $this->getRequest ()->getParam ( 'productid' );
		$product = Mage::getModel ( "catalog/product" )->load ( $productid );
		$selectid = 1;
		$select = array ();
		foreach ( $product->getOptions () as $o ) {
			if (($o->getType () == "field") || ($o->getType () == "file")) {
				$select [$selectid] = array (
						'option_id' => $o->getId (),
						'custom_option_type' => $o->getType (),
						'custom_option_title' => $o->getTitle (),
						'is_require' => $o->getIsRequire (),
						'price' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $o->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
						'price_type' => $o->getPriceType (),
						'sku' => $o->getSku (),
						'max_characters' => $o->getMaxCharacters () 
				);
			} else {
				$max_characters = $o->getMaxCharacters ();
				$optionid = 1;
				$options = array ();
				$values = $o->getValues ();
				foreach ( $values as $v ) {
					$options [$optionid] = $v->getData ();
					if(null!==$v->getData('price') && null!==$v->getData('default_price')){
						$options [$optionid]['price']=number_format ( Mage::helper ( 'directory' )->currencyConvert ( $v->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
						$options [$optionid]['default_price']=number_format ( Mage::helper ( 'directory' )->currencyConvert ( $v->getDefaultPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
					}
						
					
					$optionid ++;
				}
				$select [$selectid] = array (
						'option_id' => $o->getId (),
						'custom_option_type' => $o->getType (),
						'custom_option_title' => $o->getTitle (),
						'is_require' => $o->getIsRequire (),
						'price' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $o->getFormatedPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
						'max_characters' => $max_characters,
						'custom_option_value' => $options 
				);
			}
			
			$selectid ++;
			
		}
		echo json_encode ( $select );
	}

	   /***Convert Currency***/
	public function convert_currency($price,$from,$to) {
		return 1;
		$newPrice = Mage::helper('directory')->currencyConvert($price, $from, $to);
		return $newPrice;
	} 
	
	public function getproductdetailAction() {
		$this->productdetail($this->getRequest ()->getParam ( 'productid' ));
	}

	public function getproductdetailByskuAction() {

		$sku = $this->getRequest ()->getParam ('productsku');

		$id = Mage::getModel('catalog/product')->getResource()->getIdBySku($sku);
		if($id)$this->productdetail($id);
		else echo "[]";
	}

	public function productdetail($productid) {
        $productdetail = array ();
        $baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
        
        $currentCurrency = $this->currency;
        /*$productid = $this->getRequest ()->getParam ( 'productid' );*/
        
        $model = Mage::getModel ( "catalog/product" );
        $product = $model->load ( $productid );
      
        /*get product rating*/

        $reviews = Mage::getModel('review/review')
                ->getResourceCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addEntityFilter('product', $productid)
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();                
        $avg = 0;
        $ratings = array();
        $rdetails=array();

        $productPrice = $product->getPrice();
            $block = new Mage_Catalog_Block_Product_Price();

            $product_tier_prices = $block->getTierPrices($product);
            if(count($product_tier_prices) > 0){
                $product_tier_prices = (object)$product_tier_prices;
                $tier_prices = '';
                foreach ($product_tier_prices as $_index => $_price){
                   
                    $product_qty = $_price['price_qty'];
                    $tier_price = number_format($_price['price'], 2, '.' ,'');
                    $discount =  ceil(100 - ((100 / $productPrice) * $_price['price']));
                    $symbol = Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
                    $tier =  'Buy '.$product_qty .' for '.$symbol.$tier_price .' each and save '. $discount .'%';

                $tier_prices .=  '<span>' .$tier.'</span>';
                }
        }


        $all_custom_option_array = array();
        
        if (count($reviews) > 0):
            foreach ($reviews->getItems() as $review):
                
                $review_rating=0;

                foreach( $review->getRatingVotes() as $vote ):
                    $review_rating = $vote->getPercent();                        
                    $ratings[] = $vote->getPercent();
                endforeach;

                if($review_rating)
                $rating_by = ($review_rating/20);

                $rdetails[]= array(
                            'title'=>$review->getTitle(),
                            'description'=>$review->getDetail(),
                            'reviewby'=>$review->getNickname(),
                            'rating_by'=>$rating_by,
                            'rating_date'=>date("d-m-Y", strtotime($review->getCreatedAt())),
                    );
            endforeach;
            $avg = array_sum($ratings)/count($ratings);
        endif;
  
    $rating=ceil($avg/20);
    $reviews=$rdetails;

    /*get product rating*/

    if($product->getTypeId() == "configurable"):
        $productdetail = array();
        $config = $product->getTypeInstance(true);
 
        $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
        $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions()->getData();

        $storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA); 
        $description =  nl2br ( $product->getDescription () );
        $description = str_replace("{{media url=\"",$storeUrl,$description);
        $description = str_replace("\"}}","",$description);
            
            $c_ops=array();

            if($product->getData('has_options')):
                $has_custom_options = true;
                // Start custom options
                $all_custom_option_array = array();
                $attVal = $product->getOptions();
                $optStr = "";
                $inc=0;
                $has_custom_option = 0;

                foreach($attVal as $optionKey => $optionVal):
             
                    $has_custom_option = 1;
                    $all_custom_option_array[$inc]['custom_option_name']=$optionVal->getTitle();
                    $all_custom_option_array[$inc]['custom_option_id']=$optionVal->getId();
                    $all_custom_option_array[$inc]['custom_option_is_required']=$optionVal->getIsRequire();
                    $all_custom_option_array[$inc]['custom_option_type']=$optionVal->getType();
                    $all_custom_option_array[$inc]['sort_order'] = $optionVal->getSortOrder();
                    $all_custom_option_array[$inc]['all'] = $optionVal->getData();

                    if($all_custom_option_array[$inc]['all']['default_price_type'] == "percent") 
                     $all_custom_option_array[$inc]['all']['price'] = number_format((($product->getFinalPrice()*round($all_custom_option_array[$inc]['all']['price']*10,2)/10)/100),2);
                    else 
                      $all_custom_option_array[$inc]['all']['price'] = number_format($all_custom_option_array[$inc]['all']['price'],2);
                    

                    $all_custom_option_array[$inc]['all']['price'] = str_replace(",","",$all_custom_option_array[$inc]['all']['price']); 
                    $all_custom_option_array[$inc]['all']['price'] = strval(round($this->convert_currency($all_custom_option_array[$inc]['all']['price'],$basecurrencycode,$currentcurrencycode),2));

                    $all_custom_option_array[$inc]['custom_option_value_array'];
                    $inner_inc =0;

                    foreach($optionVal->getValues() as $valuesKey => $valuesVal):

                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['id'] = $valuesVal->getId();
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['title'] = $valuesVal->getTitle();
                        
                        $defaultcustomprice = str_replace(",","",($valuesVal->getPrice())); 
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = strval(round($this->convert_currency($defaultcustomprice,$basecurrencycode,$currentcurrencycode),2));
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price_type'] = $valuesVal->getPriceType();
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sku'] = $valuesVal->getSku();
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sort_order'] = $valuesVal->getSortOrder();
                        
                        if($valuesVal->getPriceType() == "percent"):

                            $defaultcustomprice = str_replace(",","", ($product->getFinalPrice())); 
                            $customproductprice = strval(round($this->convert_currency($defaultcustomprice,$basecurrencycode,$currentcurrencycode),2));
                            $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = str_replace(",","", round((floatval($customproductprice)  * floatval(round($valuesVal->getPrice(),1))/100),2));    
                        endif;
                        
                        $inner_inc++;
                    endforeach;

                $inc++;
                endforeach;

                
            else:
                $has_custom_options = false;
            endif;

          $addtionatt=$this->_getAditional();

 
            /*get confiogurable product attributes*/
            Mage::register('product', $product);
            Mage::helper('catalog/product')->setSkipSaleableCheck(true);

            $config_attributes = new Mage_Catalog_Block_Product_View_Type_Configurable;
            $condigurable_data = json_decode($config_attributes->getJsonConfig(),1);
            $baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
            $currentCurrency = $this->currency;
            $configurable = array();
            $childProducts = array();
            $new_array = array();
            foreach($condigurable_data['attributes'] as $key => $value){
                $configurable[] = $value;
                $childProducts[] = $value['options'];
            }
          
         //   print_r($value['options']); die(';s');
            $load_product_model = Mage::getModel('catalog/product');
            foreach ($childProducts as $key => $values) {
                    foreach ($values as $key => $valuess) {
                        foreach ($valuess['products'] as $key => $val) {
                                $load_product_data =$load_product_model->load($val);                
                            $new_array1['name'] = $load_product_data->getName ();
                            $finalPrice = ($load_product_data->getData('final_price'))?:$load_product_data->getData('special_price');
                        $new_array1['regular_price_with_tax'] = number_format($load_product_data->getPrice (), 2, '.', '');

                       /* $new_array1['final_price_with_tax'] = number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
                            Mage::helper('tax')->getPrice($load_product_data, $load_product_data->getFinalPrice(), 
                            true, null, null, null, null, false),*/
                          $new_array1['final_price_with_tax'] =  number_format ( Mage::helper ( 'directory' )
                            ->currencyConvert ( 
                            Mage::helper('tax')->getPrice($load_product_data, $finalPrice, 
                           true, null, null, null, null, false),
                            $baseCurrency, $currentCurrency ), 2, '.', '' );
                            $new_array1['id'] = $load_product_data->getId ();
                            $new_array[] =  $new_array1;
                        }  
                            
            } 
            
            }   
            //$finalPrice = ($product->getData('final_price'))?:$product->getData('special_price');
            $productdetail = array (
                    'entity_id' => $product->getId (),
                    'sku' => $product->getSku (),
                    'name' => $product->getName (),
                    'news_from_date' => $product->getNewsFromDate (),
                    'news_to_date' => $product->getNewsToDate (),
                    'special_from_date' => $product->getSpecialFromDate (),
                    'special_to_date' => $product->getSpecialToDate (),
                    'image_url' => Mage::helper('connector')-> Imageresize($product->getImage(),'product','500','500'),
                    'url_key' => $product->getProductUrl().'?shareid='.$product->getId(),
                    'is_in_stock' => $product->isAvailable (),
                    'has_custom_options' => $has_custom_options,
                   /* 'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getData('price'), $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )
                            ->currencyConvert ( 
                            Mage::helper('tax')->getPrice($product, $finalPrice, 
                            true, null, null, null, null, false),
                            $baseCurrency, $currentCurrency ), 2, '.', '' ),*/
                    'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )
                        ->currencyConvert ( 
                        Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
                        true, null, null, null, null, false),
                        $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'storeUrl' => $storeUrl,
                    'tier_price'=> $tier_prices,
                    'description' => $description,
                    'short_description'=>nl2br ($product->getShortDescription()),
                    'symbol' => Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
                    'weight'=>$product->getWeight(),
                    'review'=>$reviews,
                    'rating'=>$rating,
                    'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
                    'additional'=>$addtionatt,
                    'specialprice'=>number_format (Mage::helper('connector')->
                    getSpecialPriceProduct($product->getId ()), 2, '.', '' ),
                    /*'specialprice'=>number_format (($finalPrice <= $product->getData('special_price'))?$finalPrice : $product->getData('special_price'), 2, '.', '' )*/
            );

            if(count($all_custom_option_array))
                   $productdetail["custom_option"] = $all_custom_option_array;

            if(count($configurable))
                    $productdetail["configurable"] = $configurable;

            if(count($new_array))
                    $productdetail["childProducts"] = $new_array;
            
                    
            echo json_encode ( $productdetail );

        else:    
            $storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA); 
            $description =  nl2br ( $product->getDescription () );
            $description = str_replace("{{media url=\"",$storeUrl,$description);
            $description = str_replace("\"}}","",$description);
            

            $c_ops=array();

            if ($product->getData('has_options')):
                $has_custom_options = true;
                
                $all_custom_option_array = array();
                $attVal = $product->getOptions();
                $optStr = "";
                $inc=0;
                $has_custom_option = 0;
                foreach($attVal as $optionKey => $optionVal):

                    $has_custom_option = 1;
                    $all_custom_option_array[$inc]['custom_option_name']=$optionVal->getTitle();
                    $all_custom_option_array[$inc]['custom_option_id']=$optionVal->getId();
                    $all_custom_option_array[$inc]['custom_option_is_required']=$optionVal->getIsRequire();
                    $all_custom_option_array[$inc]['custom_option_type']=$optionVal->getType();
                    $all_custom_option_array[$inc]['sort_order'] = $optionVal->getSortOrder();
                    $all_custom_option_array[$inc]['all'] = $optionVal->getData();

                    if($all_custom_option_array[$inc]['all']['default_price_type'] == "percent")
                     $all_custom_option_array[$inc]['all']['price'] = number_format((($product->getFinalPrice()*round($all_custom_option_array[$inc]['all']['price']*10,2)/10)/100),2);
                    else
                      $all_custom_option_array[$inc]['all']['price'] = number_format($all_custom_option_array[$inc]['all']['price'],2);


                    $all_custom_option_array[$inc]['all']['price'] = str_replace(",","",$all_custom_option_array[$inc]['all']['price']); 
                    $all_custom_option_array[$inc]['all']['price'] = strval(round($this->convert_currency($all_custom_option_array[$inc]['all']['price'],$basecurrencycode,$currentcurrencycode),2));

                    $all_custom_option_array[$inc]['custom_option_value_array'];
                    $inner_inc =0;

                    foreach($optionVal->getValues() as $valuesKey => $valuesVal):
                     
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['id'] = $valuesVal->getId();
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['title'] = $valuesVal->getTitle();
                        $defaultcustomprice = str_replace(",","",($valuesVal->getPrice())); 
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = strval(round($this->convert_currency($defaultcustomprice,$basecurrencycode,$currentcurrencycode),2));
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price_type'] = $valuesVal->getPriceType();
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sku'] = $valuesVal->getSku();
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sort_order'] = $valuesVal->getSortOrder();
                        
                        if($valuesVal->getPriceType() == "percent"):
                            $defaultcustomprice = str_replace(",","", ($product->getFinalPrice())); 
                            $customproductprice = strval(round($this->convert_currency($defaultcustomprice,$basecurrencycode,$currentcurrencycode),2));
                            $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = str_replace(",","", round((floatval($customproductprice)  * floatval(round($valuesVal->getPrice(),1))/100),2));    
                            endif;
                        
                        $inner_inc++;
                    endforeach;
                    $inc++;

                endforeach;
            else:
                $has_custom_options = false;
            endif;

            $addtionatt=$this->_getAditional();
            $productdetail = array (
                    'entity_id' => $product->getId (),
                    'sku' => $product->getSku (),
                    'name' => $product->getName (),
                    'news_from_date' => $product->getNewsFromDate (),
                    'news_to_date' => $product->getNewsToDate (),
                    'special_from_date' => $product->getSpecialFromDate (),
                    'special_to_date' => $product->getSpecialToDate (),
                    'image_url' => Mage::helper('connector')-> Imageresize($product->getImage(),'product_main','500','500'),
                    'url_key' => $product->getProductUrl().'?shareid='.$product->getId(),
                    'is_in_stock' => $product->isAvailable (),
                    'has_custom_options' => $has_custom_options,
                    'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )
                            ->currencyConvert ( 
                            Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
                            true, null, null, null, null, false),
                            $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'storeUrl' => $storeUrl,
                    'description' => $description,
                    'short_description'=>nl2br ($product->getShortDescription()),
                    'symbol' => Mage::helper('connector')->getCurrencysymbolByCode($this->currency) ,
                    'weight'=>$product->getWeight(),
                    'tier_price'=> $tier_prices,
                    'qty'=>(int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty(),
                    'review'=>$reviews,
                    'rating'=>$rating,
                    'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
                    'additional'=>$addtionatt,
                   'specialprice'=>number_format (Mage::helper('connector')->getSpecialPriceProduct($product->getId ()), 2, '.', '' ),
            );
            if(count($all_custom_option_array))
                   $productdetail["custom_option"] = $all_custom_option_array;

            
                
            echo json_encode ( $productdetail );

        endif;
    }


	public function getPicListsAction() {
		$productId = ( int ) $this->getRequest ()->getParam ( 'product' );
		
		$_images = Mage::getModel ( 'catalog/product' )->load ( $productId );
		$media = $_images->getMediaGalleryImages();
		
		$images = array ();
		foreach ($media as $_image ) {

			$images [] = array (
						'url' => Mage::helper('connector')-> Imageresize($_image->getFile (),'product_main','500','500'),
						'thumbnail' => Mage::helper('connector')-> Imageresize($_image->getFile (),'thumbnail','100','100'),
						'position' => $_image->getPosition () 
				);
			
		}
		if(!sizeof($images) && $_images->getImage()):

			$images [] = array (
						'url' => Mage::helper('connector')-> Imageresize($_images->getImage(),'product_main','500','500'),
						'thumbnail' => Mage::helper('connector')-> Imageresize($_images->getImage(),'thumbnail','100','100'),
						'position' => 1 
					);
		endif;
		echo json_encode ( $images );
	}


	###get Rating and review summary
	public function getRatingAction() {
		 $productId = ( int ) $this->getRequest ()->getParam ( 'product' );
		 $reviews = Mage::getModel('review/review')
				->getResourceCollection()
				->addStoreFilter(Mage::app()->getStore()->getId())
				->addEntityFilter('product', $productId)
				->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
				->setDateOrder()
				->addRateVotes();				
				$avg = 0;
				$ratings = array();
				$rdetails=array();
				
				if (count($reviews) > 0) {
					foreach ($reviews->getItems() as $review) {
						$rdetails[]= array(
									'title'=>$review->getTitle(),
									'description'=>$review->getDetail(),
									'reviewby'=>$review->getNickname(),
							);
						foreach( $review->getRatingVotes() as $vote ) {							
							$ratings[] = $vote->getPercent();
						}
					}
					$avg = array_sum($ratings)/count($ratings);
				}
			$response['rating']=ceil($avg/20);
			$response['reviews']=$rdetails;

		echo json_encode ($response);
	}


	public function _getAditional(array $excludeAttr = array()) {
        $data = array ();
        $productId = ( int ) $this->getRequest ()->getParam ( 'productid' );
        $product = Mage::getModel ( "catalog/product" )->load ( $productId );
        $attributes = $product->getAttributes ();
        foreach ( $attributes as $attribute ) {

            if ($attribute->getIsVisibleOnFront () && ! in_array ( $attribute->getAttributeCode (), $excludeAttr )) {

                $value = $attribute->getFrontend ()->getValue ( $product );
                if ($attribute->getFrontendInput () == 'price' && is_string ( $value )) {
                    $value = Mage::app ()->getStore ()->convertPrice ( $value, true );
                }
                if (is_string ( $value ) && strlen ( $value )) {
                    $data [] = array (
                            'label' => $attribute->getStoreLabel (),
                            'value' => $value,
                            'code' => $attribute->getAttributeCode () 
                    );
                }
            }
        }
        
        return $data;
    }

	###Getting price range 




	public function _renderItemLabel($range, $value)
	{
		$storename='default';
		$store = Mage::app()->getStore();
		$fromPrice = ($value-1)*$range;
		$toPrice = $value*$range;

		return array($fromPrice, $toPrice);
	}

	public function _renderItemValue($range, $value)
	{
		$storename='default';
		$store = Mage::app()->getStore();
		$fromPrice = ($value-1)*$range;
		$toPrice = $value*$range;

		return $fromPrice.','. $toPrice;
	}


	/*search API*/
	public function searchAction() 
	{
		$searchstring = $this->getRequest ()->getParam ( 'search' );
		$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
		$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 10;
		$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';

		if($searchstring):

			$products = Mage::getModel('catalog/product')->getCollection();
			$products->addAttributeToSelect(array('name','entity_id','status','visibility'),'inner')
	  		    ->setPageSize ($limit)
				->addAttributeToFilter(array(
	                array('attribute'=>'name', 'like' => '%'.$searchstring.'%'),
	               array('attribute'=>'sku', 'like'  => '%'.$searchstring.'%'),
	            ))
			    ->addAttributeToFilter ( 'status', 1 )
				->addAttributeToFilter ( 'visibility', array ('neq' => 1 ) )
	            ->setPage ( $page, $limit );

	        $productlist = array ();
	        $baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
	        $currentCurrency = $this->currency;
	        foreach ( $products as $product ) {
	               $product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
	            	$rating = Mage::getModel('rating/rating')->getEntitySummary($product->getId());
                    $rating_final = ($rating->getSum()/$rating->getCount())/20;
	        $productPrice = $product->getPrice();
            $block = new Mage_Catalog_Block_Product_Price();

            $product_tier_prices = $block->getTierPrices($product);
            if(count($product_tier_prices) > 0){
                $product_tier_prices = (object)$product_tier_prices;
                $tier_prices = '';
                foreach ($product_tier_prices as $_index => $_price){
                   
                    $product_qty = $_price['price_qty'];
                    $tier_price = number_format($_price['price'], 2, '.' ,'');
                    $discount =  ceil(100 - ((100 / $productPrice) * $_price['price']));
                    $symbol = Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
                    $tier =  'Buy '.$product_qty .' for '.$symbol.$tier_price .' each and save '. $discount .'%';

                $tier_prices .=  '<span>' .$tier.'</span>';
                }
            }      
	          
	            $productlist [] = array (
                    'entity_id' => $product->getId (),
                    'sku' => $product->getSku (),
                    'name' => $product->getName (),
                    'news_from_date' => $product->getNewsFromDate (),
                    'news_to_date' => $product->getNewsToDate (),
                    'special_from_date' => $product->getSpecialFromDate (),
                    'special_to_date' => $product->getSpecialToDate (),
                    'image_url' => Mage::helper('connector')-> Imageresize($product->getImage(),'product','300','300'),
                    'url_key' => $product->getProductUrl (),
                    'tier'=> $tier_prices,
                    'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
							Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
							true, null, null, null, null, false),
							$baseCurrency, $currentCurrency ), 2, '.', '' ),
                    'symbol'=> Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
                    'qty'=>(int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty(),
                    'rating' => $rating_final,
                    'product_type'=>$product->getTypeId(),
                    'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
                    'categoryid' =>  end($product->getCategoryIds()),
                    'specialprice'=>number_format (Mage::helper('connector')->getSpecialPriceProduct($product->getId ()), 2, '.', '' ),
	            );
	        }

			if(sizeof($productlist))
				echo json_encode($productlist);
			else
				echo json_encode(array('status'=>'error','message'=> $this->__('There are no products matching the selection')));
		else:
				echo json_encode(array('status'=>'error','message'=> $this->__('Search string is required')));
		endif;
	}


    public function getpricerange($maincategoryId) {
        $pricerange =array();   
        $layer = Mage::getModel('catalog/layer');
        $category = Mage::getModel('catalog/category')->load($maincategoryId);
        if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
        }
        $r=Mage::getModel('catalog/layer_filter_price')
        ->setLayer($layer);

        $range = $r->getPriceRange();
        $dbRanges = $r->getRangeItemCounts($range);
        $data = array();

        foreach ($dbRanges as $index=>$count) {
        $data[] = array(
        'label' => $this->_renderItemLabel($range, $index),
        'value' => $this->_renderItemValue($range, $index),
        'count' => $count,
        );
        }
        return $data;
    }



	public function getFiltersAction(){

          $catId = Mage::app()->getRequest()->getParam('categoryid');
      
        if(empty($catId)):
            echo  json_encode(array('status'=> false,'message'=>'Category id field is not empty'));
        else:
        try{
            $json = array();
            $layer = Mage::getModel("catalog/layer");
            $category = Mage::getModel("catalog/category")->load($catId); // 3rd Category
            $categories = $category->getChildrenCategories();
            $counts = array();
            foreach ($categories as $key => $value) {
                $counts[] =array('subcategory'=> $value->getName() , 'url'=> $value->getUrl() , 'catId'=> $value->getId());

            }

            $json['category'] = $counts?$counts:null;

            
            $layer->setCurrentCategory($category);
            $attributes = $layer->getFilterableAttributes();
            foreach ($attributes as $attribute) {
                $filterBlockName = 'catalog/layer_filter_attribute';
                $result = Mage::app()->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
                foreach($result->getItems() as $option) {
                    $count[] = array('code' => $option->getLabel(),'label' => $option->getValue());
                }

                if($attribute->getAttributeCode() == 'price')
                        $filters[] = array('code' => $attribute->getAttributeCode(), 'label'=>$attribute->getFrontendLabel(),'value'=>$this->getpricerange($catId));
                
                if($count!=null AND $attribute->getAttributeCode() != 'price')
                        $filters[] = array('code'=>ucfirst($attribute->getAttributeCode()), 'label'=>$attribute->getFrontendLabel(),'value'=>$count);
                /*if($count!=null){
                    $json[] = array('code'=>ucfirst($attribute->getAttributeCode()), 'label'=>$attribute->getFrontendLabel(),'value'=>$count,'category'=>$counts,'price'=>$array);
                }*/
                unset($count,$array);
            }
            $json['filters'] = $filters;
        }
        catch (Exception $e) {
            $json = array('status' => false, 'message' => $e->getMessage());
        }
        echo json_encode(array($json));
        endif;
     }

	/*getFilter API*/


	/*Set rating API*/
	/*
	 URL : baseurl/restapi/products/setRating
	 Name : setRating
	 Method : GET
	 Input Data : Parameters : rating:{"product_Id":"Id of product","customer_Id":"Customer Id","short_description":"Title of Review"
			"description":"review description","name":"Customer name added in review box","rating_options":{"1":"4","2":"3"}}
			}	
		
	 Response : JSON
	*/
	public function setRatingAction(){		

		$rating = json_decode($this->getRequest ()->getParam('rating'),1);

		$rating_points = json_decode($rating['rating_options'], true); 
		$review = Mage::getModel('review/review');

			$review->setEntityPkValue($rating['product_Id']);
			$review->setTitle($rating['short_description']);
			$review->setDetail($rating['description']);
			$review->setEntityId(1); 
			$review->setStoreId($this->storeId);  
			$review->setTypeId(3);   
			$review->setStatusId(1);
			$review->setCustomerId($rating['customer_Id']?:NULL);
			$review->setNickname($rating['name']);
			$review->setReviewId($review->getId());
			$review->setStores(array($this->storeId));
			$review->save();				
			
			
			foreach($rating_points as $rating_id => $option_id):
				try {
					$_rating = Mage::getModel('rating/rating')
						->setRatingId($rating_id)
						->setReviewId($review->getId())
						->addOptionVote($option_id,$rating['product_Id']);
				} catch (Exception $e) {
					echo json_encode(array('status'=>'error','message'=>$e->getMessage()));
					exit;
				}
			endforeach;
			
			$updateReview = Mage::getModel('review/review')->load($review->getId());
			$updateReview->setCreatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));				
			$updateReview->save();

			echo json_encode(array('status'=>'success','message'=>'Review added sucessfully.'));
			exit;
	}
	public function  getrelatedProductAction(){ 

		$product_id =$this->getRequest ()->getParam ( 'productid' );
		$productdetail = array ();
		$relArray = array();
		$RelProduct = Mage::getModel('catalog/product')->load($product_id)->getRelatedProductIds();
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
  		$currentCurrency = $this->currency;
		foreach ($RelProduct as $id) {
			$collection = Mage::getModel('catalog/product');
			$products = $collection->load($id);
		    $product = $products->getData();

			if($products->getTypeId() == "configurable")
						$qty = Mage::helper('connector')->getProductStockInfoById($products->getId());
					else
						$qty  = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($products->getId())->getQty();

			$relArray['entity_id'] = $product['entity_id'];
			$relArray['sku'] =  $product['sku'];
			$relArray['name'] = $product['name'];
			$relArray['image']=  Mage::helper('connector')-> Imageresize($products->getImage(),'product','300','300');
			$relArray['url_key'] = $products->getProductUrl ();
			$relArray['regular_price_with_tax'] = number_format ( Mage::helper ( 'directory' )->currencyConvert ( $products->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' );
			$relArray['final_price_with_tax'] = number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
							Mage::helper('tax')->getPrice($products, $products->getFinalPrice(), 
							true, null, null, null, null, false),
							$baseCurrency, $currentCurrency ), 2, '.', '' );
			$relArray['symbol']=Mage::helper('connector')->getCurrencysymbolByCode($this->currency);
			$relArray['qty']=$qty;
			$relArray['wishlist'] =  Mage::helper('connector')->check_wishlist($products->getId ());
			$relArray['specialprice']=number_format (Mage::helper('connector')->getSpecialPriceProduct($products->getId ()), 2, '.', '' );

     		array_push($productdetail, $relArray);
		}
		echo json_encode($productdetail);
	}

    public function getshortbyListingAction(){
        $category_id = $this->getRequest ()->getParam ( 'category_id' );
        if($category_id) {     
            $attributes  = Mage::getModel('catalog/category')->load($category_id)->getAvailableSortBy() ;
            if($attributes){
                $attributeArray = array();
                foreach($attributes as $attribute) {
                    $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
                                ->setCodeFilter($attribute)
                                ->getFirstItem();
                    array_push($attributeArray, array($attribute => $attributeInfo->getData('frontend_label')));
                }
                
                  echo  json_encode($attributeArray);
            } else {
                 $attributeArray = array();
                foreach (Mage::getModel('catalog/config')->getAttributeUsedForSortByArray() as $key => $value) {
                    array_push($attributeArray, array($key => $value));
 
                }
                 echo  json_encode($attributeArray);
            }
        }else{
            echo  json_encode(array());
        }
    }
}