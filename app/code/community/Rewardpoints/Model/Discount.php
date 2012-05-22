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
class Rewardpoints_Model_Discount extends Mage_Core_Model_Abstract
{

    protected $_discount;
    protected $_quote;
    protected $_couponCode;


    public function getCartAmount(){
        $totalPrices = Mage::helper('checkout/cart')->getCart()->getQuote()->getTotals();
        $tax = 0;
        $subtotalPrice = 0;
        if (isset($totalPrices['tax'])){
            $tax_val = $totalPrices['tax'];
            $tax = $tax_val->getData('value');
        }

        $subtotalPrice = $totalPrices['subtotal'];
        $order_details = $subtotalPrice->getData('value') + $tax;
        return $order_details;
    }


    public function checkMaxPointsToApply($points){
        $order_details = $this->getCartAmount();
        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($order_details);
        $maxpoints = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount), $points);
        return $maxpoints;
    }


    public function apply(Mage_Sales_Model_Quote_Item_Abstract $item)
    {

        $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();
        $this->_quote = $quote = $item->getQuote();
        $customer = $quote->getCustomer();
        $customerId = $customer->getId();
        
        //$cart_count_items = Mage::helper('checkout/cart')->getSummaryCount();

        if ($points_apply > 0 && $customerId != null){
            $test_points = $this->checkMaxPointsToApply($points_apply);
            if ($points_apply > $test_points){
                $points_apply = $test_points;
                Mage::helper('rewardpoints/event')->setCreditPoints($points_apply);
            }            
            $points_apply_amount = Mage::helper('rewardpoints/data')->convertPointsToMoney($points_apply);
            $address = $this->_getAddress($item);
            if (!$this->_discount){
                $reward_model = Mage::getModel('rewardpoints/stats');
                if ($points_apply > $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId())){
                    return false;
                } else {
                    $discounts = $points_apply_amount;
                }
                if ((Mage::getSingleton('customer/session')->getProductChecked() >= Mage::helper('checkout/cart')->getSummaryCount() && $discounts > 0) || !Mage::getSingleton('customer/session')->getProductChecked() || Mage::getSingleton('customer/session')->getProductChecked() == 0){
                    Mage::getSingleton('customer/session')->setProductChecked(0);
                    Mage::getSingleton('customer/session')->setDiscountleft($points_apply_amount);
                    $this->_discount = $discounts;
                    $this->_couponCode = $points_apply;
                } else {
                    $this->_discount = Mage::getSingleton('customer/session')->getDiscountleft();
                    $this->_couponCode =$points_apply;
                }
            }

            $discountAmount = 0;
            $baseDiscountAmount = 0;

            $discountAmount = min($item->getRowTotal() - $item->getDiscountAmount(), $quote->getStore()->convertPrice($this->_discount));
            $baseDiscountAmount = min($item->getBaseRowTotal() - $item->getBaseDiscountAmount(), $this->_discount);

            Mage::getSingleton('customer/session')->setProductChecked(Mage::getSingleton('customer/session')->getProductChecked() + $item->getQty());
            $quote_id = Mage::helper('checkout/cart')->getCart()->getQuote()->getId();
            Mage::getSingleton('customer/session')->setDiscountleft(Mage::getSingleton('customer/session')->getDiscountleft() - $baseDiscountAmount);
            $discountAmount     = min($discountAmount + $item->getDiscountAmount(), $item->getRowTotal());
            $baseDiscountAmount = min($baseDiscountAmount + $item->getBaseDiscountAmount(), $item->getBaseRowTotal());
            $item->setDiscountAmount($discountAmount);
            $item->setBaseDiscountAmount($baseDiscountAmount);
            //store_labels
            $couponCode = explode(', ', $address->getCouponCode());
            $couponCode[] = Mage::helper('rewardpoints/data')->__('%s credit points', $this->_couponCode);
            $couponCode = array_unique(array_filter($couponCode));
            $address->setCouponCode(implode(', ', $couponCode));
            $address->setDiscountDescriptionArray($couponCode);
        }
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }


    /**
     * Get address object which can be used for discount calculation
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Sales_Model_Quote_Address
     */
    protected function _getAddress(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address = $item->getAddress();
        } elseif ($item->getQuote()->isVirtual()) {
            $address = $item->getQuote()->getBillingAddress();
        } else {
            $address = $item->getQuote()->getShippingAddress();
        }
        return $address;
    }

}
?>
