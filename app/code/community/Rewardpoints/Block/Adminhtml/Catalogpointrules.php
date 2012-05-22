<?php


class Rewardpoints_Block_Adminhtml_Catalogpointrules extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_catalogpointrules';
        $this->_blockGroup = 'rewardpoints';
        $this->_headerText = Mage::helper('rewardpoints')->__('Point rules');
        //$this->_addButton ('apply_rules', array('label'=> Mage::helper('rewardpoints')->__('Apply all rules'), 'class' => 'save', 'onclick'   => 'setLocation(\''.$this->getApplyRulesUrl().'\')'));
        parent::__construct();
    }

    public function getApplyRulesUrl()
    {
        return $this->getUrl('*/*/applyall');
    }
}
