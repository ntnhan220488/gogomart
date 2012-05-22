<?php

$sqls = array();
$connection = Mage::getSingleton('core/resource')
                 ->getConnection('rewardpoints_read');
$select = $connection->select()
                    ->from('information_schema.COLUMNS')
                    ->where("COLUMN_NAME='convertion_rate' AND TABLE_NAME='{$this->getTable('rewardpoints_account')}'");
$data = $connection->fetchRow($select);
if(!isset($data['COLUMN_NAME'])){
    $sqls[] = "ALTER TABLE {$this->getTable('rewardpoints_account')} ADD COLUMN `convertion_rate` FLOAT( 11 ) NULL AFTER `date_end`;";
}

if ($sqls != array()){
    $installer = $this;
    $installer->run(implode(' ',$sqls));
    $installer->endSetup();
}