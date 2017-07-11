<?php

/**
 * Class Urbit_InventoryFeed_Model_List_Product
 */
class Urbit_InventoryFeed_Model_List_Product implements IteratorAggregate
{
    /**
     * @var array
     */
    protected $_filterDefault = array(
        'category'     => false,
        'tag'          => false,
        'stock'        => false,
    );

    /**
     * @var array
     */
    protected $_filter = array();

    /**
     * Product items
     * @var array
     */
    protected $_products = array();

    /**
     * Urbit_InventoryFeed_Model_List_Product constructor.Z
     * @param array $filter
     */
    public function __construct($filter = array())
    {
        $this->setFilter($filter);
    }

    /**
     * Fetching data for iteration by IteratorAggregate interface
     * @return array
     */
    public function getIterator()
    {
        return $this->getProducts();
    }

    /**
     * Load products data to current block by filter
     * @param bool $reload
     * @return $this
     */
    protected function loadProducts($reload = false)
    {
        if (!$this->_products || $reload) {
            $this->_products = Mage::getModel('catalog/product')
                ->getCollection()
            ;

            $filter = $this->getFilter();

            if ($filter['category'] && !empty($filter['category'])) {
                // TODO: set category filter
            }

            if ($filter['tag'] && !empty($filter['tag'])) {
                // TODO: set tag filter
            }

            if ($filter['stock'] && $filter['stock'] > 0) {
                // TODO: set minimal stock filter
            }
        }

        return $this->_products;
    }

    /**
     * Get product collection
     */
    public function getCollection()
    {
       return $this->loadProducts();
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->getCollection()->load();
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * @param array $filter
     * @return $this
     * @throws Exception
     */
    public function setFilter($filter)
    {
        if (!is_array($filter)) {
            throw new Exception("Product feed filter should be an array");
        }

        $this->_filter = $filter + $this->_filterDefault;

        return $this;
    }
}
