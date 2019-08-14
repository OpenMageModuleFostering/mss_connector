<?php class Mss_Bannerslider_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	public function render(Varien_Object $row)
	{
		
		if($row->getData('image') != NULL)
		{
			$image=$row->getData('image');
			$path =   Mage::helper('bannerslider')->reImageName($image);
			return '<img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'media/bannerslider/'.$path.'" width="100px" alt="brandlogo"/>';
		}
	}

		
 }
