<?php

require_once URBER_PRODUCT_FEED_PLUGIN_DIR . '/admin/templates/fields.php';

function urberRegisterProdcutfeedSettings() {

    $categories = get_categories(array('taxonomy' => 'product_cat'));
    $cats_size = (count($categories) < 10) ? count($categories) : 10;

    $tags = get_terms( array('taxonomy' => 'product_tag') );
    $tags_size = (count($tags) < 10) ? count($tags) : 10;

    // parameters: $option_group, $option_name, $sanitize_callback
    register_setting('productfeed_group', URBER_PRODUCTFEED_CONFIG);

    // parameters: $id, $title, $callback, $page
    add_settings_section('productfeed_cache', 'Feed Cache', '', 'feed_cache');
    add_settings_section('productfeed_filter', 'Product Filter', '', 'product_filter');
    add_settings_section('productfeed_fields', 'Product Fields', '', 'product_fields');

    // parameters: $id, $title, $callback, $page, $section, $args
    // fields for productfeed_cache section
    add_settings_field(
        'urber_feed_cache_field',
        'Cache Duration (in hours)',
        'urberProductfeedCacheField',
        'feed_cache',
        'productfeed_cache'
    );

    // fields for product_filter section
    add_settings_field(
        'urber_product_filter_categories_field',
        'Categories',
        'urberProductfeedCategoriesField',
        'product_filter',
        'productfeed_filter',
        array(
            'categories' => $categories,
            'size' => $cats_size
        )
    );

    add_settings_field(
        'urber_product_filter_tags_field',
        'Tags',
        'urberProductfeedTagsField',
        'product_filter',
        'productfeed_filter',
        array(
            'tags' => $tags,
            'size' =>  $tags_size
        )
    );

    add_settings_field(
        'urber_product_filter_stock_field',
        'Minimal Stock',
        'urberProductfeedStockField',
        'product_filter',
        'productfeed_filter'
    );

    // fields for product_fields section
    add_settings_field(
        'urber_product_fields_dimensions_field',
        'Product Dimensions',
        'urberProductfeedDimensionsField',
        'product_fields',
        'productfeed_fields'
    );
}

add_action('admin_init', 'urberRegisterProdcutfeedSettings');
