<?php
class Mss_Bannersliderapp_Model_Mysql4_Bannersliderapp extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("bannersliderapp/bannersliderapp", "banner_id");
    }
}