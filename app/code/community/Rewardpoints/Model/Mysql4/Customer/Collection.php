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
class Rewardpoints_Model_Mysql4_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select
            ->from($this->getTable('rewardpoints/rewardpoints_account'),array(new Zend_Db_Expr('SUM('.$this->getTable('rewardpoints/rewardpoints_account').'.points_current) AS all_points_accumulated'),new Zend_Db_Expr('SUM('.$this->getTable('rewardpoints/rewardpoints_account').'.points_spent) AS all_points_spent')))
            ->where($this->getTable('rewardpoints/rewardpoints_account').'.customer_id = e.entity_id');


        if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
            $select->where(" (".$this->getTable('rewardpoints/rewardpoints_account').".order_id = '".Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW."' or '".Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN."' or ".$this->getTable('rewardpoints/rewardpoints_account').".order_id = '".Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION."'
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".order_id in  (SELECT increment_id
                       FROM ".$this->getTable('sales/order')." AS orders
                       WHERE orders.state IN ('processing','complete'))
                 ) ");
        } else {
            $table_sales_order = $this->getTable('sales/order').'_varchar';
            $select->where(" (".$this->getTable('rewardpoints/rewardpoints_account').".order_id = '".Rewardpoints_Model_Stats::TYPE_POINTS_REVIEW."' or '".Rewardpoints_Model_Stats::TYPE_POINTS_ADMIN."' or ".$this->getTable('rewardpoints/rewardpoints_account').".order_id = '".Rewardpoints_Model_Stats::TYPE_POINTS_REGISTRATION."'
                   or ".$this->getTable('rewardpoints/rewardpoints_account').".order_id in (SELECT increment_id
                                       FROM ".$this->getTable('sales/order')." AS orders
                                       WHERE orders.entity_id IN (
                                           SELECT order_state.entity_id
                                           FROM ".$table_sales_order." AS order_state
                                           WHERE order_state.value <> 'canceled'
                                           AND order_state.value in ('processing','complete'))
                                        ) ) ");
        }
        
        if (Mage::getStoreConfig('rewardpoints/default/points_duration', Mage::app()->getStore()->getId())){
            $select->where('( '.$this->getTable('rewardpoints/rewardpoints_account').'.date_end >= NOW() OR '.$this->getTable('rewardpoints/rewardpoints_account').'.date_end IS NULL)');
        }

        $select->group($this->getTable('rewardpoints/rewardpoints_account').'.customer_id');

        
        return $this;
    }

}