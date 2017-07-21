<?php

if (!defined( 'URBER_PRODUCT_FEED_PLUGIN_DIR' )) {
    exit;
}

class UPF_Query
{
    /**
     * @var UPF_Core
     */
    protected $core;

    /**
     * UPF_Query constructor.
     * @param UPF_Core $core
     */
    public function __construct(UPF_Core $core)
    {
        $this->core = $core;
    }

    /**
     * Get all products query
     *
     * @param array $args
     * @return WP_Query
     */
    function productsQuery($args = array())
    {
        // Set base query arguments
        $queryArgs = array(
            'fields'      => 'ids',
            'post_type'   => 'product',
            'post_status' => 'publish',
            'meta_query'  => array(),
        );

        //Filter products by type
        if (!empty($args['type'])){
            $types = explode(',', $args['type']);
            $queryArgs['tax_query'] = array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => $types,
                ),
            );

            unset($args['type']);
        }

        // Filter products by category
        if (!empty($args['category'])){
            $queryArgs['product_cat'] = $args['category'];
        }

        return new WP_Query($queryArgs);
    }

}