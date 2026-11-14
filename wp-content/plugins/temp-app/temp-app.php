<?php
/**
 * Plugin Name:       TempApp
 * Description:       Temporary helpers for industry demo sites (volume / package pricing).
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            MH
 * License:           GPL v2 or later
 * Text Domain:       temp-app
 *
 * @package TempApp
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TEMP_APP_VERSION', '0.1.0' );
define( 'TEMP_APP_FILE', __FILE__ );
define( 'TEMP_APP_PATH', plugin_dir_path( __FILE__ ) );

require_once TEMP_APP_PATH . 'includes/class-temp-app-volume-pricing.php';
require_once TEMP_APP_PATH . 'includes/class-temp-app-bundle-tags.php';
require_once TEMP_APP_PATH . 'includes/class-temp-app-cart-groups.php';
require_once TEMP_APP_PATH . 'includes/class-temp-app-admin.php';

/**
 * Bootstrap TempApp after plugins load.
 */
function temp_app_bootstrap(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	Temp_App_Volume_Pricing::init();
	Temp_App_Bundle_Tags::init();
	Temp_App_Cart_Groups::init();
	Temp_App_Admin::init();
}
add_action( 'plugins_loaded', 'temp_app_bootstrap', 20 );
