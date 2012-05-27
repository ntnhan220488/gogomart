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
class Rewardpoints_Block_Points extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('referafriend/points.phtml');
        $points = Mage::getModel('rewardpoints/stats')->getCollection()
            ->addClientFilter(Mage::getSingleton('customer/session')->getCustomer()->getId());
        $this->setPoints($points);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'rewardpoints.points')
            ->setCollection($this->getPoints());
        $this->setChild('pager', $pager);
        $this->getPoints()->load();

        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }


    public function getTypeOfPoint($order_id, $referral_id)
    {
        $status_field = Mage::getStoreConfig('rewardpoints/default/status_used', Mage::app()->getStore()->getId());

        $toHtml = '';
        if($referral_id){
            $referrer = Mage::getModel('rewardpoints/referral')->load($referral_id);
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Referral points (%s)',$referrer->getRewardpointsReferralEmail()).'</div>';
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $toHtml .=  '<div class="j2t-in-txt">'.$this->__('Referral order state: %s',$this->__($order->getData($status_field))).'</div>';
        } elseif ($order_id == Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW){
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Review points').'</div>';
        } elseif ($order_id < 0){
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Gift').'</div>';
        } else {
            $toHtml .= '<div class="j2t-in-title">'.$this->__('Order: %s', $order_id).'</div>';
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $toHtml .= '<div class="j2t-in-txt">'.$this->__('Order state: %s',$this->__($order->getData($status_field))).'</div>';
        }

        return $toHtml;
    }

}