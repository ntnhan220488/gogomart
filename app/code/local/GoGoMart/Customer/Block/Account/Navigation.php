<?php
class GoGoMart_Customer_Block_Account_Navigation
extends Mage_Customer_Block_Account_Navigation
{
    public function removeLinkByName($name)
    {
        foreach($this->_links as $k=>$v){
            if($v->getName() == $name){
                unset($this->_links[$k]);
            }
        }
    }
}
