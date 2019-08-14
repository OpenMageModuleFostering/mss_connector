<?php
class Mss_Pushnotification_Helper_Data extends Mage_Core_Helper_Abstract
{
	const SEND_IOS_PASSWORD = 'mss_pushnotification/setting/passphrase';
	const GOOGLE_API_KEY ='mss_pushnotification/setting_and/googlekey';
	
	const IOS_PEM = 'mss_pushnotification/setting/pem';
	const IOS_MODE='mss_pushnotification/setting/ios_mode';
	const large_icon='mss_pushnotification/setting_and/notificationimage';
	const small_icon='mss_pushnotification/setting_and/notificationthumbnail';
	
	public $total_notification=0;
	public $ios_notification=0;
	public $android_notification=0;
	public $total_android_success=0;
	public $total_ios_success=0;
	public $total_ios_error=0;
	public $total_android_error=0;

	public function sendnotification($message,$notification_type)
	{
		/*get user to send notification*/

		$activeuser_collection = Mage::getModel('pushnotification/pushnotification')->getCollection();
      		
        	/*notification message*/
		if($notification_type)
			$activeuser_collection ->addFieldToFilter('device_type',array('eq'=>($notification_type-1)));
		
		//$message_android = array("message" => $message,'largeIcon'=> 'large_icon',	'smallIcon'=> 'small_icon');
		$message_android = array("message" => $message);
		foreach($activeuser_collection->getData() as $collection)
		{
		   ($collection['device_type'])? $this->sendPushIOS($collection['registration_id'],$message) : $this->sendPushAndroid(array($collection['registration_id']),$message_android);
			
			$this->total_notification++;

		}
		 $html='';		
		 $html.= 'Total Notification sent : '.$this->total_notification.'</br>';
		 if($notification_type == 0 || $notification_type == 2)
		{
			$html.='Total IOS Device : '.$this->ios_notification.'</br>Total Notification success in IOS : '. $this->total_ios_success.'</br>Total IOS Notification error : '.$this->total_ios_error;
		}
		if($notification_type == 0 || $notification_type == 1)
		{
			$html.='</br> Total Android Devices : '.$this->android_notification.'</br> Total Notification success in Android : '.$this->total_android_success.'</br> Total Android Notification Error : '.$this->total_android_error;
		}
	return  $html;
	}
	/**
     * Sending Push Notification IOS
     */

	public function sendPushIOS($registration_id,$message)
	{

		$this->ios_notification++;
		
		$passphrase = Mage::getStoreConfig(self::SEND_IOS_PASSWORD);
       
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', Mage::getBaseUrl('media') . Mage::getStoreConfig(self::IOS_PEM));
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
		
		$ios_mode=(!Mage::getStoreConfig(self::IOS_MODE))?'ssl://gateway.sandbox.push.apple.com:2195':'ssl://gateway.push.apple.com:2195';
		// Open a connection to the APNS server
		$fp = stream_socket_client(
			$ios_mode, $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp)
			exit("The detail entered for IOS is not correct: $err $errstr" . PHP_EOL);

			echo 'Connected to APNS' . PHP_EOL;

		// Create the payload body
		$body['aps'] = array(
			'alert' => $message,
			'sound' => 'default'
			);

		// Encode the payload as JSON
		$payload = json_encode($body);

		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $registration_id) . pack('n', strlen($payload)) . $payload;

		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));

		if (!$result):
			$this->total_ios_error++;
			echo 'Message not delivered' . PHP_EOL;
		else:
			$this->total_ios_success++;
			echo 'Message successfully delivered' . PHP_EOL;
		endif;
		// Close the connection to the server
		fclose($fp);
		return;


	}

	/**
     * Sending Push Notification Android
     */

	public function sendPushAndroid($registration_id,$message)
	{
		
 		$this->android_notification++;
        // Set POST variables
		$url = 'https://android.googleapis.com/gcm/send';
	 
		$fields = array(
		    'registration_ids' => $registration_id,
		    'data' => $this->__($message)
		    
		);
	 	
		$headers = array(
		    'Authorization: key=' . Mage::getStoreConfig(self::GOOGLE_API_KEY),
		    'Content-Type: application/json'
		);

		// Open connection
		$ch = curl_init();
	 
		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
	 
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	 
		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	 
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	 
		// Execute post
		$result = curl_exec($ch);
		
		if ($result === FALSE):
		    $this->total_android_error++;
		    //echo 'Curl failed: ' . curl_error($ch);
		else:
			$this->total_android_success++;
			//echo 'Success';
		endif;
	 
		// Close connection
		curl_close($ch);
	    	return;   
    }

    

   /*   Register device  
        use below code to register device .
        Mage::helper('pushnotification')->registerdevice($data);
        where $data = array('registration_id'=>'xyz','user_id'=>'1','device_type'=>0);
        Device type 0 for android and 1 for IOS
   */

    public function registerdevice($notification)
    {
           
            $collection = Mage::getModel('pushnotification/pushnotification');
            $filter=$collection->getCollection()->addFieldToFilter('user_id',array(eq=>$notification['user_id']))
                ->addFieldToFilter('registration_id',array(eq=>$notification['registration_id']));

            if(!$filter->count()):
            	$notification['create_date'] = Mage::getModel('core/date')->date('Y-m-d');
            	$notification['update_date'] = Mage::getModel('core/date')->date('Y-m-d');
            	$notification['app_status'] = 1;
                $collection->setData($notification)->save();
            endif;
            
    }

}
	 
