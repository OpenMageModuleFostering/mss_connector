<?php
class Mss_Bannerslider_Helper_Data extends Mage_Core_Helper_Abstract
{

	 public function deleteImageFile($image) {
        if (!$image) {
            return;
        }
        $name = $this->reImageName($image);
        $banner_image_path = Mage::getBaseDir()  . '/media/bannerslider/' . $name;
        if (!file_exists($banner_image_path)) {
            return;
        }

        try {
            unlink($banner_image_path);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public static function uploadBannerImage() {
        $banner_image_path = Mage::getBaseDir() . '/media/bannerslider/';
        $image = "";
        $image_resolution = getimagesize($_FILES['image']['tmp_name']);
        
        if($image_resolution['0'] != '640' && $image_resolution['1'] != '320'):
            Mage::getSingleton('core/session')->addError('Banner Image resolution is incorrect, please upload image of resolution 640X320 px. ');
           $image = false;
        else:
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('image');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    $uploader->setFilesDispersion(true);

                    $uploader->save($banner_image_path, $_FILES['image']['name']);

                } catch (Exception $e) {
                    
                }

                $image = $_FILES['image']['name'];
            }
        endif;
        return $image;
    }

    public function reImageName($imageName) {

        $subname = substr($imageName, 0, 2);
        $array = array();
        $subDir1 = substr($subname, 0, 1);
        $subDir2 = substr($subname, 1, 1);
        $array[0] = $subDir1;
        $array[1] = $subDir2;
        $name = $array[0] . '/' . $array[1] . '/' . $imageName;

        return strtolower($name);
    }
}
	 
