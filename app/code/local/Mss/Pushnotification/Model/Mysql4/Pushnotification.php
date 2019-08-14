<?php

class Mss_Pushnotification_Model_Mysql4_Pushnotification extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        $this->_init('pushnotification/pushnotification','id');
    }
}
