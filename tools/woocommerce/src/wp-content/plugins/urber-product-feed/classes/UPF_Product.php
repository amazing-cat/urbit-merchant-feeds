<?php

class UPF_Product
{
    /**
     * @var UPF_Core
     */
    protected $core;

    /**
     * UPF_Product constructor.
     * @param UPF_Core $core
     */
    public function __construct(UPF_Core $core)
    {
        $this->core = $core;
    }

    /**
     * Get product additional images
     *
     * @param WC_Product $product
     * @return array
     */
    public function getProductImages($product)
    {
        $images = array();

        $wpImageIds = $product->get_gallery_image_ids();

        foreach ($wpImageIds as $imageId){
            $images[] = wp_get_attachment_image_src($imageId, 'full')[0];
        }

        return $images;
    }

    /**
     * Get product attributes
     *
     * @param WC_Product $product
     * @return array
     */
    public function getProductAttributes($product)
    {
        $attributes = array();

        $wcAttributes = $product->get_attributes();

        /** @var WC_Product_Attribute $wcAttribute */
        foreach ($wcAttributes as $wcAttribute){
            $type = gettype($wcAttribute->get_options()[0]);
            $resultType = $type;

            switch ($type){
                case 'integer': $resultType = 'number'; break;
                case 'double': $resultType = 'float'; break;
            }

            $attributes[] = array(
                'name' => $wcAttribute->get_name(),
                'type' => $resultType,
                'value' => $wcAttribute->get_options()[0]
            );
        }

        return $attributes;
    }

    /**
     * Get product prices
     *
     * @param WC_Product $product
     * @param string $currency
     * @return array
     */
    public function getProductPrices($product, $currency)
    {
        $prices = array(
            array(
                'currency' => $currency,
                'value' => $product->get_regular_price(),
                'type' => 'regular',
            )
        );

        if(! empty($product->get_sale_price())){
            $prices[] = array(
                array(
                    'currency' => $currency,
                    'value' => $product->get_sale_price(),
                    'type' => 'sale',
                ),
            );
        }

        return $prices;
    }

    /**
     * Get product categories
     *
     * @param WC_Product $product
     * @return array
     */
    public function getProductCategories($product)
    {
        $categories = array();

        $categoryIds = $product->get_category_ids();

        foreach ($categoryIds as $categoryId){
            if($term = get_term_by('id', $categoryId, 'product_cat')){
                $categories[]['name'] = $term->name;
            }
        }

        return $categories;
    }

}