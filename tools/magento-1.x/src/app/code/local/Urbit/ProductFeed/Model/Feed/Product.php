<?php

/**
 * Class Urbit_ProductFeed_Model_Feed_Product
 *
 * Special properties:
 * @property $isSimple
 * @property $currencyCode
 *
 * Field properties (for feed $data property):
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $link
 * @property array  $prices
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
class Urbit_ProductFeed_Model_Feed_Product
{
    /**
     * Array with product fields
     * @var array
     */
    protected $data = array();

    /**
     * Magento product object
     * @var Mage_Catalog_Model_Product
     */
    protected $product;

    /**
     * Magento product resource object
     * @var Mage_Catalog_Model_Resource_Product
     */
    protected $resource;

    /**
     * Urbit_ProductFeed_Model_Feed_Product constructor.
     * @param Mage_Catalog_Model_Product $product
     */
    public function __construct(Mage_Catalog_Model_Product $product)
    {
        $this->product  = $product;
        $this->resource = $product->getResource();
    }

    /**
     * Get feed product data
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get feed product data fields
     * @param string $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
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

        $this->data[$name] = $value;
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
        $propertyExist = isset($this->data[$property]);

        if ($propertyExist) {
            if (stripos($name, 'unset') === 0) {
                unset($this->data[$property]);
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
     * Process Magento product and get data for feed
     * @return bool
     */
    public function process()
    {

        if (!$this->isSimple) {
            return false;
        }

        $product = $this->product;

        $this->id          = (string) $product->getId();
        $this->name        = $product->getName();
        $this->description = $product->getDescription();
        $this->link        = $product->getProductUrl();

        $this->processCategories();
        $this->processImages();
        $this->processVariableProduct();
        $this->processConfigurableFields();
        
        return true;
    }

    /**
     * Process product prices
     */
    protected function processPrices()
    {
        $product = $this->product;

        // Regular price
        $this->prices = array(
            array(
                "currency" => $this->currencyCode,
                "value"    => number_format($this->product->getPrice(), 2),
                "type"     => "regular",
            ),
        );

        // Special price with date range

        if ($product->getSpecialPrice()) {
            $from  = (new DateTime($product->getSpecialFromDate()))->format('c');
            $to    = (new DateTime($product->getSpecialToDate()))->format('c');
            $value = number_format($product->getSpecialPrice(), 2);

            $this->prices[] = array(
                "currency" => $this->currencyCode,
                "value"    => $value,
                "type"     => "sale",
                'price_effective_date' => "{$from}/{$to}",
            );
        }

        // current special price

        $rule = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $product->getPrice());

        if ($rule) {
            $this->prices[] = array(
                "currency" => $this->currencyCode,
                "value"    => $rule,
                "type"     => "sale",
            );
        }
    }

    /**
     * Process product categories
     */
    protected function processCategories()
    {
        $category_ids = $this->product->getCategoryIds();

        if (empty($category_ids)) {
            return;
        }

        $categories = [];

        foreach ($category_ids as $category_id) {
            $category = $this->model('catalog/category', 'load', $category_id);

            if (!$category) {
                continue;
            }

            $categories[] = [
                'id'       => (int) $category->getId(),
                'parentId' => (int) $category->getParentId(),
                'name'     => $category->getName(),
            ];
        }

        if (!empty($categories)) {
            $this->categories = $categories;
        }
    }

    /**
     * Process product images
     */
    protected function processImages()
    {
        if ($images = $this->product->getMediaGalleryImages()) {
            $additional = array();

            foreach ($images as $image) {
                $additional[] = $image['url'];
            }

            $this->additional_image_links = $additional;
        }

        $this->image_link = Mage::helper('catalog/image')->init($this->product, 'image');
    }

    /**
     * Process on part of configurable product
     */
    protected function processVariableProduct()
    {
        $parent_ids = $this->model('catalog/product_type_configurable', 'getParentIdsByChild', $this->id);

        if (!$parent_ids) {
            $parent_ids = $this->model('catalog/product_type_grouped', 'getParentIdsByChild', $this->id);
        }

        if (isset($parent_ids[0])){
            $this->item_group_id = $this->model('catalog/product', 'load', $parent_ids[0])->getSku();
        }
    }

    /**
     * Process configurable fields (associated custom product attributes)
     */
    protected function processConfigurableFields()
    {

        $fields = $this->model("productfeed/config", 'get', 'fields');

        // TODO: need mpn

        $ean = $this->attr($fields['ean']);

        if ($ean) {
            $this->gtin = $ean;
        }

        // TODO: need unit for weigh
        $this->dimensions = array(
            'weight' => array(
                'value' => (float) $this->product->getWeigth(),
                'unit'  => ''
            ),
        );

        foreach (array('height', 'length', 'width') as $key) {
            $keyField = "dimention_{$key}";

            if (!isset($fields[$keyField]) || !$fields[$keyField]) {
                continue;
            }

            $attr = $this->attr($fields[$keyField]);

            if (!$attr) {
                continue;
            }

            // TODO: dimensions unit value
            $this->dimensions[$key] = array(
                'value' => (int) $attr,
                'unit'  => '',
            );
        }

        foreach (array("sizeType", "size", "color", "gender", "material", "pattern", "age_group", "condition") as $key) {
            if (!isset($fields[$key]) || !$fields[$key]) {
                continue;
            }

            $attr = $this->attr($fields[$key]);

            if ($attr === null) {
                continue;
            }

            $this->{$key} = $attr;
        }
    }

    /**
     * Check if product have simple type
     * @return bool
     */
    public function isSimple()
    {
        return $this->product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
    }

    /**
     * Helper function
     * Get currency code for current store
     * @return string
     */
    protected function getCurrencyCode()
    {
        return Mage::app()->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Helper function
     * Call function of other models
     * @param string $name
     * @param string $func
     * @param mixed $param
     * @return mixed
     */
    protected function model($name, $func, $param)
    {
        return Mage::getModel($name)->{$func}($param);
    }

    /**
     * Helper function
     * Get product attribute value
     * @param string $name
     * @return mixed
     */
    protected function attr($name)
    {
        $attr = $this->resource->getAttribute($name);

        if (!$attr) {
            return null;
        }

        return $attr->getFrontend()->getValue($this->product);
    }
}