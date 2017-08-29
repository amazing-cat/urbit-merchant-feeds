<?php

/**
 * Class FeedProduct
 */
class FeedProduct
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

        if (!empty($this->combination)) {
            $this->id = (string)$this->combId;
        } else {
            $this->id = (string)$product->id;
        }

        $this->name         = $product->name[$this->context->language->id]; //TODO:  сделать для вариативных продуктов имя = имя продукта + его атрибуты (напр. T-Shirt Black L))
        $this->description  = $product->description[$this->context->language->id];
        $this->link         = $product->getLink();



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
        //TODO: correct currency code
        $product = $this->product;

        if (!$this->combId) {
            // Regular price
            $prices = [
                [
                    "currency" => $this->currencyCode,
                    "value"    => number_format($product->price, 2, '.', ''),
                    "type"     => "regular",
                ],
            ];
        } else {
            //Special Price
            $specialPrice = SpecificPrice:: getByProductId($product->id, $this->combId);

            if ((float)$specialPrice[0]['reduction'] > 0.00) {
                $prices = [
                    [
                        "currency" => $this->currencyCode,
                        "value"    => $this->combination['price'] + $specialPrice[0]['reduction'],
                        "type"     => "regular",
                    ],
                    [
                        "currency" => $this->currencyCode,
                        "value"    => $this->combination['price'],
                        "type"     => "sale",
                    ],
                ];
            } else {
                // Regular price
                $prices = [
                    [
                        "currency" => $this->currencyCode,
                        "value"    => $this->combination['price'],
                        "type"     => "regular",
                    ],
                ];
            }
        }

        $this->prices = $prices;
    }

    protected function processCategories()
    {
        $product = $this->product;
        $categories = [];

        $categoriesInfo = Product::getProductCategoriesFull($product->id);


        foreach ($categoriesInfo as $category)
        {
            $allCategories = Category::getCategories();
            $parentId = null;

            foreach ($allCategories as $allCategory)
            {
                foreach ($allCategory as $childCategory)
                if ($childCategory['infos']['id_category'] == $category['id_category'])
                {
                    $parentId = $childCategory['infos']['id_parent'];
                    break;
                }
            }

            $categories[] =[
                'id' => $category['id_category'],
                'name' => $category['name'],
                'parentId' => $parentId,

            ];
        }

        $this->categories = $categories;
    }


    protected function processImages()
    {
        $product = $this->product;
        $cover = Product::getCover($product->id);

        $linkRewrite = $product->link_rewrite;
        $image = Context::getContext()->link->getImageLink($linkRewrite, $cover ? $cover['id_image'] : '', 'home_default');

        $additional_images = [];
        foreach (Image::getImages($this->context->language->id, $product->id) as $img)
        {
            $imageId = (new Image((int)$img['id_image']))->id;
            $link = new Link;
            $additional_images[] = 'http://' . $link->getImageLink($product->link_rewrite, $imageId, 'home_default');   //TODO: 'http://'. возможно стоит переделать
        }


        if(count($additional_images) > 0)
            $this->additional_image_links = $additional_images;
        else
            $this->additional_image_links = [];

        $this->image_link = $image;
    }


    protected function processVariableProduct()
    {

    }

    protected function processConfigurableFields()
    {

    }

    protected function processAttributes()
    {
        $product = $this->product;
        $attributes = [];

        $combinations = $product->getAttributeCombinations($this->context->language->id);

        $moduleConfigureAttributes =
            [
                'URBIT_PRODUCTFEED_ATTRIBUTE_COLOR'                 => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_COLOR'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE'                  => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_SIZE'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_GENDER'                => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_GENDER'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL'              => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_MATERIAL'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN'               => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_PATTERN'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP'             => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_AGE_GROUP'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION'             => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_CONDITION'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE'             => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_SIZE_TYPE'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS'                => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_BRANDS'),
                'URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE'  => Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE'),
            ];
//
//        //foreach ($product->getAttributesGroups($this->context->language->id) as $k => $attr)
//        foreach ($product->getatt($this->context->language->id) as $k => $attr)
//        {
//            $code = null;
//            $type = null;
//            $value = null;
//
//
//            $attributes[] = array(
//                'name'  => $code,
//                'type'  => $type,
//                //'unit'  => null,
//                'value' => $value,
//            );
//        }
//
//
//
////        foreach ($attributes as $attribute)
////        {
////
////
////            $result[]  =
////            [
////                'name' => $attribute['group_name'], // "group_name":"Цвет",
////                'type' => 'string', // null,
////                'unit' => null,
////                'value' => $attribute['attribute_name'], // "attribute_name":"Оранжевый",
////            ];
////        }
//
//        $allattrib = Attribute::getAttributes($this->context->language->id, $not_null = false);
//
//        $this->config = $moduleConfigureAttributes;
//        $this->combinations = $combinations;
        $this->attributes = $attributes;
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

}
