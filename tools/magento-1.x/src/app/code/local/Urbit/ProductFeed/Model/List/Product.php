<?php

/**
 * Class Urbit_ProductFeed_Model_List_Product
 */
class Urbit_ProductFeed_Model_List_Product implements IteratorAggregate
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
     * Urbit_ProductFeed_Model_List_Product constructor.Z
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

            /**
             * Category filter
             */
            if ($filter['category'] && !empty($filter['category'])) {
                $this->_products
                    ->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id', null, 'left')
                    ->addAttributeToFilter('category_id', array('in' => explode(',',$filter['category'])));
                $this->_products->getSelect()->group('e.entity_id');
            }

            /**
             * Tag filter
             */
            if ($filter['tag'] && !empty($filter['tag'])) {
                $tagged_ids = [];
                foreach (explode(',',$filter['tag']) as $tag) {
                    $tag_id = Mage::getModel('tag/tag')->load($tag)->getId();
                    $tagged_products = Mage::getResourceModel('tag/product_collection')->addTagFilter($tag_id);
                    foreach($tagged_products as $tagged_product){
                        array_push($tagged_ids, $tagged_product->getId());
                    }
                }
                $this->_products->addAttributeToFilter('entity_id', array('in' => $tagged_ids));
            }

            /**
             * minimal stock filter
             */
            if ($filter['stock'] && $filter['stock'] > 0) {
                $this->_products
                    ->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                    ->addAttributeToFilter('qty', array('gt' => $filter['stock']));
                $this->_products->getSelect()->group('e.entity_id');
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
