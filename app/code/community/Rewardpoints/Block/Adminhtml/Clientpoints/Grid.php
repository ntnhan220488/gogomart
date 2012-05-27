<?php
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Rewardpoints_Block_Adminhtml_Clientpoints_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('clientpointsGrid');
      $this->setDefaultSort('customer_id ');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {

      /*$collection = Mage::getResourceModel('rewardpoints/rewardpoints_collection');
      
      $this->setCollection($collection);

      if (!Mage::app()->isSingleStoreMode()) {
            $this->getCollection()->addStoreData();
        }

      return parent::_prepareCollection();
      */



      $collection = Mage::getResourceModel('rewardpoints/rewardpoints_collection');
        $this->setCollection($collection);
        parent::_prepareCollection();

        if (!Mage::app()->isSingleStoreMode()) {
            $this->getCollection()->addStoreData();
        } 

        return $this;




  }

  
  protected function _prepareColumns()
  {
      $model = Mage::getModel('rewardpoints/stats');

      $this->addColumn('id', array(
          'header'    => Mage::helper('rewardpoints')->__('id'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'rewardpoints_account_id',
          'type'  => 'number',
      ));

      $this->addColumn('client_id', array(
          'header'    => Mage::helper('rewardpoints')->__('Client ID'),
          'align'     =>'right',
          'index'     => 'customer_id',
          'width'     => '50px',
          'type'  => 'number',
      ));

      $this->addColumn('email', array(
          'header'    => Mage::helper('rewardpoints')->__('Email'),
          'align'     =>'right',
          'index'     => 'email',
      ));

      $this->addColumn('order_id', array(
          'header'    => Mage::helper('rewardpoints')->__('Order ID'),
          'align'     =>'right',
          'index'     => 'order_id',
      ));


      $this->addColumn('order_id_corres', array(
            'header'    => Mage::helper('rewardpoints')->__('Type of points'),
            'index'     => 'order_id',
            'width'     => '150px',
            'type'      => 'options',
            'options'   => array(Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW => Mage::helper('adminhtml')->__('Review points'), Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN => Mage::helper('adminhtml')->__('Admin gift'), Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION => Mage::helper('adminhtml')->__('Registration points')),
        ));
        

/*
      
*/
      $this->addColumn('points_current', array(
          'header'    => Mage::helper('rewardpoints')->__('Accumulated points'),
          'align'     => 'right',
          'index'     => 'points_current',
          'width'     => '50px',
          'filter'    => false,
      ));
      $this->addColumn('points_spent', array(
          'header'    => Mage::helper('rewardpoints')->__('Spent points'),
          'align'     => 'right',
          'index'     => 'points_spent',
          'width'     => '50px',
          'filter'    => false,
      ));

      

      if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('rewardpoints')->__('Stores'),
                'index'     => 'stores',
                'type'      => 'store',
                'store_view' => false,
                'sortable'   => false,
            ));
        }







      
      $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
      $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));

      return parent::_prepareColumns();
  }

  

  protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rewardpoints_account_id');
        $this->getMassactionBlock()->setFormFieldName('rewardpoints_account_ids');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('rewardpoints')->__('Delete&nbsp;&nbsp;'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('rewardpoints')->__('Are you sure?')
        ));

        return $this;
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }



}