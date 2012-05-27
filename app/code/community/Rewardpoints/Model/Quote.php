<?php

class Rewardpoints_Model_Quote extends Mage_Sales_Model_Quote
{
    protected function _validateCouponCode()
    {
        $code = $this->_getData('coupon_code');
        if ($code) {
            $addressHasCoupon = false;
            $addresses = $this->getAllAddresses();
            if (count($addresses)>0) {
                foreach ($addresses as $address) {
                    //if ($address->hasCouponCode()) {
                    if (preg_match("/".$code."/i", $address->getCouponCode())) {
                        $addressHasCoupon = true;
                    }
                }
                if (!$addressHasCoupon) {
                    $this->setCouponCode('');
                }
            }
        }

        return $this;
    }
}