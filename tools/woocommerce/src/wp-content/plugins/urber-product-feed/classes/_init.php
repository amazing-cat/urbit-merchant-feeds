<?php
/**
 * Load main classes
 */

if (!defined( 'URBER_PRODUCT_FEED_PLUGIN_DIR' )) {
    exit;
}

require URBER_PRODUCT_FEED_CLASS_DIR . "/UPF_Core.php";
require URBER_PRODUCT_FEED_CLASS_DIR . "/UPF_Template.php";
require URBER_PRODUCT_FEED_CLASS_DIR . "/UPF_Feed.php";
require URBER_PRODUCT_FEED_CLASS_DIR . "/UPF_Cache.php";
