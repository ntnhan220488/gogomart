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
class Rewardpoints_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getReferalUrl()
    {
        return $this->_getUrl('rewardpoints/');
    }

    public function processMathValue($amount){
        $math_method = Mage::getStoreConfig('rewardpoints/default/math_method', Mage::app()->getStore()->getId());
        if ($math_method == 1){
            $amount = round($amount);
        } elseif ($math_method == 0) {
            $amount = floor($amount);
        }
        return $amount;
    }

    public function getProductPoints($product){

        $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($product);
        if ($catalog_points === false){
            return 0;
        }

        $product_points = $product->getData('reward_points');
        if ($product_points > 0){
            $points_tobeused = $product_points + (int)$catalog_points;
            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
                if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $points_tobeused){
                    return Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                }
            }
            return ($points_tobeused);
        } else {
            //get product price include vat
            $_finalPriceInclTax  = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
            $_weeeTaxAmount = Mage::helper('weee')->getAmount($product);
            $price = Mage::helper('core')->currency($_finalPriceInclTax+$_weeeTaxAmount,false,false);
            $money_to_points = Mage::getStoreConfig('rewardpoints/default/money_points', Mage::app()->getStore()->getId());
            if ($money_to_points > 0){
                $price = $price * $money_to_points;
            }

            $points_tobeused = $this->processMathValue($price + (int)$catalog_points);

            if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
                if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $points_tobeused){
                    return Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                }
            }
            
            return ($points_tobeused);

        }
    }

    public function convertMoneyToPoints($money){
        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());
        $money_amount = $this->processMathValue($money*$points_to_get_money);

        return $money_amount;
    }

    public function convertPointsToMoney($points_to_be_used){
        $customerId = Mage::getModel('customer/session')
                                        ->getCustomerId();
        
        $reward_model = Mage::getModel('rewardpoints/stats');
        $current = $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId());


        if ($current < $points_to_be_used) {
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('Not enough points available.'));
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
            return 0;
        }
        $step = Mage::getStoreConfig('rewardpoints/default/step_value', Mage::app()->getStore()->getId());
        $step_apply = Mage::getStoreConfig('rewardpoints/default/step_apply', Mage::app()->getStore()->getId());
        if ($step > $points_to_be_used && $step_apply){
            Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('The minimum required points is not reached.'));
            Mage::helper('rewardpoints/event')->setCreditPoints(0);
            return 0;
        }

        
        if ($step_apply){
            if (($points_to_be_used % $step) != 0){
                Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('Amount of points wrongly used.'));
                Mage::helper('rewardpoints/event')->setCreditPoints(0);
                return 0;
            }
        }

        $points_to_get_money = Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId());

        $discount_amount = $this->processMathValue($points_to_be_used/$points_to_get_money);

        return $discount_amount;
    }

    public function getPointsOnOrder($cartLoaded = null, $cartQuote = null){
        $rewardPoints = 0;

        //get points cart rule
        if ($cartLoaded != null){
            $points_rules = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered($cartLoaded);
        } else {
            $points_rules = Mage::getModel('rewardpoints/pointrules')->getAllRulePointsGathered();
        }
        
        if ($points_rules === false){
            return 0;
        }
        $rewardPoints += (int)$points_rules;
        
        if ($cartLoaded == null){
            $cartHelper = Mage::helper('checkout/cart');
            $items = $cartHelper->getCart()->getItems();
        } elseif ($cartQuote != null){
            $items = $cartQuote->getAllItems();
        }else {
            $items = $cartLoaded->getAllItems();
        }

        
        $cart_amount = 0;
        foreach ($items as $_item){
            $_product = Mage::getModel('catalog/product')->load($_item->getProductId());
            $catalog_points = Mage::getModel('rewardpoints/catalogpointrules')->getAllCatalogRulePointsGathered($_product);
            if ($catalog_points === false){
                continue;
            } else {
                //$rewardPoints += (int)$catalog_points * $_item->getQty();

                if ($cartLoaded == null || $cartQuote != null){
                    $rewardPoints += (int)$catalog_points * $_item->getQty();
                } else {
                    $rewardPoints += (int)$catalog_points * $_item->getQtyOrdered();
                }
            }
            $product_points = $_product->getData('reward_points');
            
            if ($product_points > 0){
                if ($_item->getQty() > 0 || $_item->getQtyOrdered() > 0){
                    if ($cartLoaded == null || $cartQuote != null){
                        $rewardPoints += (int)$product_points * $_item->getQty();
                    } else {
                        $rewardPoints += (int)$product_points * $_item->getQtyOrdered();
                    }
                }
            } else {
                $price = $_item->getRowTotal() + $_item->getTaxAmount() - $_item->getDiscountAmount();    
                $rewardPoints += (int)Mage::getStoreConfig('rewardpoints/default/money_points', Mage::app()->getStore()->getId()) * $price;
            }
            $cart_amount += $_item->getRowTotal() + $_item->getTaxAmount() - $_item->getDiscountAmount();
        }
        $rewardPoints = $this->processMathValue($rewardPoints);

        if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
            if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $rewardPoints){
                return Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
            }
        }
        
        return $rewardPoints;
    }
}
