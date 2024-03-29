<?php
class Rewardpoints_Model_Validator extends Mage_SalesRule_Model_Validator
{

    public function processShippingAmount(Mage_Sales_Model_Quote_Address $address)
    {
        //parent::process($address);
        parent::processShippingAmount($address);

        $shipping_process = Mage::getStoreConfig('rewardpoints/default/process_shipping', Mage::app()->getStore()->getId());
        if (version_compare(Mage::getVersion(), '1.4.0', '>=') && $shipping_process){
            Mage::getSingleton('rewardpoints/session')->setShippingChecked(0);
            Mage::getModel('rewardpoints/discount')->applyShipping($address);
        }
    }


    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        parent::process($item);
        
        try {
            $customer = Mage::getSingleton('customer/session');
            if ($customer->isLoggedIn()){

                $customerId = Mage::getModel('customer/session')->getCustomerId();
                $reward_model = Mage::getModel('rewardpoints/stats');

                $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', Mage::app()->getStore()->getId());
                if ($auto_use){
                    $customer_points = $reward_model->getPointsCurrent($customerId, Mage::app()->getStore()->getId());
                    if ($customer_points && $customer_points > Mage::helper('rewardpoints/event')->getCreditPoints()){
                        $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount();
                        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($cart_amount);

                        $points_value = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount), (int)$customer_points);

                        Mage::getSingleton('customer/session')->setProductChecked(0);
                        Mage::helper('rewardpoints/event')->setCreditPoints($points_value);
                    }
                }
                Mage::getModel('rewardpoints/discount')->apply($item);
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        } catch (Exception $e) {
           Mage::getSingleton('checkout/session')->addError($e);
        }
        

        return $this;
    }
}
