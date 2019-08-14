<?php
class Mss_Connector_IndexController extends Mage_Core_Controller_Front_Action {

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


	public function CategoryListingAction()
	{
		
		echo json_encode($this->getCategoryTree(3));
		exit;

	}

	protected function getCategoryTree($recursionLevel)
	{
		$parent = Mage::app()->getStore($this->storeId)->getRootCategoryId();    
		$tree = Mage::getResourceModel('catalog/category_tree');
			  
    	$nodes = $tree->loadNode($parent)
			->loadChildren()
			->getChildren();
	    	$tree->addCollectionData(null, false, $parent,true,false);
			$categoryTreeData = array();
			$category_model = Mage::getModel('catalog/category');
			foreach ($nodes as $node) {
				if($node->getIsActive() && $category_model->load($node->getId())->getIncludeInMenu()) 
					$categoryTreeData[] = $this->getNodeChildrenData($node);
        	}
		    return $categoryTreeData;
	}

	protected function getNodeChildrenData(Varien_Data_Tree_Node $node)
	{
		

		$data = array(
			'id' => $node->getData('entity_id'),
			'title' => $node->getData('name'),
			'url'   => $node->getData('url_key'),
		);

		foreach ($node->getChildren() as $childNode) {
			

			if (!array_key_exists('children', $data)) {
				$data['children'] = array();
			}
			if($childNode->getIsActive())
				$data['children'][] = $this->getNodeChildrenData($childNode);
		}
		return $data;
	}

	public function getSubcategoryAction()
	{


		   $categoryid = ($this->getRequest ()->getParam ( 'categoryid' )) ? ($this->getRequest ()->getParam ( 'categoryid' )) : 2;

		   /*check for cache*/
			Mage::helper('connector')->checkcache($categoryid,'1');

		   $_categorylist = array ();
				$children = Mage::getModel('catalog/category')->getCategories($categoryid);


				foreach ($children as $_category) {
					$catimg=Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getImageUrl ();
						$thumbimg=Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getThumbnailUrl ();
						if(!$thumbimg):
							$thumbimg=Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/default/small_image.jpg';
						endif;
						if(!$catimg):
							$catimg=Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/default/small_image.jpg';
						endif;
					$_categorylist [] = array (
								'category_id' => $_category->getId (),
								'name' => $_category->getName (),
								'is_active' => $_category->getIsActive (),
								'position ' => $_category->getPosition (),
								'level ' => $_category->getLevel (),
								'url_key' => Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getUrlPath (),
								'thumbnail_url' => $thumbimg,
								'image_url' => $catimg,
								
								);
				}
				if(!sizeof($_categorylist))
					$_categorylist=array("status"=>"error");

			/*create new cache*/
			Mage::helper('connector')->createNewcache($categoryid,'1',json_encode($_categorylist));

		echo json_encode($_categorylist);
	}


	public function indexAction() {
		Mage::app ()->cleanCache ();
		$cmd = $this->getRequest ()->getParam ( 'cmd' );
		if (!Zend_Validate::is($cmd, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('filter field should not be empty')));
						exit;
		endif;
		switch ($cmd) {
			case 'menu' : 

				/*check for cache*/
			    Mage::helper('connector')->checkcache('menu','1');

				$_helper = Mage::helper ( 'catalog/category' );
				$_categories = $_helper->getStoreCategories ();
				$_categorylist = array ();
				if (count ( $_categories ) > 0) {
					foreach ( $_categories as $_category ) {
						$_helper->getCategoryUrl ( $_category );
						# if category have not image then set placeholder image
						$catimg=Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getImageUrl ();
						$thumbimg=Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getThumbnailUrl ();
						if(!$thumbimg):
							$thumbimg=Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/default/small_image.jpg';
						endif;
						if(!$catimg):
							$catimg=Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/default/small_image.jpg';
						endif;
						$_categorylist [] = array (
								'category_id' => $_category->getId (),
								'name' => $_category->getName (),
								'is_active' => $_category->getIsActive (),
								'position ' => $_category->getPosition (),
								'level ' => $_category->getLevel (),
								'url_key' => Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getUrlPath (),
								'thumbnail_url' => $thumbimg,
								'image_url' => $catimg,
								'children' => Mage::getModel ( 'catalog/category' )->load ( $_category->getId () )->getAllChildren () 
						);
					

					}
				}
				else
				{
					
					$_categorylist = array('status'=>'error','message'=> $this->__('No Record Found'));
					exit;
				}

				/*create new cache*/
			    Mage::helper('connector')->createNewcache('menu','1',json_encode($_categorylist));

				echo json_encode ( $_categorylist );
				break;
			case 'catalog' : // OK
				//min=29022&max=95032&ajaxcatalog=true&dir=asc&order=weight&filter
				$categoryid = $this->getRequest ()->getParam ( 'categoryid' );

				if (!Zend_Validate::is($categoryid, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('category id should not be empty')));
						exit;
				endif;

				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 10;
				$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
				$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
				
				$category = Mage::getModel ( 'catalog/category' )->load ( $categoryid );


				if (!$category->getId()):
						echo json_encode(array('status'=>'error','message'=> $this->__('category id does not exist')));
						exit;
				endif;

				$model = Mage::getModel ( 'catalog/product' ); // getting product model
				$collection = $category->getProductCollection ()->addAttributeToFilter ( 'status', 1 )
							->addAttributeToFilter ( 'visibility', array ('neq' => 1 ) );

				$price_filter = array();

				/*filter added*/
				if($this->getRequest ()->getParam ( 'filter' )){
					$filters = json_decode($this->getRequest ()->getParam ( 'filter' ),1);

					
					foreach($filters as $key => $filter):
						
						if(sizeof($filter)):

							if($key == 'price'):
								
								$price = explode(',',$filter[0]);
								$price_filter = array('gt'=>$price['0'],'lt'=>$price['1']);
								$collection = $collection->addAttributeToFilter ( 'price', array ('gt' => $price['0'] ) );
								$collection = $collection->addAttributeToFilter ( 'price', array ('lt' => $price['1']) );
							else:
								$collection = $collection->addAttributeToFilter ( $key, array('in' => $filter) );
							endif;
						endif;
					endforeach;
				}
				/*filter added*/


				if($this->getRequest ()->getParam ( 'min' )){
					$collection=$collection->addAttributeToFilter ( 'price', array ('gt' => $this->getRequest ()->getParam ( 'min' ) ) );
				}
				if($this->getRequest ()->getParam ( 'max' )){
					$collection=$collection->addAttributeToFilter ( 'price', array ('lt' => $this->getRequest ()->getParam ( 'max' ) ) );
				}

				$collection=$collection->addAttributeToSort ( $order, $dir )/* ->setPage ( $page, $limit ) */;
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				 $count=$collection->getSize();

				if(!$count):
					
					echo json_encode(array('status'=>'error','message'=> $this->__('No Record Found')));
						exit;
				endif;

				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$productlist = $this->getProductlist ( $collection, 'catalog',$price_filter );
				}

				if(sizeof($productlist))
					echo json_encode ( $productlist );
				else
					echo "[]";

				
				break;
			case 'coming_soon' : 

				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
				$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
		;
				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				$tdatomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 3, date ( 'y' ) );
				$tdaTomorrow = date ( 'm/d/y', $tdatomorrow );
				$_productCollection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$_productCollection->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'visibility', array (
						'neq' => 1 
				) )->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'special_price', array (
						'neq' => 0 
				) )->addAttributeToFilter ( 'special_from_date', array (
						'date' => true,
						'to' => $dateTomorrow 
				) )->addAttributeToFilter ( array (
						array (
								'attribute' => 'special_to_date',
								'date' => true,
								'from' => $tdaTomorrow 
						),
						array (
								'attribute' => 'special_to_date',
								'null' => 1 
						) 
				) )
				->addAttributeToFilter('visibility',array('eq'=>4))
				->addAttributeToSort ( $order, $dir )/* ->setPage ( $page, $limit ) */;
				$pages = $_productCollection->setPageSize ( $limit )->getLastPageNumber ();
				 $count=$collection->getSize();
				if(!$count):
					
					echo json_encode(array('status'=>'error','message'=> $this->__('No Record Found')));
						exit;
				endif;
				if ($page <= $pages) {
					$_productCollection->setPage ( $page, $limit );
					$products = $_productCollection->getItems ();
					$productlist = $this->getProductlist ( $products );
				}
			
				if(sizeof($productlist))
					echo json_encode ( $productlist );
				else
					echo "[]";
				
				break;
			case 'best_seller' :

				$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
				$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';                 // ------------------------------Home Pre Specials BEGIN------------------------------//
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$_products = Mage::getModel ( 'catalog/product' )->getCollection ()->addAttributeToSelect ('*')
				->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'news_from_date', array (
						'or' => array (
								0 => array (
										'date' => true,
										'to' => $todayDate 
								),
								1 => array (
										'is' => new Zend_Db_Expr ( 'null' ) 
								) 
						) 
				), 'left' )->addAttributeToFilter ( 'news_to_date', array (
						'or' => array (
								0 => array (
										'date' => true,
										'from' => $todayDate 
								),
								1 => array (
										'is' => new Zend_Db_Expr ( 'null' ) 
								) 
						) 
				), 'left' )->addAttributeToFilter ( array (
						array (
								'attribute' => 'news_from_date',
								'is' => new Zend_Db_Expr ( 'not null' ) 
						),
						array (
								'attribute' => 'news_to_date',
								'is' => new Zend_Db_Expr ( 'not null' ) 
						) 
				) )->addAttributeToFilter ( 'visibility', array (
						'in' => array (
								2,
								4 
						) 
				) )
				->addAttributeToFilter('visibility',array('eq'=>4))
				->addAttributeToSort ( 'news_from_date', 'desc' )->addAttributeToSort ( $order, $dir )/* ->setPage ( $page, $limit ) */;
				$pages = $_products->setPageSize ( $limit )->getLastPageNumber ();
				 $count=$collection->getSize();
				if(!$count):
					
					echo json_encode(array('status'=>'error','message'=> $this->__('No Record Found')));
						exit;
				endif;
				if ($page <= $pages) {
					$_products->setPage ( $page, $limit );
					$products = $_products->getItems ();
					$productlist = $this->getProductlist ( $products );
				}
				
				if(sizeof($productlist))
					echo json_encode ( $productlist );
				else
					echo "[]";
				
				break;
			case 'daily_sale' :
								
				$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
				$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				
				$collection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$collection->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'visibility', array (

						'neq' => 1 

				) )->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'special_price', array (
						'neq' => "0" 
				) )->addAttributeToFilter ( 'special_from_date', array (
						'date' => true,
						'to' => $todayDate 
				) )->addAttributeToFilter ( array (
						array (
								'attribute' => 'special_to_date',
								'date' => true,
								'from' => $dateTomorrow 
						),
						array (
								'attribute' => 'special_to_date',
								'null' => 1 
						) 
				) )
				->addAttributeToFilter('visibility',array('eq'=>4))
				->addAttributeToSort ( $order, $dir );
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				 $count=$collection->getSize();
				if(!$count):
					echo json_encode(array('status'=>'error','message'=> $this->__('No Record Found')));
						exit;
				endif;
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$products = $collection->getItems ();
					$productlist = $this->getProductlist ( $products );
				}
				if(sizeof($productlist))
					echo json_encode ( $productlist );
				else
					echo "[]";
				
				break;
				case 'new_product' :

				$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

				$_productCollection = Mage::getResourceModel('catalog/product_collection')
						->addAttributeToSelect('*')
						->addAttributeToFilter('status',array('eq'=>1))
						->addAttributeToFilter('visibility',array('eq'=>4))
						->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
						->addAttributeToFilter('news_to_date', array('or'=> array(
						0 => array('date' => true, 'from' => $todayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
						), 'left');

			$now = date('Y-m-d');
			$newsFrom= substr($_productCollection->getData('news_from_date'),0,10);
			$newsTo=  substr($_productCollection->getData('news_to_date'),0,10);

			if(!$_productCollection->count()):
				echo json_encode(array('status'=>'error','message'=> $this->__('There are no products matching the selection')));
			 else:
				if ($now>=$newsFrom && $now<=$newsTo)$i=0;
				 
				 $productlist = array ();
			$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
			$currentCurrency = $this->currency;
			foreach ( $_productCollection as $product ) {
			
				$product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
				$rating = Mage::getModel('rating/rating')->getEntitySummary($product->getId());
				$rating_final = ($rating->getSum()/$rating->getCount())/20;
				
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
					'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
					'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
							Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
							true, null, null, null, null, false),
							$baseCurrency, $currentCurrency ), 2, '.', '' ),
					'symbol'=> Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
					'qty'=>(int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty(),
					'rating' => $rating_final,
					'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
					'specialprice'=>Mage::helper('connector')->getSpecialPriceByProductId($product->getId ()),

			);
		}
		
		if(sizeof($productlist))
					echo json_encode ( $productlist );
				else
					echo "[]";
				
			endif;
		break;

		default :
			echo 'Parameters are invalid';
			
		break;
		}
	}

	public function getStaticBlockAction(){
		$block = ($this->getRequest ()->getParam ( 'block' )) ? ($this->getRequest ()->getParam ( 'block' )) : 'footer_links';
		echo $this->getLayout()->createBlock('cms/block')->setBlockId($block)->toHtml();
	}

	public function getProductlist($products, $mod = 'product',$price_filter = array()) {

		$productlist = array ();
		$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
		$currentCurrency = $this->currency;

		foreach($products as $product):

			if($mod == 'catalog'):
				$product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
				$rating = Mage::getModel('rating/rating')->getEntitySummary($product->getId());
				$rating_final = ($rating->getSum()/$rating->getCount())/20;
			endif;	
			
			if($product->getTypeId() == "configurable")
						$qty = Mage::helper('connector')->getProductStockInfoById($product->getId());
			else
				$qty  = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();

			if(sizeof($price_filter)):

				if($product->getFinalPrice() > $price_filter['0'] AND $product->getFinalPrice() > $price_filter['1']):
				
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
						'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
						'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
										Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
										true, null, null, null, null, false),
										$baseCurrency, $currentCurrency ), 2, '.', '' ),
						'symbol'=> Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
						'qty'=>$qty,
						'rating' => $rating_final,
						'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
						'specialprice'=>Mage::helper('connector')->getSpecialPriceByProductId($product->getId ()),
					);
				endif;
			else:
			
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
					'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
					'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
							Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
							true, null, null, null, null, false),
							$baseCurrency, $currentCurrency ), 2, '.', '' ),
					'symbol'=> Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
					'qty'=>$qty,
					'rating' => $rating_final,
					'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
					'specialprice'=>Mage::helper('connector')->getSpecialPriceByProductId($product->getId ()),
				);
			endif;
		endforeach;

		return $productlist;
	}

	# start dashboard api to return the new products sale products
	public function getdashboardAction()
	{

		/*check for cache*/
		Mage::helper('connector')->checkcache('dashboard','1');

		
		$top_products=$this->getBestsellerProducts();
		$new_pro_list=$this->getnewproducts();
		$sale_pro_list=$this->getsaleproducts();
		$topproductslist=array(
					'Title'=>'Top Products',
					'count'=>count($top_products),
					'products'=>$top_products

			);
		$newproductslist=array(
					'Title'=>'New Products',
					'count'=>count($new_pro_list),
					'products'=>$new_pro_list

			);

		$saleproductslist=array(
					'Title'=>'Sale Products',
					'count'=>count($sale_pro_list),
					'products'=>$sale_pro_list

			);
		/*create new cache*/
		Mage::helper('connector')->createNewcache('dashboard','1',json_encode(array($topproductslist,$newproductslist,$saleproductslist)));

		echo json_encode(array($topproductslist,$newproductslist,$saleproductslist)); die;
	}

	public function getBestsellerProducts()
	{
		$products = Mage::getResourceModel('reports/product_collection')
		->addOrderedQty()
		->addAttributeToSelect('*')
		->addAttributeToSelect(array('name', 'price', 'small_image'))
		->addAttributeToFilter('visibility',array('eq'=>4))
		->setStoreId($this->storeId)
		->addStoreFilter($this->storeId)
		->addViewsCount();

		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
			
 		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
		$products->setPageSize(5)->setCurPage(1);
			//->getLastPageNumber ();
				$new_productlist = array ();
				$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
				$currentCurrency = $this->currency;
				foreach ( $products as $product ) {

						if(!$product['cat_index_position']) continue;
					
						$product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
						$rating = Mage::getModel('rating/rating')->getEntitySummary($product->getId());
						$rating_final = ($rating->getSum()/$rating->getCount())/20;

					if($product->getTypeId() == "configurable")
						$qty = Mage::helper('connector')->getProductStockInfoById($product->getId());
					else
						$qty  = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();

					$new_productlist [] = array (
							'entity_id' => $product->getId (),
							'sku' => $product->getSku (),
							'name' => $product->getName (),
							'news_from_date' => $product->getNewsFromDate (),
							'news_to_date' => $product->getNewsToDate (),
							'special_from_date' => $product->getSpecialFromDate (),
							'special_to_date' => $product->getSpecialToDate (),
							'image_url' => Mage::helper('connector')-> Imageresize($product->getImage(),'product','300','300'),
							'url_key' => $product->getProductUrl (),
							'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
							'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
											Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
											true, null, null, null, null, false),
											$baseCurrency, $currentCurrency ), 2, '.', '' ),
							'symbol'=>Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
							'qty'=>$qty,
							'rating' => $rating_final,
							'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
							'specialprice'=>Mage::helper('connector')->getSpecialPriceByProductId($product->getId ()),
					);
				}
			return $new_productlist;
		}
	public function getnewproducts()
	{
		 # get New Products start
		
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$_rootcatID = Mage::app($this->storeId)->getStore()->getRootCategoryId();

		$_productCollection = Mage::getResourceModel('catalog/product_collection')
				->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
				->addAttributeToFilter('category_id', array('in' => $_rootcatID))
				->addAttributeToSelect('*')
				->addAttributeToFilter('status',array('eq'=>1))
				->setPageSize (5)
				->addAttributeToFilter('visibility',array('eq'=>4))
				->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
				->addAttributeToFilter('news_to_date', array('or'=> array(
							0 => array('date' => true, 'from' => $todayDate),
							1 => array('is' => new Zend_Db_Expr('null')))
							), 'left')
				->setStoreId($this->storeId)
				;


			$now = date('Y-m-d');
			$newsFrom= substr($_productCollection->getData('news_from_date'),0,10);
			$newsTo=  substr($_productCollection->getData('news_to_date'),0,10);

			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);
			
			if(!$_productCollection->count()):
					return  $new_productlist = array ();
			else:
				if ($now>=$newsFrom && $now<=$newsTo)$i=0;
				 
			$new_productlist = array ();
			$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();
			$currentCurrency = $this->currency;
			foreach ( $_productCollection as $product ) {
				
					$product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );
					
					$rating = Mage::getModel('rating/rating')->getEntitySummary($product->getId());
					$rating_final = ($rating->getSum()/$rating->getCount())/20;

				if($product->getTypeId() == "configurable")
					$qty = Mage::helper('connector')->getProductStockInfoById($product->getId());
				else
					$qty  = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();


				$new_productlist [] = array (
						'entity_id' => $product->getId (),
						'sku' => $product->getSku (),
						'name' => $product->getName (),
						'news_from_date' => $product->getNewsFromDate (),
						'news_to_date' => $product->getNewsToDate (),
						'special_from_date' => $product->getSpecialFromDate (),
						'special_to_date' => $product->getSpecialToDate (),
						'image_url' => Mage::helper('connector')-> Imageresize($product->getImage(),'product','300','300'),
						'url_key' => $product->getProductUrl (),
						'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),
						'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( 
							Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 
							true, null, null, null, null, false),
							$baseCurrency, $currentCurrency ), 2, '.', '' ),
						'symbol'=>Mage::helper('connector')->getCurrencysymbolByCode($this->currency),
						'qty'=>$qty,
						'rating' => $rating_final,
						'wishlist' =>  Mage::helper('connector')->check_wishlist($product->getId ()),
						'specialprice'=>Mage::helper('connector')->getSpecialPriceByProductId($product->getId ()),
				);
			}
	
			return $new_productlist;
			
		endif;

	}






	public function getsaleproducts()
	{
				  
				$order = ($this->getRequest ()->getParam ( 'order' )) ? ($this->getRequest ()->getParam ( 'order' )) : 'entity_id';
				$dir = ($this->getRequest ()->getParam ( 'dir' )) ? ($this->getRequest ()->getParam ( 'dir' )) : 'desc';
				$page = ($this->getRequest ()->getParam ( 'page' )) ? ($this->getRequest ()->getParam ( 'page' )) : 1;
				$limit = ($this->getRequest ()->getParam ( 'limit' )) ? ($this->getRequest ()->getParam ( 'limit' )) : 5;
				$todayDate = Mage::app ()->getLocale ()->date ()->toString ( Varien_Date::DATETIME_INTERNAL_FORMAT );
				$tomorrow = mktime ( 0, 0, 0, date ( 'm' ), date ( 'd' ) + 1, date ( 'y' ) );
				$dateTomorrow = date ( 'm/d/y', $tomorrow );
				
				$_rootcatID = Mage::app($this->storeId)->getStore()->getRootCategoryId();

				$collection = Mage::getModel ( 'catalog/product' )->getCollection ();
				$collection->addAttributeToSelect ( '*' )->addAttributeToFilter ( 'visibility', array (

						'neq' => 1 

				) )->addAttributeToFilter ( 'status', 1 )->addAttributeToFilter ( 'special_price', array (
						'neq' => "0" 
				) )->addAttributeToFilter ( 'special_from_date', array (
						'date' => true,
						'to' => $todayDate 
				) )->addAttributeToFilter ( array (
						array (
								'attribute' => 'special_to_date',
								'date' => true,
								'from' => $dateTomorrow 
						),
						array (
								'attribute' => 'special_to_date',
								'null' => 1 
						) 
				) )

				->joinField('category_id','catalog/category_product','category_id','product_id=entity_id',null,'left')
				->addAttributeToFilter('category_id', array('in' => $_rootcatID))

				->setStoreId($this->storeId)
				->addAttributeToFilter('visibility',array('eq'=>4))
				->addAttributeToSort ( $order, $dir );
				$pages = $collection->setPageSize ( $limit )->getLastPageNumber ();
				 $count=$collection->getSize();
				if(!$count):
						return array();
				endif;
				if ($page <= $pages) {
					$collection->setPage ( $page, $limit );
					$products = $collection->getItems ();
					$productlist = $this->getProductlist ( $products );
				}
				return $productlist;

	}
} 
