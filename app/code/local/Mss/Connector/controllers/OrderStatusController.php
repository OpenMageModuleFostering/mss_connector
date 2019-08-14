   <?php
class Mss_Connector_OrderStatusController extends Mage_Core_Controller_Front_Action {
 
    public function _construct(){

        header('content-type: application/json; charset=utf-8');
        header("access-control-allow-origin: *");
        Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
        parent::_construct();
        
    }
    public function checkOrderAction() {

        $post = Mage::app()->getRequest()->getParams();
        $incrementId    = '';
        $email          = '';
        $errors         = false;
        $order = Mage::getModel('sales/order');
            if (!empty($post) && isset($post['oar_order_id']))  {
                $incrementId    = $post['oar_order_id'];
                $email          = $post['oar_email'];
                $order->loadByIncrementId($incrementId);
               
                if ($order->getId()) {
                    $billingAddress = $order->getBillingAddress();
                    $shippingAddress = $order->getShippingAddress();

                    if  (strtolower($email) != strtolower($order->getCustomerEmail()) && $email != "") {
                            echo json_encode(array('status'=>error,'message'=>'Email is not valid')); 
                            exit;
                    }
                    $items = $order->getAllItems();
                    $itemcount= count($items);
                    $data = array();
                    $coll = array() ;
                    $i=0;
                    #loop for all order items
                    foreach ($items as $itemId => $item)
                    {
                        $products = Mage::getModel('catalog/product')->load($item->getProductId());
                        $default_image  = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/media/catalog/product'.$products->getThumbnail();
                        $getThumbnail = $products->getThumbnail();

                        $images= $getThumbnail ? $default_image : Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/' .Mage::getStoreConfig("catalog/placeholder/small_image_placeholder");
; 
                        $data['name'] = $item->getName();
                        $data['image_url'] = $images;
                        $data['price'] = number_format((float)$item->getPrice(), 2, '.', '');   
                        $data['sku'] = $item->getSku();
                        $data['id'] = $item->getProductId();
                       // $data[$i]['qty'] = $item->getQtyToInvoice();
                        $data['qty'] = number_format((float)$item->getData('qty_ordered'), 2, '.', '');
                        array_push($coll, $data);
                    }
                        $array['billingAddress'] = $billingAddress->getData();
                        $array['shippingAddress'] = $shippingAddress->getData();
                        $array['item_ordered'] = $coll;
                        $array['shipping_method'] = $order->getData('shipping_method');
                        $array['status'] = $order->getStatus();
                        $array['payment_method'] =$order->getPayment()->getMethodInstance()->getTitle();
                        $array['subtotal'] = number_format((float)$order->getData('subtotal'), 2, '.', '');
                        $array['shipping_incl_tax'] = number_format((float)$order->getData('shipping_incl_tax'), 2, '.', '');
                        $array['base_grand_total'] = number_format((float)$order->getData('base_grand_total'), 2, '.', '');
                    echo json_encode(array('status'=>true,'data'=>$array));
                    exit;
                }  else {
                    echo json_encode(array('status'=>false,'message'=>'Data missing'));
                    exit;
                }
            }
    }
}