<?php
/**
 * Plugin Name: Urbit Inventory Feed
 * Plugin URI: https://urb-it.com/
 * Description: Urbit Inventory Feed plugin for Woocommerce.
 * Version: 1.0.0
 * Author: Urb-IT
 * Author URI: https://urb-it.com/
 */

/**
 * Init constaints
 */
require_once dirname(__FILE__) . '/constants.php';

/*
 * Init classes
 */
require_once URBIT_INVENTORY_FEED_CLASS_DIR . '/_init.php';

/*
 * Run plugin
 */
$UIF = new UIF_Core(__FILE__);

/*
 * Run admin
 */
if (is_admin()) {
    $UIFAdmin = new UIF_Admin_Core($UIF);
}
