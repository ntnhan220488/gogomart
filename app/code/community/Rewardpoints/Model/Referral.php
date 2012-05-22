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
class Rewardpoints_Model_Referral extends Mage_Core_Model_Abstract
{
    
    const XML_PATH_SUBSCRIPTION_EMAIL_TEMPLATE       = 'rewardpoints/registration/subscription_email_template';
    const XML_PATH_SUBSCRIPTION_EMAIL_IDENTITY       = 'rewardpoints/registration/subscription_email_identity';

    const XML_PATH_CONFIRMATION_EMAIL_TEMPLATE       = 'rewardpoints/registration/confirmation_email_template';
    const XML_PATH_CONFIRMATION_EMAIL_IDENTITY       = 'rewardpoints/registration/confirmation_email_identity';
    


    public function _construct()
    {
        parent::_construct();
        $this->_init('rewardpoints/referral');
    }

    public function getInvites($id){
        return $this->getCollection()->addClientFilter($id);
    }

    public function loadByEmail($customerEmail)
    {
        $this->addData($this->getResource()->loadByEmail($customerEmail));
        return $this;
    }

    public function subscribe(Mage_Customer_Model_Customer $parent, $email, $name)
    {
        $this->setRewardpointsReferralParentId($parent->getId())
             ->setRewardpointsReferralEmail($email)
             ->setRewardpointsReferralName($name);
        return $this->save() && $this->sendSubscription($parent, $email, $name);
    }

    public function isSubscribed($email)
    {
        $collection = $this->getCollection()->addEmailFilter($email);
        return $collection->count() ? true : false;
    }

    public function isConfirmed($email)
    {
        $collection = $this->getCollection()->addFlagFilter(0);
        $collection->addEmailFilter($email);
        return $collection->count() ? false : true;
    }

    public function sendSubscription(Mage_Customer_Model_Customer $parent, $destination, $destination_name)
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        
        //$template = Mage::getStoreConfig(self::XML_PATH_SUBSCRIPTION_EMAIL_TEMPLATE, $this->getStoreId());

        $email = Mage::getModel('core/email_template');
        /* @var $email Mage_Core_Model_Email_Template */
        //$email->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()));


        $template = Mage::getStoreConfig(self::XML_PATH_SUBSCRIPTION_EMAIL_TEMPLATE, $this->getStoreId());
        $recipient = array(
            'email' => $destination,
            'name'  => $destination_name
        );

        $sender  = array(
            'name' => strip_tags($parent->getFirstname().' '.$parent->getLastname()),
            'email' => strip_tags($parent->getEmail())
        );

        $email->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    $template,
                    $sender,
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'parent'   => $parent,
                        'referral' => $this,
                        'store_name' => Mage::getModel('core/store')->load(Mage::app()->getStore()->getCode())->getName()
                    )
                );

        $translate->setTranslateInline(true);

        return $email->getSentSuccess();
    }

    public function sendConfirmation(Mage_Customer_Model_Customer $parent, Mage_Customer_Model_Customer $child, $destination)
    {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');
        /* @var $email Mage_Core_Model_Email_Template */        

        $template = Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_EMAIL_TEMPLATE, $this->getStoreId());
        $recipient = array(
            'email' => $destination,
            'name'  => $destination
        );

        $sender  = Mage::getStoreConfig(self::XML_PATH_CONFIRMATION_EMAIL_IDENTITY);

        $email->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    $template,
                    $sender,
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'parent'   => $parent,
                        'child'   => $child,
                        'referral' => $this,
                        'store_name' => Mage::getModel('core/store')->load(Mage::app()->getStore()->getCode())->getName()
                    )
                );

        $translate->setTranslateInline(true);
        return $email->getSentSuccess();
    }

}