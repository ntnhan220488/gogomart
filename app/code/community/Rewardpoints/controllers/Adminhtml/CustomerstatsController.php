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
class Rewardpoints_Adminhtml_CustomerstatsController extends Mage_Adminhtml_Controller_Action
{

    protected function _initCustomer($idFieldName = 'id')
    {
        //$this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    public function indexAction() {
        /*$this->_initAction()
            ->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_customerstats'))
            ->renderLayout();*/
        $this->_initCustomer();
        $this->getResponse()->setBody($this->getLayout()->createBlock('rewardpoints/adminhtml_customerstats')->toHtml());
    }

   
/*
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('customer/config');
        $this->_addBreadcrumb(Mage::helper('customer')->__('Customer'),  Mage::helper('customer')->__('Customer'));
        $this->_addBreadcrumb(Mage::helper('customer')->__('Config'), Mage::helper('customer')->__('Config'));
        $this->_addContent(
            $this->getLayout()->createBlock('rewardpoints/adminhtml_customerstats')
        );
        $this->getLayout()->getBlock('left')
            ->append($this->getLayout()->createBlock('adminhtml/customer_config_tabs'));

        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/config');
    }
*/

}