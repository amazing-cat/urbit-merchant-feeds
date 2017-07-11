<?php

/**
 * Class Urbit_InventoryFeed_Block_Xml
 */
class Urbit_InventoryFeed_Block_Xml extends Mage_Core_Block_Template
{
    /**
     * @var Urbit_InventoryFeed_Model_List_Product
     */
    protected $_products;

    /**
     * Return json feed data
     * @return string
     */
    public function getProductsJson()
    {
        /** @var Urbit_InventoryFeed_Helper_Feed $feedHelper */
        $feedHelper = Mage::helper("inventoryfeed/feed");

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($this->_products);
        }

        return $feedHelper->getDataJson();
    }

    /**
     * @param array $filter
     */
    public function setProductsByFilter($filter = array())
    {
        $this->_products = Mage::getModel("inventoryfeed/list_product", $filter);
    }
}