<?php

class Mss_Bannerslider_Block_Adminhtml_Bannerslider_Grid extends Mage_Adminhtml_Block_Widget_Grid {

   public function __construct()
   {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('banner_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);

   }
   protected function _prepareCollection()
   {
      $collection = Mage::getModel('bannerslider/bannerslider')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
    }
   protected function _prepareColumns()
   {
       $this->addColumn('banner_id',
             array(
                    'header' => 'ID',
                    'align' =>'right',
                    'width' => '50px',
                    'index' => 'banner_id',
               ));
       $this->addColumn('name',
               array(
                    'header' => 'Name',
                    'align' =>'left',
                    'index' => 'name',
              ));
       $this->addColumn('url_type', array(
                    'header' => 'Link Type',
                    'align' =>'left',
                    'index' => 'url_type',
             ));
        $this->addColumn('image', array(
                     'header' => 'Image',
                     'align' =>'left',
                     'index' => 'image',
                      'renderer' => 'Mss_Bannerslider_Block_Adminhtml_Renderer_Image',
          ));
         return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
         return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}