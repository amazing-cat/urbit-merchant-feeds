<?php

namespace Urbit\ProductFeed\Model\Feed;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product\Interceptor as MagentoProduct;
use Exception;


/**
 * Class FeedProduct
 * Working and process with Magento Product
 * @package Urbit\ProductFeed\Model\Feed
 *
 * Special properties:
 * @property $isSimple
 *
 * Field properties (for feed $data property):
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $link
 * @property array  $prices
 * @property array  $brands
 * @property array  $attributes
 * @property string $gtin
 * @property array  $categories
 * @property string $image_link
 * @property array  $additional_image_links
 * @property string $item_group_id
 * @property array  $dimensions
 *
 * @property string $sizeType
 * @property string $size
 * @property string $color
 * @property string $gender
 * @property string $material
 * @property string $pattern
 * @property string $age_group
 * @property string $condition
 */
class FeedProduct
{
    /**
     * Magento product object
     * @var MagentoProduct
     */
    protected $_product;

    /**
     * Magento product resource object
     * @var
     */
    protected $_resource;

    /**
     * Array with product fields
     * @var array
     */
    protected $_data = [];

    /**
     * @var StoreManagerInterface
     */
    protected $_store;

    /**
     * FeedProduct constructor.
     * @param MagentoProduct $product
     * @param StoreManagerInterface $store
     */
    public function __construct(
        MagentoProduct $product,
        StoreManagerInterface $store
    ) {
        $this->_product = $product;
        $this->_store = $store;
    }

    /**
     * Get product data for feed
     * @return array
     */
    public function toArray()
    {
        if (empty($this->_data)) {
            $this->process();
        }

        return $this->_data;
    }

    /**
     * Get feed product data fields
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        if (stripos($name, 'is') === 0 && method_exists($this, $name)) {
            return $this->{$name}();
        }

        $getMethod = "get{$name}";

        if (method_exists($this, $getMethod)) {
            return $this->{$getMethod}();
        }

        return null;
    }

    /**
     * Set feed product data fields
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {

        $setMethod = "set{$name}";

        if (method_exists($this, $setMethod)) {
            $this->{$setMethod}($value);
            return;
        }

        $this->_data[$name] = $value;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this|mixed|null
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        $property      = strtolower(preg_replace("/^unset/", $name));
        $propertyExist = isset($this->_data[$property]);

        if ($propertyExist) {
            if (stripos($name, 'unset') === 0) {
                unset($this->_data[$property]);
                return $this;
            }

            if (stripos($name, 'get') === 0) {
                return $this->{$property};
            }

            if (stripos($name, 'set') === 0 && isset($arguments[0])) {
                $this->{$property} = $arguments[0];
                return $this;
            }
        }

        throw new Exception("Unknown method {$name}");
    }

    /**
     * Process Magento Product
     * @return bool
     */
    public function process()
    {
        // TODO: Add checking product on "simple" type
        if (!$this->isSimple()) {
            return false;
        }

        $product = $this->_product;

        $this->id          = (string) $product->getId();
        $this->name        = $product->getName();
        $this->description = $product->getDescription();
        $this->link        = $product->getProductUrl();

        $this->processPrices();
        $this->processCategories();
        $this->processImages();
        $this->processVariableProduct();
        $this->processAttributes();
        $this->processConfigurableFields();


        return true;
    }

    /**
     * Process product prices
     */
    protected function processPrices()
    {
        // TODO: Process regular prices
        // TODO: Process discount prices
        // TODO: Process special price rules

        $this->prices = [];
    }
    /**
     * Process product categories
     */
    protected function processCategories()
    {
        // TODO: process product categories
    }

    /**
     * get all parent category
     * @param  array $categories List of found categories
     * @param  int   $parentId   Id of parent category
     * @return array             Full list of categories
     */
    public function processParentCategory($categories, $parentId)
    {
        // TODO: get product parent categories
    }

    /**
     * Process product images
     */
    protected function processImages()
    {
        // TODO: Get product images
    }

    /**
     * Process on part of configurable product
     */
    protected function processVariableProduct()
    {
        // TODO: Get feed "item_group_id" property if product is part of Variable product
    }

    /**
     * Process configurable fields (associated custom product attributes)
     */
    protected function processConfigurableFields()
    {
        // TODO: Process product attributes to feed root properties
    }

    /**
     * Process product attributes
     */
    protected function processAttributes()
    {
        // TODO: Process additional product attributes to feed "attributes" property
    }

    /**
     * Check if product have simple type
     * @return bool
     */
    public function isSimple()
    {
        // TODO: Add checking product on "simple" type
        return true;
    }
}