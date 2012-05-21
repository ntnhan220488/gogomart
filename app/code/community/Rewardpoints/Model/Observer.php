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
	class Rewardpoints_Model_Observer extends Mage_Core_Model_Abstract {
		
		public function recordPointsUponRegistration($observer){
                    if (Mage::getStoreConfig('rewardpoints/registration/registration_points', Mage::app()->getStore()->getId()) > 0){
                        //check if points already earned
                        $customerId = $observer->getEvent()->getCustomer()->getEntityId();
                        $points = Mage::getStoreConfig('rewardpoints/registration/registration_points', Mage::app()->getStore()->getId());
                        //$orderId = -2;
                        $this->recordPoints($points, $customerId, Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN, false);
                    }
                }

                public function recordPointsForOrderEvent($observer) {
                    $event = $observer->getEvent();
                    $order = $event->getOrder();
                    $quote = $event->getQuote();

                    $order->setQuote($quote);
                    $rewardPoints = Mage::helper('rewardpoints/data')->getPointsOnOrder($order);

                    if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
                        if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $rewardPoints){
                            $rewardPoints = Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                        }
                    }


                    $customerId = $order->getCustomerId();

                    //record points for item into db
                    if ($rewardPoints > 0){
                        $this->recordPoints($rewardPoints, $customerId, $order->getIncrementId());
                    }



                    //subtract points for this order
                    $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();
                    if ($points_apply > 0){
                        $this->useCouponPoints($points_apply, $customerId, $order->getIncrementId());
                    }

                    //$this->sales_order_success_referral($order->getIncrementId());
                    $this->sales_order_success_referral($order);
		}

                protected function getMultishippingQuote($order) {
                    $order_shipping_address = Mage::getModel('sales/order_address')->load($order->getShippingAddressId());
                    $customer_shipping_address = $order_shipping_address->getCustomerAddressId();

                    $order_billing_address = Mage::getModel('sales/order_address')->load($order->getBillingAddressId());
                    $customer_billing_address = $order_billing_address->getCustomerAddressId();

                    $quote_tmp = Mage::getModel('sales/quote');
                    $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                    foreach($quote->getAddressesCollection() as $my_quote){
                        if ($my_quote->getAddressType() == 'shipping' && $my_quote->getCustomerAddressId() == $customer_shipping_address){
                            $quote_tmp->setShippingAddress($my_quote);
                        } elseif($my_quote->getAddressType() == 'billing' && $my_quote->getCustomerAddressId() == $customer_billing_address) {
                            $quote_tmp->setBillingAddress($my_quote);
                        }
                    }
                    return $quote_tmp;
                }




                public function recordPointsForMultiOrderEvent($observer) {

                    $event = $observer->getEvent();
                    $orders = $event->getOrders();
                    $quote = $event->getQuote();

                    if ($orders == array()){
                        $this->recordPointsForOrderEvent($observer);
                        return true;
                    }

                    //$order = Mage::getModel('sales/order')->load($orderId);
                    foreach($orders as $order){
                        //$order = Mage::getModel('sales/order')->load($order->getId());


                        //$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                        //$quote->getShippingAddress()->setBaseSubtotal($order->getBaseSubtotal());
                        //$quote->setData($order->getData());
                        $order->setQuote($this->getMultishippingQuote($order));

                        $customerId = $order->getCustomerId();

                        /*$quote_order = Mage::getModel('sales/convert_order')->toQuote($order);
                        $order->setQuote($quote_order);*/

                        $rewardPoints = Mage::helper('rewardpoints/data')->getPointsOnOrder($order);

                        if (Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId())){
                            if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId()) < $rewardPoints){
                                $rewardPoints = Mage::getStoreConfig('rewardpoints/default/max_point_collect_order', Mage::app()->getStore()->getId());
                            }
                        }

                        //record points for item into db
                        if ($rewardPoints > 0){
                            $this->recordPoints($rewardPoints, $customerId, $order->getIncrementId());
                        }

                        //subtract points for this order
                        $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();
                        if ($points_apply > 0){
                            $this->useCouponPoints($points_apply, $customerId, $order->getIncrementId());
                        }

                        //$this->sales_order_success_referral($order->getIncrementId());
                        $this->sales_order_success_referral($order);
                    }
		}




		public function useCouponPoints($pointsAmt, $customerId, $orderId) {
                    $reward_model = Mage::getModel('rewardpoints/stats');
                    //money_points
                    //points_money

                    $test_points = $reward_model->checkProcessedOrder($customerId, $orderId, false);
                    if (!$test_points->getId()){
                        $post = array('order_id' => $orderId, 'customer_id' => $customerId, 'store_id' => Mage::app()->getStore()->getId(), 'points_spent' => $pointsAmt, 'convertion_rate' => Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId()));
                        $reward_model->setData($post);
                        $reward_model->save();
                        Mage::helper('rewardpoints/event')->setCreditPoints(0);
                    }
		}
		
		public function recordPoints($pointsInt, $customerId, $orderId, $no_check = true) {
                    $reward_model = Mage::getModel('rewardpoints/stats');

                    //check if points are already processed
                    $test_points = $reward_model->checkProcessedOrder($customerId, $orderId, true);
                    if (!$test_points->getId()){
                        $post = array('order_id' => $orderId, 'customer_id' => $customerId, 'store_id' => Mage::app()->getStore()->getId(), 'points_current' => $pointsInt, 'convertion_rate' => Mage::getStoreConfig('rewardpoints/default/points_money', Mage::app()->getStore()->getId()));
                        $reward_model->setData($post);
                        $reward_model->save();
                    }
		}


                public function sales_order_success_referral($order)
                {

                    //check if referral from link...
                    if ($userId = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
                        $referrer = Mage::getModel('customer/customer')->load($userId);
                        $referree_email = $order->getCustomerEmail();
                        $referree_name = $order->getCustomerName();

                        $referralModel = Mage::getModel('rewardpoints/referral');
                        if (!$referralModel->isSubscribed($referree_email) && $referrer->getEmail() != $referree_email) {
                            $referralModel->setRewardpointsReferralParentId($userId)
                                     ->setRewardpointsReferralEmail($referree_email)
                                     ->setRewardpointsReferralName($referree_name);
                            $referralModel->save();
                        }
                        Mage::getSingleton('rewardpoints/session')->setReferralUser(null);
                        Mage::getSingleton('rewardpoints/session')->unsetAll();
                    }

                    $rewardPoints = Mage::getStoreConfig('rewardpoints/registration/referral_points', Mage::app()->getStore()->getId());
                    $rewardPointsChild = Mage::getStoreConfig('rewardpoints/registration/referral_child_points', Mage::app()->getStore()->getId());
                    
                    if ($rewardPoints > 0 || $rewardPointsChild > 0 && $order->getCustomerEmail()){
                        //$order = $observer->getEvent()->getInvoice()->getOrder();
                        $referralModel = Mage::getModel('rewardpoints/referral');
                        if ($referralModel->isSubscribed($order->getCustomerEmail())) {
                            if (!$referralModel->isConfirmed($order->getCustomerEmail())) {
                                $referralModel->loadByEmail($order->getCustomerEmail());
                                $referralModel->setData('rewardpoints_referral_status', true);
                                $referralModel->setData('rewardpoints_referral_child_id', $order->getCustomerId());
                                $referralModel->save();

                                $parent = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_parent_id'));
                                $child    = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_child_id'));                                

                                try {
                                    if ($rewardPoints > 0){
                                        //$reward_points = Mage::getModel('rewardpoints/account');
                                        //$reward_points->saveCheckedOrder($order->getIncrementId(), $referralModel->getData('rewardpoints_referral_parent_id'), $order->getStoreId(), $rewardPoints, $referralModel->getData('rewardpoints_referral_id'), true);


                                        $reward_model = Mage::getModel('rewardpoints/stats');
                                        $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_parent_id'),
                                            'store_id' => $order->getStoreId(), 'points_current' => $rewardPoints, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                                        $reward_model->setData($post);
                                        $reward_model->save();
                                    }

                                    if ($rewardPointsChild > 0){
                                        //$reward_points2 = Mage::getModel('rewardpoints/account');
                                        //$reward_points2->saveCheckedOrder($order->getIncrementId(), $referralModel->getData('rewardpoints_referral_child_id'), $order->getStoreId(), $rewardPointsChild, $referralModel->getData('rewardpoints_referral_id'), true);



                                        $reward_model = Mage::getModel('rewardpoints/stats');
                                        $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_child_id'),
                                            'store_id' => $order->getStoreId(), 'points_current' => $rewardPointsChild, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                                        $reward_model->setData($post);
                                        $reward_model->save();


                                    }

                                } catch (Exception $e) {
                                    //Mage::getSingleton('session')->addError($e->getMessage());
                                }
                                $referralModel->sendConfirmation($parent, $child, $parent->getEmail());
                            }
                        }
                    }
                }

                public function sales_order_invoice_pay($observer)
                {
                    $rewardPoints = Mage::getStoreConfig('rewardpoints/registration/referral_points', Mage::app()->getStore()->getId());
                    $rewardPointsChild = Mage::getStoreConfig('rewardpoints/registration/referral_child_points', Mage::app()->getStore()->getId());
                    if ($rewardPoints > 0 || $rewardPointsChild > 0){
                        $order = $observer->getEvent()->getInvoice()->getOrder();
                        $referralModel = Mage::getModel('rewardpoints/referral');
                        if ($referralModel->isSubscribed($order->getCustomerEmail())) {
                            if (!$referralModel->isConfirmed($order->getCustomerEmail())) {
                                $referralModel->loadByEmail($order->getCustomerEmail());
                                $referralModel->setData('rewardpoints_referral_status', true);
                                $referralModel->setData('rewardpoints_referral_child_id', $order->getCustomerId());
                                $referralModel->save();

                                $parent = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_parent_id'));
                                $child    = Mage::getModel('customer/customer')->load($referralModel->getData('rewardpoints_referral_child_id'));
                                $referralModel->sendConfirmation($parent, $child, $parent->getEmail());

                                try {
                                    if ($rewardPoints > 0){
                                        //$reward_points = Mage::getModel('rewardpoints/account');
                                        //$reward_points->saveCheckedOrder($order->getIncrementId(), $referralModel->getData('rewardpoints_referral_parent_id'), $order->getStoreId(), $rewardPoints, $referralModel->getData('rewardpoints_referral_id'), true);



                                        $reward_model = Mage::getModel('rewardpoints/stats');
                                        $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_parent_id'),
                                            'store_id' => $order->getStoreId(), 'points_current' => $rewardPoints, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                                        $reward_model->setData($post);
                                        $reward_model->save();

                                    }


                                    if ($rewardPointsChild > 0){
                                        //$reward_points2 = Mage::getModel('rewardpoints/account');
                                        //$reward_points2->saveCheckedOrder($order->getIncrementId(), $referralModel->getData('rewardpoints_referral_child_id'), $order->getStoreId(), $rewardPointsChild, $referralModel->getData('rewardpoints_referral_id'), true);


                                        $reward_model = Mage::getModel('rewardpoints/stats');
                                        $post = array('order_id' => $order->getIncrementId(), 'customer_id' => $referralModel->getData('rewardpoints_referral_child_id'),
                                            'store_id' => $order->getStoreId(), 'points_current' => $rewardPointsChild, 'rewardpoints_referral_id' => $referralModel->getData('rewardpoints_referral_id'));
                                        $reward_model->setData($post);
                                        $reward_model->save();

                                    }

                                } catch (Exception $e) {
                                    //Mage::getSingleton('session')->addError($e->getMessage());
                                }
                            }
                        }
                    }
                }

                public function applyDiscount($observer)
                {
                    /*try {
                        
                        $customer = Mage::getSingleton('customer/session');
                        if ($customer->isLoggedIn()){
                            return Mage::getModel('rewardpoints/discount')->apply($observer->getEvent()->getItem());
                        } else return null;
                        
                        //return $this->_discount->apply($observer->getEvent()->getItem());
                    } catch (Mage_Core_Exception $e) {
                        Mage::getSingleton('checkout/session')->addError($e->getMessage());
                    } catch (Exception $e) {
                       Mage::getSingleton('checkout/session')->addError($e);
                    }*/
                }

		
	}
