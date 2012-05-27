<?php
/**
 * Magento
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
class Rewardpoints_Block_Dashboard extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rewardpoints/dashboard_points.phtml');
    }


    public function getPointsCurrent(){
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $reward_model = Mage::getModel('rewardpoints/stats');
        $store_id = Mage::app()->getStore()->getId();
        return $reward_model->getPointsCurrent($customerId, $store_id);
    }

    public function getPointsReceived(){
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $reward_model = Mage::getModel('rewardpoints/stats');
        $store_id = Mage::app()->getStore()->getId();
        return $reward_model->getPointsReceived($customerId, $store_id);
    }

    public function getPointsSpent(){
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $reward_model = Mage::getModel('rewardpoints/stats');
        $store_id = Mage::app()->getStore()->getId();
        return $reward_model->getPointsSpent($customerId, $store_id);
    }

    public function getPointsWaitingValidation(){
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $reward_model = Mage::getModel('rewardpoints/stats');
        $store_id = Mage::app()->getStore()->getId();
        return $reward_model->getPointsWaitingValidation($customerId, $store_id);
    }

}
