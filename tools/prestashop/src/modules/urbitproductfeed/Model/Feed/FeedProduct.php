<?php
/**
 * 2015-2017 Urb-it
 *
 * NOTICE OF LICENSE
 *
 *
 *
 * Do not edit or add to this file if you wish to upgrade Urb-it to newer
 * versions in the future. If you wish to customize Urb-it for your
 * needs please refer to https://urb-it.com for more information.
 *
 * @author    Urb-it SA <parissupport@urb-it.com>
 * @copyright 2015-2017 Urb-it SA
 * @license  http://www.gnu.org/licenses/
 */
 
/**
 * Class FeedProduct
 */
class UrbitProductfeedFeedProduct
{
    const DEFAULT_UNIT = 'cm';

    const DEFAULT_WEIGHT_UNIT = 'kg';

    /**
     * Array with product fields
     * @var array
     */
    protected $data = array();

    /**
     * Product Object
     * @var Object
     */
    protected $product;

    /**
     * Combination Id
     * @var int
     */
    protected $combId;

    /**
     * Product combination with quantity, price, attributes information
     * @var array
     */
    protected $combination = array();

    /**
     * PrestaShop Context
     * @var object
     */
    protected $context = null;

    /**
     * FeedProduct constructor.
     * @param $product
     * @param null $combId
     * @param null $combination
     */
    public function __construct($product, $combId = null, $combination = null)
    {
        $this->product = new Product($product['id_product']);
        $this->context = Context::getContext();

        if ($combId) {
            $this->combId = $combId;
            $this->combination = $combination;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return Object|Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return Context|object
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array|null
     */
    public function getCombination()
    {
        return $this->combination;
    }

    /**
     * @return int|null
     */
    public function getCombId()
    {
        return $this->combId;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getProductAttribute($name)
    {
        if (isset($this->product->{$name})) {
            return $this->product->{$name};
        }

        return '';
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
        $property = Tools::strtolower(preg_replace("/^unset/", '', $name));
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
     * Get data for feed
     * @return bool
     */
    public function process()
    {
        $this->processId();
        $this->processName();
        $this->processDescription();
        $this->processLink();
        $this->processGtin();
        $this->processMpn();
        $this->processDimensions();
        $this->processPrices();
        $this->processBrands();
        $this->processCategories();
        $this->processImages();
        $this->processVariableProduct();
        $this->processAttributes();
        $this->processConfigurableFields();

        return true;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function _processAttribute($name)
    {
        return UrbitProductfeedFieldsFactory::processAttribute($this, $name);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function _processAttributeByKey($key)
    {
        return UrbitProductfeedFieldsFactory::processAttributeByKey($this, $key);
    }

    /**
     * @param string $name
     * @param string $key
     * @return mixed
     */
    protected function _processAttributeOrByKey($name, $key)
    {
        return $this->_processAttribute($name) ?: $this->_processAttributeByKey($key);
    }

    /**
     * Process product id
     * add to feed product id
     */
    protected function processId()
    {
        $noCombination = empty($this->combination);

        if ($id = $this->_processAttribute('URBITPRODUCTFEED_ATTRIBUTE_ID')) {
            $this->id =  (string) $id . ($noCombination ?  '' : '-' . $this->getCombId());
        } else if ($noCombination) {
            $this->id = (string)$this->getProduct()->id;
        } else {
            $combination = $this->getCombination();
            $true = isset($combination['reference']) && $combination['reference'];
            $cid = $this->getCombId();

            $this->id = ($true  ? $combination['reference'] : $this->getProduct()->id) . '-' . $cid;
        }
    }

    /**
     * Process product name
     * add to feed product name
     */
    protected function processName()
    {
        if ($name = $this->_processAttribute('URBITPRODUCTFEED_ATTRIBUTE_NAME')) {
            $this->name = $name;
        }
    }

    /**
     * Process product description
     */
    protected function processDescription()
    {
        if ($description = $this->_processAttribute('URBITPRODUCTFEED_ATTRIBUTE_DESCRIPTION')) {
            $this->description = $description;
        }
    }

    protected function processLink()
    {
        $this->link = $this->product->getLink();
    }

    /**
     * Process product gtin
     */
    protected function processGtin()
    {
        if ($gtin = $this->_processAttribute('URBITPRODUCTFEED_ATTRIBUTE_GTIN')) {
            $this->gtin = $gtin;
        }
    }

    /**
     * Process product mpn
     */
    protected function processMpn()
    {
        if ($mpn = $this->_processAttribute('URBITPRODUCTFEED_ATTRIBUTE_MPN')) {
            $this->mpn = $mpn;
        }
    }

    /**
     * Process product dimensions
     */
    protected function processDimensions()
    {
        $dimensions = array();

        if (floatval($heightValue = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_HEIGHT_VALUE'))) {
            $heightUnit = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_HEIGHT_UNIT')  ?: static::DEFAULT_UNIT;

            $dimensions['height'] = array(
                'value' => $heightValue,
                'unit'  => $heightUnit,
            );
        }

        if (floatval($lengthValue = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_LENGTH_VALUE'))) {
            $lengthUnit = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_LENGTH_UNIT')
                ?: static::DEFAULT_UNIT;

            $dimensions['length'] = array(
                'value' => $lengthValue,
                'unit'  => $lengthUnit,
            );
        }

        if (floatval($widthValue = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_WIDTH_VALUE'))) {
            $widthUnit = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_WIDTH_UNIT') ?: static::DEFAULT_UNIT;

            $dimensions['width'] = array(
                'value' => $widthValue,
                'unit'  => $widthUnit,
            );
        }

        if (floatval($weightValue = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_WEIGHT_VALUE'))) {
            $weightUnit = $this->_processAttribute('URBITPRODUCTFEED_DIMENSION_WEIGHT_UNIT') ?: static::DEFAULT_WEIGHT_UNIT;

            $dimensions['weight'] = array(
                'value' => $weightValue,
                'unit'  => $weightUnit,
            );
        }

        if (count($dimensions)) {
            $this->dimensions = $dimensions;
        }
    }

    /**
     * @param integer $productId
     * @param $taxRate
     * @param null|integer $combId
     * @param boolean $useReduction
     * @return string
     */
    protected function getPrice($productId, $taxRate, $combId = null, $useReduction = true)
    {

        $useTax = $taxRate ? false : true;

        $price = number_format(
            Product::getPriceStatic(
                $productId,
                $useTax,
                ($combId ? $combId : null),
                6,
                null,
                false,
                $useReduction
            ),
            2,
            '.',
            ''
        );

        return ($taxRate) ? $price + ($price * ($taxRate / 100)) : $price;
    }


    /**
     * Process product prices
     */
    protected function processPrices()
    {
        $prices = array();

        $regularPrice = $this->_processAttributeOrByKey('URBITPRODUCTFEED_REGULAR_PRICE_VALUE', 'calc_RegularPrice');
        $salePrice    = $this->_processAttributeOrByKey('URBITPRODUCTFEED_SALE_PRICE_VALUE', 'calc_SalePrice');

        //regular price
        $prices[] = array(
            'currency' => $this->_processAttributeOrByKey('URBITPRODUCTFEED_REGULAR_PRICE_CURRENCY', 'calc_Currency'),
            'value'    => $regularPrice,
            'vat'      => $this->_processAttributeOrByKey('URBITPRODUCTFEED_REGULAR_PRICE_VAT', 'calc_TaxRate'),
            'type'     => 'regular',
        );

        //sale price
        if ($salePrice != $regularPrice) {
            $sPrice = array(
                'currency' => $this->_processAttributeOrByKey('URBITPRODUCTFEED_SALE_PRICE_CURRENCY', 'calc_Currency'),
                'value'    => $salePrice,
                'vat'      => $this->_processAttributeOrByKey('URBITPRODUCTFEED_SALE_PRICE_VAT', 'calc_TaxRate'),
                'type'     => 'sale',
            );

            if ($salePriceDate = $this->_processAttribute('URBITPRODUCTFEED_PRICE_EFFECTIVE_DATE')) {
                $sPrice['price_effective_date'] = $salePriceDate;
            }

            $prices[] = $sPrice;
        }

        $this->prices = $prices;
    }

    /**
     * Process product categories
     */
    protected function processCategories()
    {
        $product = $this->product;
        $categories = array();

        $categoriesInfo = Product::getProductCategoriesFull($product->id);

        foreach ($categoriesInfo as $category) {
            $allCategories = Category::getCategories();
            $parentId = null;

            foreach ($allCategories as $allCategory) {
                foreach ($allCategory as $childCategory) {
                    if ($childCategory['infos']['id_category'] == $category['id_category']) {
                        $parentId = $childCategory['infos']['id_parent'];
                        break;
                    }
                }
            }

            $categories[] = array(
                'id'       => $category['id_category'],
                'name'     => $category['name'],
                'parentId' => $parentId,

            );
        }

        $this->categories = $categories;
    }

    /**
     * Process product images and additional images
     */
    protected function processImages()
    {
        $product = $this->product;

        $linkRewrite = $product->link_rewrite;

        $additional_images = array();
        $image = null;
        $coverImageId = null;

        if (!empty($this->combination)) { // combination

            $combinationImagesIds = $product->getCombinationImages($this->context->language->id);

            if (isset($combinationImagesIds[$this->combId])) {
                $combinationImagesIds = $combinationImagesIds[$this->combId];

                if (!empty($combinationImagesIds)) {
                    foreach ($combinationImagesIds as $combinationImagesId) {
                        $additional_images[] = $this->context->link->getImageLink($linkRewrite[1],
                            $combinationImagesId['id_image'],
                            (version_compare(_PS_VERSION_, "1.7", "<")) ?
                                ImageType::getFormatedName('large'):ImageType::getFormattedName('large') );
                    }
                } else { //if combination hasn't own image
                    $coverImageId = Product::getCover($product->id)['id_image'];
                    $image = $this->context->link->getImageLink($linkRewrite[1], $coverImageId,
                        (version_compare(_PS_VERSION_, "1.7", "<")) ?
                            ImageType::getFormatedName('large'): ImageType::getFormattedName('large') );
                }
            }
        } else {   //simple product
            $coverImageId = Product::getCover($product->id)['id_image'];

            $additionalImages = Image::getImages($this->context->language->id, $product->id);

            foreach ($additionalImages as $img) {
                $imageId = (new Image((int)$img['id_image']))->id;
                if ((int)$coverImageId == $imageId) {
                    continue;
                }
                $link = new Link;

                $additional_image_link = 'http://' . $link->getImageLink($linkRewrite[1], $imageId,
                        (version_compare(_PS_VERSION_, "1.7", "<")) ?
                            ImageType::getFormatedName('large') : ImageType::getFormattedName('large'));
                $additional_images[] = $additional_image_link;
            }

            if ($coverImageId) {
                $image = $this->context->link->getImageLink($linkRewrite[1], $coverImageId,
                    (version_compare(_PS_VERSION_, "1.7", "<")) ?
                        ImageType::getFormatedName('large'): ImageType::getFormatedName('large'));
            }
        }

        if ($additional_images) {
            $this->additional_image_links = $additional_images;
        }

        if ($coverImageId && $image) {
            $this->image_link = $image;
        }
    }

    /**
     * Set group id for variable product
     */
    protected function processVariableProduct()
    {
        if (!empty($this->combination) && ($this->combination['product_id'])) {
            $this->item_group_id = $this->combination['product_id'];
        }
    }

    /**
     * Process configurable fields
     */
    protected function processConfigurableFields()
    {
        $dimensions = array();

        $FieldNames = array(
            'ean',
            'mpn',
            'dimension_height',
            'dimension_length',
            'dimension_width',
            'dimension_weight',
            'color',
            'size',
            'gender',
            'material',
            'pattern',
            'age_group',
            'condition',
            'size_type',
            'brands',
        );

        foreach ($FieldNames as $FieldName) {
            if ($FieldName == 'ean') {
                if (isset($this->product->ean13) && $this->product->ean13) {
                    $this->ean = $this->product->ean13;

                    continue;
                } elseif (isset($this->product->upc) && $this->product->upc) {
                    $this->ean = $this->product->upc;

                    continue;
                } else {
                    $fieldValue = $this->getFieldValueAndNameByConfigValue($this->getConfigureValueByName($FieldName));

                    if (!empty($fieldValue) && isset($fieldValue['value'])) {
                        $this->{$FieldName} = $fieldValue['value'];
                    }

                    continue;
                }
            }

            if ($FieldName == 'mpn') {
                $fieldValue = $this->getFieldValueAndNameByConfigValue($this->getConfigureValueByName($FieldName));

                if (!empty($fieldValue) && isset($fieldValue['value'])) {
                    $this->{$FieldName} = $fieldValue['value'];
                }
                continue;
            }

            $configValue = $this->getConfigureValueByName($FieldName);

            if (strpos($FieldName, 'dimension') !== false) {
                $configInfo = $this->getFieldValueAndNameByConfigValue($configValue);

                if (isset($configInfo['value'])) {
                    $dimensionValue = $configInfo['value'];

                    if ($dimensionValue) {
                        $unit = ($FieldName == 'dimension_weight') ? $this->getConfigureValueByName('weight_unit') : $this->getConfigureValueByName('dimension_unit');

                        if ($unit) {
                            $dimensions[explode('_', $FieldName)[1]] = array(
                                'value' => (float)$dimensionValue,
                                'unit'  => ($FieldName == 'dimension_weight') ? $this->getConfigureValueByName('weight_unit') : $this->getConfigureValueByName('dimension_unit'),
                            );
                        } else {
                            $dimensions[explode('_', $FieldName)[1]] = array(
                                'value' => (float)$dimensionValue,
                            );
                        }
                    }
                }
                continue;
            }

            $fieldValue = $this->getFieldValueAndNameByConfigValue($this->getConfigureValueByName($FieldName));

            if (!empty($fieldValue)) {
                $this->{$FieldName} = $fieldValue;
            }
        }
        if (!empty($dimensions)) {
            $this->dimensions = $dimensions;
        }
    }

    /**
     * Process attributes
     */
    protected function processAttributes()
    {
        $product = $this->product;
        $attributes = array();
        $additionalAttributes = json_decode(Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW', null), true);

        if ($additionalAttributes) {
            foreach ($additionalAttributes as $attribute) {
                if (isset($attribute['name'])) {
                    $cls = UrbitProductfeedFieldsFactory::getFieldClassByFieldName($attribute['name']);

                    switch ($cls) {
                        case 'UrbitProductfeedFieldsFieldDB':
                            $value = UrbitProductfeedFieldsFieldDB::processAttribute($this, $attribute['name']);

                            if ($value) {
                                $attributes[] = array(
                                    'name'  => $attribute['name'],
                                    'type'  => (isset($attribute['type']) && $attribute['type'] != "") ? $attribute['type'] : null,
                                    'unit'  => (isset($attribute['unit']) && $attribute['unit'] != "") ? $attribute['unit'] : null,
                                    'value' => $value,
                                );
                            }
                            break;

                        case 'UrbitProductfeedFieldsFieldAttribute':
                            //get product attributes
                            $attributeCombinations = $product->getAttributeCombinations($this->context->language->id);

                            if (!empty($attributeCombinations)) {
                                foreach ($attributeCombinations as $attributeCombination) {
                                    if ((isset($attribute['name'])) && ('a_' . $attributeCombination['id_attribute_group'] == $attribute['name']) && $attributeCombination['id_product_attribute'] == $this->combId) {
                                        $attributes[] = array(
                                            'name'  => $attributeCombination['group_name'],
                                            'type'  => (isset($attribute['type']) && $attribute['type'] != "") ? $attribute['type'] : null,
                                            'unit'  => (isset($attribute['unit']) && $attribute['unit'] != "") ? $attribute['unit'] : null,
                                            'value' => $attributeCombination['attribute_name'],
                                        );
                                    }
                                }
                            }
                            break;

                        case 'UrbitProductfeedFieldsFieldFeature':
                            //get product features
                            $FrontFeatures = $product->getFrontFeatures($this->context->language->id);

                            if (!empty($FrontFeatures)) {
                                foreach ($FrontFeatures as $frontFeature) {
                                    if ('f_' . $frontFeature['id_feature'] == $attribute['name']) {
                                        $attributes[] = array(
                                            'name'  => $frontFeature['name'],
                                            'type'  => (isset($attribute['type']) && $attribute['type'] != "") ? $attribute['type'] : null,
                                            'unit'  => (isset($attribute['unit']) && $attribute['unit'] != "") ? $attribute['unit'] : null,
                                            'value' => $frontFeature['value'],
                                        );
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }

        if (!empty($attributes)) {
            $this->attributes = $attributes;
        }
    }

    /**
     * Helper function
     * Get currency code for current store
     * @return string
     */
    protected function getCurrencyCode()
    {
        return $this->context->currency->iso_code;
    }

    /**
     * Process name
     */
    protected function processNameOld()
    {
        $product = $this->product;
        $name = $product->name[$this->context->language->id];

        // combination
        if (!empty($this->combination)) {
            $attributeResume = $product->getAttributesResume($this->context->language->id);

            foreach ($attributeResume as $attributesSet) {
                if ($attributesSet['id_product_attribute'] == $this->combId) {
                    foreach ($product->getAttributeCombinationsById($attributesSet['id_product_attribute'], $this->context->language->id) as $attribute) {
                        $name .= ' ' . $attribute['attribute_name'];
                    }
                    break;
                }
            }
        }

        $this->name = $name;
    }

    /**
     * Process brands
     */
    protected function processBrands()
    {
        $product = $this->product;
        $brands = array();

        if ($product->id_manufacturer != "0") {
            $brands[] = array(
                'name' => Manufacturer::getNameById($product->id_manufacturer),
            );
        }

        if (!empty($brands)) {
            $this->brands = $brands;
        }
    }


    /**
     * Helper function
     * Get configure value by name
     * @return string
     */
    protected function getConfigureValueByName($name)
    {
        $key = 'URBITPRODUCTFEED_';
        $prefix = explode('_', $name)[0];

        $key .= $prefix != 'dimension' && $prefix != 'weight' ? 'ATTRIBUTE_' . Tools::strtoupper($name) : Tools::strtoupper($name);

        return Configuration::get($key);
    }

    /**
     * Helper function
     * Get field value by config name
     * @return array | null
     */
    protected function getFieldValueAndNameByConfigValue($configValue)
    {
        $product = $this->product;
        $type = Tools::substr($configValue, 0, 1);
        $id = Tools::substr($configValue, 2);

        switch ($type) {
            // case attribute
            case 'a':
                $attributeCombinations = $product->getAttributeCombinations($this->context->language->id);

                foreach ($attributeCombinations as $attributeCombination) {
                    if ($attributeCombination['id_product_attribute'] == $this->combId && $attributeCombination['id_attribute_group'] == $id) {
                        return $attributeCombination['attribute_name'];
                    }
                }

                break;

            // case feature
            case 'f':
                $FrontFeatures = $product->getFrontFeatures($this->context->language->id);

                foreach ($FrontFeatures as $frontFeature) {
                    if ($frontFeature['id_feature'] == $id) {
                        return array(
                            'value' => $frontFeature['value'],
                            'name'  => $frontFeature['name'],
                        );
                    }
                }

                return $FrontFeatures;
        }

        return null;
    }
}
