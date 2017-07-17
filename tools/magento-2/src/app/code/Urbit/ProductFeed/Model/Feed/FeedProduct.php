<?php

namespace Urbit\ProductFeed\Model\Feed;

use Magento\Directory\Model\Currency;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface as Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory as MagentoProductConfigurableFactory;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Catalog\Model\Product\Type\AbstractType as ProductType;
use Magento\Catalog\Model\Product\Type\Simple as ProductTypeSimple;

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
     * Factory of configurable products.
     * Used for fetching parent product / item group id
     * @var MagentoProductConfigurableFactory
     */
    protected $_configurableFactory;

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
     * @var CategoryCollection
     */
    protected $_categoryCollectionFactory;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var Currency
     */
    protected $_currency;

    /**
     * FeedProduct constructor.
     * @param MagentoProduct $product
     * @param MagentoProductConfigurableFactory $configurableFactory
     * @param ProductRepository $productRepository
     * @param StoreManagerInterface $store
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Image $imageHelper
     * @param Currency $currency
     */
    public function __construct(
        MagentoProduct $product,
        MagentoProductConfigurableFactory $configurableFactory,
        ProductRepository $productRepository,
        StoreManagerInterface $store,
        CategoryCollectionFactory $categoryCollectionFactory,
        Image $imageHelper,
        Currency $currency
    ) {
        $this->_product  = $productRepository->getById($product->getId());
        $this->_store    = $store;
        $this->_currency = $currency;
        $this->_configurableFactory = $configurableFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_imageHelper = $imageHelper;
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
        $prices    = [];
        $product   = $this->_product;
        $currency  = $this->_getCurrency();
        $priceInfo = $product->getPriceInfo();

        $regularPrice = $priceInfo->getPrice('regular_price');
        $finalPrice = $priceInfo->getPrice('final_price');

        $regularPriceValue = $regularPrice->getValue();
        $finalPriceValue = $finalPrice->getValue();

        if ($regularPriceValue) {
            $prices[] = [
                "currency" => $currency,
                "value"    => number_format($regularPriceValue, 2),
                "type"     => "regular",
            ];
        }

        if ($finalPriceValue && $finalPriceValue !== $regularPriceValue) {
            $prices[] = [
                "currency" => $currency,
                "value"    => number_format($finalPriceValue, 2),
                "type"     => "special",
            ];
        }

        $this->prices = $prices;
    }
    /**
     * Process product categories
     */
    protected function processCategories()
    {
        $categoryIds =  $this->_product->getCategoryIds();

        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->_categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', $categoryIds)
        ;

        $productCategories = [];

        /** @var Category $category */
        foreach ($categoryCollection as $category) {
            $productCategories[] = [
                'id'       => (int) $category->getId(),
                'name'     => $category->getName(),
                'parentId' => (int) $category->getParentId(),
            ];

            $this->processParentCategory($category, $productCategories);
        }

        if (!empty($productCategories)) {
            $this->categories = $productCategories;
        }
    }

    /**
     * get all parent category
     * @param  Category $category Category to process
     * @param  array $categories List of found categories
     */
    public function processParentCategory($category, &$categories)
    {
        $parentId = (int) $category->getParentId();

        if (!$parentId) {
            return;
        }

        foreach ($categories as $cat) {
            if ($cat['id'] == $parentId) {
                return;
            }
        }

        /** @var CategoryCollection $categoryCollection */
        $collection = $this->_categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', $parentId)
        ;

        /** @var Category $parentCategory */
        $parentCategory = $collection->getFirstItem();

        $newCategory = [
            'id'   => (int) $parentCategory->getId(),
            'name' => $parentCategory->getName(),
        ];

        if ((int) $parentCategory->getParentId()) {
            $newCategory['parentId'] = $parentCategory->getParentId();
            $categories[] = $newCategory;
            $this->processParentCategory($parentCategory, $categories);
        } else {
            $categories[] = $newCategory;
        }
    }

    /**
     * Process product images
     */
    protected function processImages()
    {
        $product = $this->_product;

        if ($product->getImage()) {
            $mainImage = $this->_getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product'
                . $this->_product->getImage()
            ;

            $this->image_link = $mainImage;
        }

        if ($images = $this->_product->getMediaGalleryImages()) {
            $additional = [];

            foreach ($images as $image) {
                if ($this->image_link === $image['url']) {
                    continue;
                }

                $additional[] = $image['url'];
            }

            if (!empty($additional)) {
                $this->additional_image_links = $additional;
            }
        }
    }

    /**
     * Process on part of configurable product
     */
    protected function processVariableProduct()
    {
        $parentIDs = $this->_configurableFactory
            ->create()
            ->getParentIdsByChild($this->_product->getId())
        ;

        if (!empty($parentIDs)) {
            $this->item_group_id = array_shift($parentIDs);
        }
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
        /** @var ProductType $type */
        $type = $this->_product->getTypeInstance();

        return $type instanceof ProductTypeSimple;
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        return $this->_store->getStore();
    }

    /**
     * Helper function
     * Get store currency
     * @return string
     */
    protected function _getCurrency()
    {
        return $this->_getStore()->getCurrentCurrencyCode();
    }
}
