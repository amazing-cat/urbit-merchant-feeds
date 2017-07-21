<?php
/**
 * Plugin Name: Urbit Product Feed
 * Plugin URI: https://urb-it.com/
 * Description: Urbit Product Feed plugin for Woocommerce.
 * Version: 1.0.0
 * Author: Urb-IT
 * Author URI: https://urb-it.com/
 */

/**
 * Init constaints
 */
require_once dirname(__FILE__) . '/constants.php';

require_once URBER_PRODUCT_FEED_CLASS_DIR . '/_init.php';

/**
 * Admin settings
 */
if (is_admin()) {
    require_once URBER_PRODUCT_FEED_PLUGIN_DIR . '/admin/init.php';
}

$UPF = new UPF_Core(__FILE__);
