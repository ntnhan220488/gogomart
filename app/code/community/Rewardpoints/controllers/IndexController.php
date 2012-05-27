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

        /*$handles = array('default');
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $handles[] = 'customer_account';
        }
        $this->loadLayout($handles);
        $this->renderLayout();*/

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
        Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
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

        Mage::getSingleton('rewardpoints/session')->setProductChecked(0);
        Mage::getSingleton('rewardpoints/session')->setShippingChecked(0);
        
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