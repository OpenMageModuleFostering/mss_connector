<?php

class Mss_Pushnotification_Model_Mysql4_Pushnotification_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('pushnotification/pushnotification');
    }
}
