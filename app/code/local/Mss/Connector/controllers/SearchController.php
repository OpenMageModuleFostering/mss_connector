<?php

/**

 * Catalog Search Controller

 */

class Mss_Connector_SearchController extends Mage_Core_Controller_Front_Action {
	public function _construct(){

		header('content-type: application/json; charset=utf-8');
		header("access-control-allow-origin: *");
		Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
		parent::_construct();
		
	}

	protected function _getSession() {

		return Mage::getSingleton ( 'catalog/session' );

	}

	public function indexAction() {

		$query = Mage::helper ( 'catalogsearch' )->getQuery ();

		/* @var $query Mage_CatalogSearch_Model_Query */

		$query->setStoreId ( Mage::app ()->getStore ()->getId () );

		
		if ($query->getQueryText () != '') {

			if (Mage::helper ( 'catalogsearch' )->isMinQueryLength ()) {

				$query->setId ( 0 )->setIsActive ( 1 )->setIsProcessed ( 1 );

			} else {

				if ($query->getId ()) {

					$query->setPopularity ( $query->getPopularity () + 1 );

				} else {

					$query->setPopularity ( 1 );

				}

				

				if ($query->getRedirect ()) {

					$query->save ();

					$this->getResponse ()->setRedirect ( $query->getRedirect () );

					return;

				} else {

					$query->prepare ();

				}

			}

			

			Mage::helper ( 'catalogsearch' )->checkNotes ();

		

			$collection = $query->getResultCollection ();

			$i = 1;

			$baseCurrency = Mage::app ()->getStore ()->getBaseCurrency ()->getCode ();

		    $currentCurrency = Mage::app ()->getStore ()->getCurrentCurrencyCode ();

			foreach($collection as $product){

			    $product = Mage::getModel ( 'catalog/product' )->load (  $product->getId () );

			    $productlist [] = array (

        			'entity_id' => $product->getId (),

        			'sku' => $product->getSku (),

        			'name' => $product->getName (),

        			'news_from_date' => $product->getNewsFromDate (),

        			'news_to_date' => $product->getNewsToDate (),

        			'special_from_date' => $product->getSpecialFromDate (),

        			'special_to_date' => $product->getSpecialToDate (),

        			'image_url' => $product->getImageUrl (),

        			'url_key' => $product->getProductUrl (),

        			'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),

        			'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),

        			'symbol' => Mage::app ()->getLocale ()->currency ( Mage::app ()->getStore ()->getCurrentCurrencyCode () )->getSymbol ()

    			);

    			$i ++;

			}

			echo json_encode($productlist);

			if (! Mage::helper ( 'catalogsearch' )->isMinQueryLength ()) {

				$query->save ();

			}

		} else {


		}

	}

	public function testAction() {

		$query = Mage::helper ( 'catalogSearch' )->getQuery ();

		$searcher = Mage::getSingleton ( 'catalogsearch/advanced' )->addFilters ( array (

				'name' => $query->getQueryText (),

				'description' => $query->getQueryText () 

		) );

		// $obj = new stdClass ();

		// $obj->query = $query->getQueryText ();

		// $obj->results = $searcher->getProductCollection (); // nothing returned

		$result = $searcher->getProductCollection()->getData()/* ->getItems () */;

		//$mod = Mage::getModel ( 'catalog/product' );

		//echo $result;

		foreach ( $result as $product ) {

			//var_dump ( $product);

			// $product = Mage::getModel ( 'catalog/product' )->load ( $product ['entity_id'] );

			// $productlist [] = array (

			// 'entity_id' => $product->getId (),

			// 'sku' => $product->getSku (),

			// 'name' => $product->getName (),

			// 'news_from_date' => $product->getNewsFromDate (),

			// 'news_to_date' => $product->getNewsToDate (),

			// 'special_from_date' => $product->getSpecialFromDate (),

			// 'special_to_date' => $product->getSpecialToDate (),

			// 'image_url' => $product->getImageUrl (),

			// 'url_key' => $product->getProductUrl (),

			// 'regular_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),

			// 'final_price_with_tax' => number_format ( Mage::helper ( 'directory' )->currencyConvert ( $product->getSpecialPrice (), $baseCurrency, $currentCurrency ), 2, '.', '' ),

			// 'symbol' => Mage::app ()->getLocale ()->currency ( Mage::app ()->getStore ()->getCurrentCurrencyCode () )->getSymbol ()

			// );

		}

		var_dump($result);

	}

}

