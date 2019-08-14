<?php


class Mss_Bannersliderapp_Block_Adminhtml_Bannersliderapp_Helper_Image
    extends Varien_Data_Form_Element_Image {
    protected function _getUrl(){
        $url = false;
        if ($this->getValue()) {
			$path =   Mage::helper('bannersliderapp')->reImageName($this->getValue());
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."media/bannerslider/".$path;
        }
        return $url;
    }
}
