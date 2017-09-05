<?php

/**
 * Class FeedProduct
 */
class Urbit_Productfeed_FeedProduct
{
    /**
     * Array with product fields
     * @var array
     */
    protected $data = [];

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
    protected $combination = [];

    /**
     * PrestaShop Context
     * @var object
     */
    protected $context = null;

    /**
     * Inventory constructor.
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
        $property = strtolower(preg_replace("/^unset/", $name));
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
        $product = $this->product;

        $this->processId();
        $this->description = $product->description[$this->context->language->id];
        $this->link = $product->getLink();
        $this->processName();
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
     * Process product id
     * add to feed product id
     */
    protected function processId()
    {

        $this->id = empty($this->combination) ?
            ($this->product->reference ? $this->product->reference : (string) $this->product->id):
            (isset($this->combination['reference']) && $this->combination['reference'] ? $this->combination['reference'] : $this->product->id . '-' . $this->combId)
        ;
    }

    /**
     * @param integer $productId
     * @param null|integer $combId
     * @param boolean $useReduction
     * @return string
     */
    protected function getPrice($productId, $combId = null, $useReduction = true)
    {
        return number_format(
            Product::getPriceStatic($productId, true, ($combId ? $combId : null), 6, NULL, false, $useReduction),
            2,'.',''
        );
    }

    /**
     * Process product prices
     */
    protected function processPrices()
    {
        $product = $this->product;

        $regularPrice = $this->getPrice($product->id, $this->combId, false);
        $salePrice    = $this->getPrice($product->id, $this->combId, true);

        $prices = [
            [
                "currency" => $this->currencyCode,
                "value"    => $regularPrice,
                "type"     => "regular",
            ],
        ];

        if ($regularPrice !== $salePrice) {
            $prices[] = [
                "currency" => $this->currencyCode,
                "value"    => $salePrice,
                "type"     => "sale",
            ];
        }

        $this->prices = $prices;
    }

    /*
     * Process product categories
     * */
    protected function processCategories()
    {
        $product = $this->product;
        $categories = [];

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

            $categories[] = [
                'id'       => $category['id_category'],
                'name'     => $category['name'],
                'parentId' => $parentId,

            ];
        }

        $this->categories = $categories;
    }

    /*
     * Process product images and additional images
     * */
    protected function processImages()
    {
        $product = $this->product;

        $linkRewrite = $product->link_rewrite;

        $additional_images = [];
        $image = null;
        $coverImageId = null;

        if (!empty($this->combination)) { // combination

            $combinationImagesIds = $product->getCombinationImages($this->context->language->id);

            if (isset($combinationImagesIds[$this->combId])) {
                $combinationImagesIds = $combinationImagesIds[$this->combId];

                if (!empty($combinationImagesIds)) {
                    foreach ($combinationImagesIds as $combinationImagesId) {
                        $additional_images[] = $this->context->link->getImageLink($linkRewrite[1], $combinationImagesId['id_image'], 'large_default');
                    }
                } else { //if combination hasn't own image
                    $coverImageId = Product::getCover($product->id)['id_image'];
                    $image = $this->context->link->getImageLink($linkRewrite[1], $coverImageId, 'large_default');
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

                $additional_image_link = 'http://' . $link->getImageLink($linkRewrite[1], $imageId, 'large_default');
                $additional_images[] = $additional_image_link;
            }

            if ($coverImageId) {
                $image = $this->context->link->getImageLink($linkRewrite[1], $coverImageId, 'large_default');
            }
        }

        if ($additional_images){
            $this->additional_image_links = $additional_images;
        }

        if ($coverImageId && $image) {
            $this->image_link = $image;
        }
    }

    protected function processVariableProduct()
    {
        if (!empty($this->combination) && ($this->combination['product_id'])) {
            $this->item_group_id = $this->combination['product_id'];
        }
    }

    /**
     *  Process configurable fields
     */
    protected function processConfigurableFields()
    {
        $dimensions = [];

        $FieldNames = [
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
            'brands'
        ];

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
                $configInfo =  $this->getFieldValueAndNameByConfigValue($configValue);

                if (isset($configInfo['value'])) {
                    $dimensionValue = $configInfo['value'];

                    if ($dimensionValue) {
                        $unit = ($FieldName == 'dimension_weight') ? $this->getConfigureValueByName('weight_unit') : $this->getConfigureValueByName('dimension_unit');

                        if ($unit) {
                            $dimensions[explode('_', $FieldName)[1]] = [
                                'value' => (float)$dimensionValue,
                                'unit'  => ($FieldName == 'dimension_weight') ? $this->getConfigureValueByName('weight_unit') : $this->getConfigureValueByName('dimension_unit'),
                            ];
                        } else {
                            $dimensions[explode('_', $FieldName)[1]] = [
                                'value' => (float)$dimensionValue,
                            ];
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
        $attributes = [];

        $additionalAttributes = Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE');

        //check product features
        $FrontFeatures = $product->getFrontFeatures($this->context->language->id);

        if (!empty($FrontFeatures)) {
            foreach ($FrontFeatures as $frontFeature) {
                if (in_array('f' . $frontFeature['id_feature'], explode(',', $additionalAttributes))) {
                    $attributes[] = [
                        'name'  => $frontFeature['name'],
                        'type'  => 'string',
                        'value' => $frontFeature['value'],
                    ];
                }
            }
        }

        //check product attributes
        $attributeCombinations = $product->getAttributeCombinations($this->context->language->id);

        if (!empty($attributeCombinations)) {
            foreach ($attributeCombinations as $attributeCombination) {
                if (in_array('a' . $attributeCombination['id_attribute_group'], explode(',', $additionalAttributes)) && $attributeCombination['id_product_attribute'] == $this->combId) {
                    $attributes[] = [
                        'name'  => $attributeCombination['group_name'],
                        'type'  => 'string',
                        'value' => $attributeCombination['attribute_name'],
                    ];
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
    protected function processName()
    {
        $product = $this->product;
        $name = $product->name[$this->context->language->id];

        if (!empty($this->combination))  // combination
        {
            $attributeResume = $product->getAttributesResume($this->context->language->id);
            foreach ($attributeResume as $attributesSet) {
                if ($attributesSet['id_product_attribute'] == $this->combId) {
                    foreach ($product->getAttributeCombinationsById($attributesSet['id_product_attribute'], $this->context->language->id) as $attribute) {
                        $name = $name . ' ' . $attribute['attribute_name'];
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
        $brands = [];

        if ($product->id_manufacturer != "0") {
            $brands[] =
                [
                    'name' => Manufacturer::getNameById($product->id_manufacturer),
                ];
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
        $key = 'URBIT_PRODUCTFEED_';
        $prefix = explode('_', $name)[0];

        if ($prefix != 'dimension' && $prefix != 'weight') {
            $key = $key . 'ATTRIBUTE_' . strtoupper($name);
        } else {
            $key = $key . strtoupper($name);
        }

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
        $type = substr($configValue, 0, 1);
        $id = substr($configValue, 1);

        switch ($type) {
            case 'a':   // case attribute
                $attributeCombinations = $product->getAttributeCombinations($this->context->language->id);
                //$this->DEBUGATTRIB = $attributeCombinations;
                foreach ($attributeCombinations as $attributeCombination) {
                    if ($attributeCombination['id_product_attribute'] == $this->combId && $attributeCombination['id_attribute_group'] == $id) {
                        return $attributeCombination['attribute_name'];
                    }
                }
                //return $attributeCombinations;
                break;
            case 'f':   // case feature

                $FrontFeatures = $product->getFrontFeatures($this->context->language->id);
                //$this->FRONTDEBUG = $FrontFeatures;
                foreach ($FrontFeatures as $frontFeature) {
                    if ($frontFeature['id_feature'] == $id) {
                        return [
                            'value' => $frontFeature['value'],
                            'name'  => $frontFeature['name'],
                        ];
                    }
                }
                return $FrontFeatures;
                break;
        }

        return null; // TODO: should throw exception ?

    }


}
