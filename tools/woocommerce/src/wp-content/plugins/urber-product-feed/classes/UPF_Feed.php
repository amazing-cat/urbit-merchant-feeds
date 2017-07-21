<?php

if (!defined( 'URBER_PRODUCT_FEED_PLUGIN_DIR' )) {
    exit;
}

/**
 * Class UPF_Feed
 */
class UPF_Feed
{
    /**
     * @var UPF_Core
     */
    protected $core;

    /**
     * UPF_Feed constructor.
     * @param UPF_Core $core
     */
    public function __construct(UPF_Core $core)
    {
        $this->core = $core;
    }

    public function generate()
    {
        /** @var UPF_Cache $cache */
        $cache = $this->core->getCache();

        $feedResult = '';
        $cacheFile  = $cache->getLastCacheFile();

        if (!$cache->checkFeedCacheExpired($cacheFile)) {
            $feedResult = $this->process();
            $cache->saveFeedToCache($feedResult);
        }

        if (!$feedResult) {
            $cache->getFeedFromCache($cacheFile);
        }

        //change header content type
        header('Content-Type: application/json');

        //print feed data
        echo $feedResult;
    }

    /**
     * Process feed generation
     * @return string
     */
    protected function process()
    {
        $this->core->getCache()->flushAllCacheFiles();

        /** @var UPF_Product $UProduct */
        $UProduct = $this->core->getProduct();

        $feedResult = array();

        /** @var WP_Query $query */
        $query = $this->core->getQuery()->productsQuery();

        $dimensionUnit = get_option('woocommerce_dimension_unit');
        $weightUnit    = get_option('woocommerce_weight_unit');
        $currency      = get_option('woocommerce_currency');

        foreach ($query->posts as $productId){
            $product = new WC_Product($productId);

            //setup product dimensions
            $dimensions = [
                'height' => [
                    'value' => $product->get_height(),
                    'unit' => $dimensionUnit
                ],
                'length' => [
                    'value' => $product->get_length(),
                    'unit' => $dimensionUnit
                ],
                'width' => [
                    'value' => $product->get_width(),
                    'unit' => $dimensionUnit
                ],
                'weight' => [
                    'value' => $product->get_weight(),
                    'unit' => $weightUnit
                ],
            ];

            $feedResult[] = array(
                'name'                   => $product->get_title(),
                'description'            => $product->get_description(),
                'id'                     => $product->get_sku(),
                'dimensions'             => $dimensions,
                'categories'             => $UProduct->getProductCategories($product),
                'prices'                 => $UProduct->getProductPrices($product, $currency),
                'attributes'             => $UProduct->getProductAttributes($product),
                "image_link"             => wp_get_attachment_image_src($product->get_image_id(), 'full')[0],
                "additional_image_links" => $UProduct->getProductImages($product),
                "link"                   => get_permalink($productId),
            );
        }

        return json_encode($feedResult, JSON_PRETTY_PRINT);
    }
}