<?php

/**
 * Init global menu
 */

global $urberMenuInited;

if (!isset($urberMenuInited)) {
    $urberMenuInited = false;
}

if (!$urberMenuInited){
    add_action('admin_menu', function() {
        add_menu_page(
            'Urber Settings Page',
            'Urber',
            'manage_options',
            'urber',
            'urberMainSettings',
            'dashicons-cart',
            81
        );
    });

    $urberMenuInited = true;
}

function urberMainSettings(){
    require URBER_PRODUCT_FEED_PLUGIN_DIR . '/admin/templates/main.php';
}

add_action('admin_menu', function() {
    add_submenu_page(
        'urber',
        'Product Feed Settings',
        'Product feed',
        'manage_options',
        'product-feed',
        'urberProductFeedSettings'
    );
});

function urberProductFeedSettings()
{
    require URBER_PRODUCT_FEED_PLUGIN_DIR . '/admin/templates/productfeed.php';
}