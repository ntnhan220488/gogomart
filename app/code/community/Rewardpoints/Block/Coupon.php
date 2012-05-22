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

class Rewardpoints_Block_Coupon extends Mage_Checkout_Block_Cart_Abstract
{
    /*public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }*/

    public function getAutoUse(){
        return Mage::getStoreConfig('rewardpoints/default/auto_use', Mage::app()->getStore()->getId());
    }

    public function getPointsOnOrder() {
        return Mage::helper('rewardpoints/data')->getPointsOnOrder();
    }

    public function getCustomerId() {
        return Mage::getModel('customer/session')->getCustomerId();
    }

    public function getPointsCurrentlyUsed() {
        return Mage::helper('rewardpoints/event')->getCreditPoints();
    }

    public function canUseCouponCode(){
        return Mage::getStoreConfig('rewardpoints/default/coupon_codes', Mage::app()->getStore()->getId());
    }

    public function getCustomerPoints() {
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $reward_model = Mage::getModel('rewardpoints/stats');
        $store_id = Mage::app()->getStore()->getId();
        return $reward_model->getPointsCurrent($customerId, $store_id);
    }

    public function getPointsInfo() {
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $reward_model = Mage::getModel('rewardpoints/stats');
        $store_id = Mage::app()->getStore()->getId();
        $customerPoints = $reward_model->getPointsCurrent($customerId, $store_id);

        //points required to get 1 €
        $points_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        //step to reach to get discount
        $step = Mage::getStoreConfig('rewardpoints/default/step_value', Mage::app()->getStore()->getId());
        //check if step needs to apply
        $step_apply = Mage::getStoreConfig('rewardpoints/default/step_apply', Mage::app()->getStore()->getId());
        $full_use = Mage::getStoreConfig('rewardpoints/default/full_use', Mage::app()->getStore()->getId());

        $order_details = $this->getQuote()->getSubtotal();
        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($order_details);
        $max_use = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount), $customerPoints);

        return array('customer_points' => $customerPoints, 'points_money' => $points_money, 'step' => $step, 'step_apply' => $step_apply, 'full_use' => $full_use, 'max_use' => $max_use);
    }

    public function pointsToAddOptions($customer_points, $step){
        $toHtml = '';
        $creditToBeAdded = 0;

        //points required to get 1 €
        $points_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        $max_points_tobe_used = Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId());
        
        $order_details = $this->getQuote()->getSubtotal();
        $cart_amount = Mage::helper('rewardpoints/data')->convertMoneyToPoints($order_details);

        $customer_points_origin = $customer_points;

        $test = "";
        while ($customer_points > 0){
            $creditToBeAdded += $step;
            $customer_points-=$step;
            if ($creditToBeAdded > $customer_points_origin || $cart_amount < $creditToBeAdded || ($max_points_tobe_used != 0 && $max_points_tobe_used < $creditToBeAdded)){
                break;
            }
            //check if credits always lower than total cart amount
            $toHtml .= '<option value="'. $creditToBeAdded .'">'. $this->__("%d loyalty point(s)",$creditToBeAdded) .'</option>';
        }
        return $toHtml;
    }

}