<?php

class Urber_ProductFeed_Helper_Data extends Mage_Core_Helper_Abstract
{
	/**
     * Feed generation
     * @return string
     */
    public static function generateFeed()
    {
        $products_collection = Mage::getModel('catalog/product')->getCollection();

        /**
         * List of validated products
         * @var array
         */
        $validated_products = [];

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
            $gallery_images =$model->getMediaGalleryImages();
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
                'gtin'  => '1455582344',

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
                "item_group_id"  => "11",
                "prices" => [
                    "currency" => Mage::app()->getStore()->getCurrentCurrencyCode(),
                    "value" => (float) $model->getPrice(),
                    /**
                    * Non-standard property. Need an example
                    */
                    "price_effective_date" => "2016-02-24T13 =>00-0800/2016-02-29T15 =>30-0800",
                    "type" => "regular",
                    "vat" => 10000
                ],
                /**
                * Non-standard property. Need an example
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
                "sizeSystem"  => null,
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
        }
        return json_encode($validated_products, JSON_PRETTY_PRINT);
    }
}