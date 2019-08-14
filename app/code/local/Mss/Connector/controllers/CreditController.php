<?php

class Mss_Connector_CreditController extends Mage_Core_Controller_Front_Action{

    public function _construct(){

        header('content-type: application/json; charset=utf-8');
        header("access-control-allow-origin: *");
        Mage::helper('connector')->loadParent(Mage::app()->getFrontController()->getRequest()->getHeader('token'));
        parent::_construct();
        
    }

 private function getCustomerId($customer)
    {
        $customerid = Mage::getModel('customer/customer')->load($customer);
        if ($customerid->getData()) {
            return $customerid->getId();
        } else {
            $collection = Mage::getModel('customer/customer')->getCollection();
            $collection->addFieldToFilter('email', $customer);
            return $collection->getFirstItem()->getId();
        }
    }

    /* set transaction type
     *
     * return type_id
     */

    private function setType($type)
    {
        $collection = Mage::getModel('customercredit/typetransaction')->getCollection();
        $collection->addFieldToFilter('transaction_name', $type);
        if (count($collection)) {
            return $collection->getFirstItem()->getId();
        } else {
            $update_type = Mage::getModel('customercredit/typetransaction');
            $update_type->setTransactionName($type);
            try {
                $update_type->save();
                return $update_type->getId();
            } catch (exception $e) {
                return null;
            }
        }
    }

    /* update credit balance */

    public function updateBalance($customer, $value)
    {
        $customer_id = $this->getCustomerId($customer);
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $customer->setCreditValue($customer->getCreditValue() + $value)->save();
    }

    public function getCreditBalanceAction()
    {
       $customer_id=Mage::app ()->getRequest ()->getParam ( 'customer_id' );
	if (!Zend_Validate::is($customer_id, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('Customer Id should not be empty')));
		    			exit;
	endif;
	$customer = Mage::getModel('customer/customer')->load($customer_id);
	if(count($customer->getData())>0):
       		echo json_encode('The credit balance of customer id '.$customer_id.'is'.$customer->getCreditValue()); 
	else:
		echo json_encode(array('status'=>'error','message'=> $this->__('CustomerId does not exist')));
	endif;
        
    }

    public function updateCreditAction()
    {
	$customer_id=Mage::app ()->getRequest ()->getParam ( 'customer_id' );
	$transaction_type=Mage::app ()->getRequest ()->getParam ( 'transaction_type' );
	$transaction_detail=Mage::app ()->getRequest ()->getParam ( 'transaction_detail' );
	$order_id=Mage::app ()->getRequest ()->getParam ( 'order_id' );
	$amount_credit=Mage::app ()->getRequest ()->getParam ( 'amount_credit' );
        
	if (!Zend_Validate::is($customer_id, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('Customer Id should not be empty')));
		    			exit;
	endif;
	
	if (!Zend_Validate::is($transaction_type, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('Transaction Type should not be empty')));
		    			exit;
	endif;
	
	if (!Zend_Validate::is($amount_credit, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('Amount Credit should not be empty')));
		    			exit;
	endif;
        
        Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, $transaction_type, $transaction_detail, $order_id, $amount_credit);
        $this->updateBalance($customer_id, $amount_credit);
	echo json_encode(array('status'=>'success','message'=> $this->__('Credit has been updated')));
    }

    public function redeemCreditAction()
    {
	
        $customer_id=Mage::app ()->getRequest ()->getParam ( 'customer_id' );
	$creditcode=Mage::app ()->getRequest ()->getParam ( 'creditcode' );
	if (!Zend_Validate::is($customer_id, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('Customer Id should not be empty')));
		    			exit;
	endif;
	
	if (!Zend_Validate::is($creditcode, 'NotEmpty')):
					echo json_encode(array('status'=>'error','message'=> $this->__('Credit Code should not be empty')));
		    			exit;
	endif;
	
        $credit = Mage::getModel('customercredit/creditcode')->getCollection()
            ->addFieldToFilter('credit_code', $creditcode);
	if(count($credit->getData)):
			echo json_encode(array('status'=>'error','message'=> $this->__('Customer ID does not be exist')));
		    	exit;
	endif;
        if ($credit->getSize() == 0) {
          json_encode(array('status'=>'error','message'=> $this->__('Code is invalid. Please check again!')));  
		exit;
        } 
	if ($credit->getFirstItem()->getStatus() != 1) {
           json_encode(array('status'=>'error','message'=> $this->__('Code was used. Please check again!')));
        } 

            Mage::getModel('customercredit/creditcode')
                ->changeCodeStatus($credit->getFirstItem()->getId(), Magestore_Customercredit_Model_Status::STATUS_UNUSED);
            $credit_amount = $credit->getFirstItem()->getAmountCredit();
            Mage::getModel('customercredit/transaction')->addTransactionHistory($customer_id, Magestore_Customercredit_Model_TransactionType::TYPE_REDEEM_CREDIT, "redeemcredit", "", $credit_amount);
            $this->updateBalance($customer_id, $credit_amount);
            json_encode(array('status'=>'success','message'=> $this->__(' Credit has been redeemed')));
        }
    }




?>
