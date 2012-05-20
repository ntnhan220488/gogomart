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
class Rewardpoints_IndexController extends Mage_Core_Controller_Front_Action
{

    public function testAction(){
        echo '<pre>';
        //print_r(Mage::getSingleton('checkout/cart')->getQuote());
        //die;
        $order = Mage::getModel('sales/order')->load(61);
        /*$quote_order = Mage::getModel('sales/convert_order')->toQuote($order);
        $quote_order->setBillingAddress($order->getBillingAddress());
        $quote_order->setShippingAddress($order->getShippingAddress());
*/

        $order_shipping_address = Mage::getModel('sales/order_address')->load($order->getShippingAddressId());
        $customer_shipping_address = $order_shipping_address->getCustomerAddressId();

        $order_billing_address = Mage::getModel('sales/order_address')->load($order->getBillingAddressId());
        $customer_billing_address = $order_billing_address->getCustomerAddressId();

        //customer_address_id

        //print_r($customer_billing_address);
        //billing_address_id
        //shipping_address_id
        echo ">>>>>>>>>>>>>>>>>>>> FIN ORDER <<<<<<<<<<<<<<<<<<<<<<<";
        $quote_tmp = Mage::getModel('sales/quote');


        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        foreach($quote->getAddressesCollection() as $my_quote){
            echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> <br />';
            //print_r($my_quote->getBaseSubtotal());
            /*[address_id] => 315
                                            [quote_id] => 71
                                            [created_at] => 2010-09-27 16:19:54
                                            [updated_at] => 2010-09-27 16:20:21
                                            [customer_id] => 2
                                            [save_in_address_book] => 0
                                            [customer_address_id] => 1
                                            [address_type]*/
            //print_r($my_quote->getAddressId());

            //$quote_address = Mage::getModel('sales/quote_address')->load($my_quote->getAddressId());
            //print_r($quote_address->getId());


            //$address = Mage::getModel('customer/address')->load($my_quote->getCustomerAddressId());
            //print_r($address->getId());

            if ($my_quote->getAddressType() == 'shipping' && $my_quote->getCustomerAddressId() == $customer_shipping_address){
                $quote->setShippingAddress($my_quote);
                //print_r($my_quote->getData());
                //echo $quote->getBaseSubtotal();
                $quote_tmp->setShippingAddress($my_quote);
            } elseif($my_quote->getAddressType() == 'billing' && $my_quote->getCustomerAddressId() == $customer_billing_address) {
                $quote->setBillingAddress($my_quote);
                $quote_tmp->setBillingAddress($my_quote);
            }

            /*print_r($my_quote->getCustomerAddressId());
            echo " ";
            print_r($my_quote->getData('is_virtual'));
            echo " ";
            print_r($my_quote->getData('address_id'));
            echo " ";
            print_r($my_quote->getData('base_subtotal'));
            echo " ";
            print_r($my_quote->getData('address_type'));*/
        }

        $order->setQuote($quote_tmp);
        $address = $order->getQuote()->getShippingAddress();

        //print_r($address->getBaseSubtotal());
        //die;

        //$address = $order->getQuote()->getShippingAddress()->setBaseSubtotal($order->getBaseSubtotal());
        //echo $order->getQuote()->getShippingAddress()->getData('base_subtotal');
        //print_r($order);

        echo Mage::helper('rewardpoints/data')->getPointsOnOrder($order);

        die;

        die;


        $convertQuote = Mage::getModel('sales/convert_order');
        /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
        //$order = Mage::getModel('sales/order');
        $quote_tmp = $convertQuote->toQuoteShippingAddress($order);
        /* @var $order Mage_Sales_Model_Order */

        $address = $convertQuote->toQuoteShippingAddress($order);

        $quote_tmp->setBillingAddress($address);
        $quote_tmp->setShippingAddress($address);
        //$quote_tmp->addAddress($order->getShippingAddress());
        
        //die;
        

        //$quote_tmp->addressToQuoteAddress($order->getShippingAddress());
        //$order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));
        foreach ($order->getAllItems() as $item) {
            //echo 'ici';
            $quote_tmp->addItem($convertQuote->itemToShipmentItem($item));
        }



        //$quote_tmp->collectTotals();


        //print_r($quote_tmp);

        

        print_r($quote_tmp->getShippingAddress()->getBaseSubtotal());

        die;
        

        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $shippingAddress = Mage::getModel('sales/quote_address')
                ->setData($order->getShippingAddress());
        $quote->setShippingAddress($shippingAddress);

        //$quote_order_temp = Mage::getModel('sales/convert_order')->toQuoteShippingAddress($order);


        $quote_order = Mage::getModel('sales/convert_order')->toQuote($order);

        //$quote_order->toQuoteShippingAddress(Mage_Sales_Model_Order $order)

        print_r($quote_order->getShippingAddress()->getBaseSubtotal());
        die;

        //print_r($quote->getShippingAddress()->getBaseSubTotal());
        //die;


        //$quote->setData($order->getData());
        $order->setQuote($quote);

        //$address = $order->getQuote()->getShippingAddress()->setBaseSubtotal($order->getBaseSubtotal());
        //echo $order->getQuote()->getShippingAddress()->getData('base_subtotal');
        print_r($order);
        die;


        //print_r($order->getQuote()->getBaseSubtotal());
        //die;


        /*print_r($order->getQuote());
        die;
        print_r(Mage::getModel('sales/convert_order')->toQuoteShippingAddress($order));
        die;

        $order->setQuote($order->getData());
        $order->getQuote()->setBillingAddress($order->getBillingAddress());
        $order->getQuote()->setShippingAddress($order->getShippingAddress());

        print_r($order);
        die;*/

        //$order = Mage::getModel('sales/order')->load(57);
        //$quote_order = Mage::getModel('sales/convert_order')->toQuote($order);
        //$quote_order->setBaseSubtotal(12);
        //$order->setQuote($quote_order);

        /*echo '<pre>';
        print_r($order);*/
        echo $order->getData('base_subtotal');

        echo '<br />';


        echo Mage::helper('rewardpoints/data')->getPointsOnOrder($order);
        die;
    }


    public function indexAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session         = Mage::getSingleton('core/session');
            $email           = trim((string) $this->getRequest()->getPost('email'));
            $name            = trim((string) $this->getRequest()->getPost('name'));

            
            $customerSession = Mage::getSingleton('customer/session');
            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }
                
                if ($name == ''){
                    Mage::throwException($this->__('Please enter your friend name.'));
                }
                $referralModel = Mage::getModel('rewardpoints/referral');

                $customer = Mage::getModel('customer/customer')
                                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                                ->loadByEmail($email);

                if ($referralModel->isSubscribed($email) || $customer->getEmail() == $email) {
                    Mage::throwException($this->__('This email has been already submitted.'));
                } else {
                    if ($referralModel->subscribe($customerSession->getCustomer(), $email, $name)) {
                        $session->addSuccess($this->__('This email was successfully invited.'));
                    } else {
                        $session->addException($this->__('There was a problem with the invitation.'));
                    }
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('%s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the invitation.'));
            }
        }

        $this->loadLayout();
        $this->renderLayout();


    }

    public function referralAction()
    {
        $this->indexAction();
    }

    public function pointsAction()
    {
        $this->indexAction();
    }


    public function goReferralAction(){
        $userId = (int) $this->getRequest()->getParam('referrer');
        Mage::getSingleton('rewardpoints/session')->setReferralUser($userId);
        //Mage::getSingleton('rewardpoints/session')->getReferralUser()
        $url = Mage::getUrl();
        $this->getResponse()->setRedirect($url);
    }

    public function removequotationAction(){
        Mage::getSingleton('customer/session')->setProductChecked(0);
        Mage::helper('rewardpoints/event')->setCreditPoints(0);
        $refererUrl = $this->_getRefererUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
        }
        $this->getResponse()->setRedirect($refererUrl);
    }

    public function quotationAction(){
        $session = Mage::getSingleton('core/session');
        $points_value = $this->getRequest()->getPost('points_to_be_used');
        if (Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId())){
            if ((int)Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId()) < $points_value){
                $points_max = (int)Mage::getStoreConfig('rewardpoints/default/max_point_used_order', Mage::app()->getStore()->getId());
                $session->addError($this->__('You tried to use %s loyalty points, but you can use a maximum of %s points per shopping cart.', $points_value, $points_max));
                $points_value = $points_max;
            }
        }
        $quote_id = Mage::helper('checkout/cart')->getCart()->getQuote()->getId();

        Mage::getSingleton('customer/session')->setProductChecked(0);
        Mage::helper('rewardpoints/event')->setCreditPoints($points_value);

        $refererUrl = $this->_getRefererUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
        }
        $this->getResponse()->setRedirect($refererUrl);
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
        if ('referral' == $action){
            $loginUrl = Mage::helper('customer')->getLoginUrl();

            if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
        }
    }
    
}