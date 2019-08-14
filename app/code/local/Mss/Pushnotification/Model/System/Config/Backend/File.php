<?php
class Mss_Pushnotification_Model_System_Config_Backend_File extends Mage_Adminhtml_Model_System_Config_Backend_File
{
    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return array
     */
    protected function _getAllowedExtensions()
    {
        return array('pem');
    }
}