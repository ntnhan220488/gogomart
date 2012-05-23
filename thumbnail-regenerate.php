<?php
require 'app/Mage.php';
Mage::app();

$products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
foreach ($products as $product) {
    if (!$product->hasImage()) continue;
    if (!$product->hasSmallImage()) $product->setSmallImage($product->getImage());
    if (!$product->hasThumbnail()) $product->setThumbnail($product->getImage());
    $product->save();
}
?>
