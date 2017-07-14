<?php

namespace Urbit\ProductFeed\Model\Collection;

use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ProductFactory as MagentoProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as MagentoProductCollection;
use Exception;
use IteratorAggregate;

/**
 * Class Product
 * @package Urbit\ProductFeed\Model\Collection
 */
class Product implements IteratorAggregate
{
    /**
     * @var array
     */
    protected $_filterDefault = [
        'category' => false,
        'stock'    => false,
        'attribute_name'  => false,
        'attribute_value' => false,
    ];

    /**
     * @var array
     */
    protected $_filter = [];

    /**
     * @var MagentoProductCollection
     */
    protected $_products;

    /**
     * @var MagentoProduct
     */
    protected $_productModel;

    /**
     * Product Collection constructor.
     * @param array $filter
     * @param MagentoProductFactory $productFactory
     */
    public function __construct($filter = array(), MagentoProductFactory $productFactory)
    {
        $this->_filter = $filter;
        $this->_productModel = $productFactory->create();
    }

    /**
     * Fetching data for iteration by IteratorAggregate interface
     * @return MagentoProductCollection
     */
    public function getIterator()
    {
        return $this->getProducts();
    }

    /**
     * Load products data to current block by filter
     * @param bool $reload
     * @return MagentoProductCollection
     */
    protected function loadProducts($reload = false)
    {
        if (!$this->_products || $reload) {
            $this->_products = $this->_productModel->getCollection();

            $filter = $this->getFilter();

            //filtering by category
            if ($filter['category'] && !empty($filter['category'])) {
                $this->_products->addCategoriesFilter(['in' => explode(',',$filter['category'])]);
            }
       }

        return $this->_products;
    }


    /**
     * Get product collection
     * @return MagentoProductCollection
     */
    public function getCollection()
    {
        return $this->loadProducts();
    }

    /**
     * @return MagentoProductCollection
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