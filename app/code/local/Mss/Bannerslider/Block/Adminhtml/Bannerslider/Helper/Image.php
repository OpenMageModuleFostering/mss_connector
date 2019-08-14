<?php


class Mss_Bannerslider_Block_Adminhtml_Bannerslider_Helper_Image
    extends Varien_Data_Form_Element_Image {
    protected function _getUrl(){
        $url = false;
        if ($this->getValue()) {
			$path =   Mage::helper('bannerslider')->reImageName($this->getValue());
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."media/bannerslider/".$path;
        }
        return $url;
    }
}
