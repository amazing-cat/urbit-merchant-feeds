<?php

/**
 * Class Urbit_ProductFeed_Block_Xml
 */
class Urbit_ProductFeed_Block_Xml extends Mage_Core_Block_Template
{
    /**
     * @var Urbit_ProductFeed_Model_List_Product
     */
    protected $_products;

    /**
     * Return json feed data
     * @return string
     */
    public function getProductsJson()
    {
        /** @var Urbit_ProductFeed_Helper_Feed $feedHelper */
        $feedHelper = Mage::helper("productfeed/feed");

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
        $this->_products = Mage::getModel("productfeed/list_product", $filter);
    }
}