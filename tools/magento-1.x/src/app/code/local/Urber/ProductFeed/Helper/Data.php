<?php

class Urber_ProductFeed_Helper_Data extends Mage_Core_Helper_Abstract
{
    static $params;
    static $dimentions;

    function __construct()
    {
        $store_id = Mage::app()->getStore()->getStoreId(); 
        self::$params['color']     = Mage::getStoreConfig('productfeed_config/fields/color', $store_id);
        self::$params['size']      = Mage::getStoreConfig('productfeed_config/fields/size', $store_id);
        self::$params['gender']    = Mage::getStoreConfig('productfeed_config/fields/gender', $store_id);
        self::$params['material']  = Mage::getStoreConfig('productfeed_config/fields/material', $store_id);
        self::$params['pattern']   = Mage::getStoreConfig('productfeed_config/fields/pattern', $store_id);
        self::$params['age_group'] = Mage::getStoreConfig('productfeed_config/fields/age_group', $store_id);
        self::$params['condition'] = Mage::getStoreConfig('productfeed_config/fields/condition', $store_id);

        self::$dimentions['height'] = Mage::getStoreConfig('productfeed_config/fields/dimention_height', $store_id);
        self::$dimentions['length'] = Mage::getStoreConfig('productfeed_config/fields/dimention_length', $store_id);
        self::$dimentions['width'] = Mage::getStoreConfig('productfeed_config/fields/dimention_width', $store_id);
    }

    public function generateFeed()
    {
        $validated_products = [];
        $products_collection = Mage::getModel('catalog/product')->getCollection();
        foreach ($products_collection as $product) {

            $model = Mage::getModel('catalog/product')->load($product->getId());
            
            if ($model->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {

                /**
                 * item_group_id
                 */
                $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                if(!$parent_ids) {
                    $parent_ids = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
                }
                if(isset($parent_ids[0])){
                    $entities['item_group_id'] = Mage::getModel('catalog/product')->load($parent_ids[0])->getSku();
                }

                /**
                 * color, size, gender, material, pattern, age_group, condition
                 */
                foreach (self::$params as $index => $param) {
                    if ($param) {
                        $attributeValue = $model->getResource()->getAttribute($param)->getFrontend()->getValue($model);
                        $entities[$index] = $attributeValue;
                    }
                }

                $entities['dimensions'] = [
                    'height'    => [
                        'value' => (int) self::$dimentions['height'],
                        'unit'  => ''
                    ],
                    'length'    => [
                        'value' => (int) self::$dimentions['length'],
                        'unit'  => ''
                    ],
                    'width'    => [
                        'value' => (int) self::$dimentions['width'],
                        'unit'  => ''
                    ],
                    'weight' => [
                        'value' => (float) $model->getWeigth(),
                        'unit'  => ''
                    ],
                ];

                array_push($validated_products, $entities);
            }
            return json_encode($validated_products, JSON_PRETTY_PRINT);
        }
        return json_encode($validated_products, JSON_PRETTY_PRINT);
    }
	/**
     * Feed generation
     * @return string
     */
    public function generateFeed_old()
    {
        /**
         * List of validated products
         * @var array
         */
        $validated_products = [];

        $products_collection = Mage::getModel('catalog/product')->getCollection();

        foreach ($products_collection as $product) {
            $model = Mage::getModel('catalog/product')->load($product->getId());

            /**
             * Product categories
             * @var array
             */
            $categories = [];
            $category_ids = $model->getCategoryIds();
            foreach ($category_ids as $category_id) {
                $category = (object) Mage::getModel('catalog/category')->load($category_id);
                $categories[] = [
                    'id' => (int) $category->getId(),
                    'parentId' => (int) $category->getParentId(),
                    'name' => $category->getName(),
                ];
            }

            /**
             * list of image URL's
             * @var array
             */
            $additional_image_links = [];
            $gallery_images = $model->getMediaGalleryImages();
            foreach($gallery_images as $g_image) {
                $additional_image_links[] = $g_image['url'];
            }

            /**
             * Validated product
             * @var array
             */
            $validated_product = [
                'name' => $model->getName(),
                'description' => $model->getDescription(),
                'id' => (string) $model->getSku(),
                
                /**
                 * Non-standard property. Need an example
                 */
                'gtin'  => '1455582344', // ean or upc

                /**
                 * Non-standard property. Need an example
                 */
                'mpn'   => '43509',

                /**
                 * Non-standard property. Need an example
                 */
                'dimensions' => [
                    'height'    => [
                        'value' => (int) 30,
                        'unit'  => 'cm'
                    ],
                    'length'    => [
                        'value' => (int) 30,
                        'unit'  => 'cm'
                    ],
                    'width'    => [
                        'value' => (int) 30,
                        'unit'  => 'cm'
                    ],
                    'weight' => [
                        'value' => (float) $model->getWeigth(),
                        'unit'  => 'cm'
                    ],
                ],

                'categories' => $categories,
                "item_group_id"  => "11", // gropue or for varivative
                "prices" => [
                    [
                    "currency" => Mage::app()->getStore()->getCurrentCurrencyCode(),
                    "value" => (float) $model->getPrice(),
                    "type" => "regular",
                    ]
                ],
                /**
                * Non-standard property. Need an example
                * manufacturers
                */
                "brands"  => [
                    [
                        "name"  => "Rörstrand"
                    ],
                    [
                        "name"  => "Swedish"
                    ]
                ],

                /**
                * Need an example
                */
                "attributes"  => [
                    [
                        "name"  => "volume",
                        "type"  => "number",
                        "unit"  => "cl",
                        "value"  => 45
                    ],
                    [
                        "name"  => "mikrovågsugn",
                        "type"  => "string",
                        "unit"  => null,
                        "value"  => "Ja"
                    ],
                    [
                        "name"  => "ungssäker",
                        "type"  => "boolean",
                        "unit"  => null,
                        "value"  => true
                    ]
                ],
                "size"  => null,
                "sizeType"  => null,
                "color"  => "rosa",
                "gender"  => null,
                "material"  => "porslin",
                "pattern"  => null,
                "age_group"  => null,
                "condition"  => "new",

                "image_link"  => Mage::helper('catalog/image')->init($model, 'image'),
                "additional_image_links"  => $additional_image_links,
                "link"  => $model->getProductUrl(),
            ];

            $validated_products[] = $validated_product;
            $attributes = $parentProduct->getTypeInstance()->getConfigurableAttributes($parentProduct());
            return json_encode($validated_products);
        }
        return json_encode($validated_products);
    }
}