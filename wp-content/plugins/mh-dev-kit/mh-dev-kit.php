<?php
/**
 * Plugin Name:       MH Dev Kit
 * Description:       Development utilities and scaffolding for MH WordPress projects.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            MH
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mh-dev-kit
 *
 * @package MH_Dev_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MH_DEV_KIT_VERSION', '0.1.0' );
define( 'MH_DEV_KIT_FILE', __FILE__ );
define( 'MH_DEV_KIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MH_DEV_KIT_URL', plugin_dir_url( __FILE__ ) );

require_once MH_DEV_KIT_PATH . 'includes/class-mh-dev-kit.php';

/**
 * Bootstrap the plugin.
 */
function mhdk_init(): void {
	MH_Dev_Kit::instance();
}
add_action( 'plugins_loaded', 'mhdk_init' );

/**
 * Activation hook.
 */
function mhdk_activate(): void {
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mhdk_activate' );

/**
 * Deactivation hook.
 */
function mhdk_deactivate(): void {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mhdk_deactivate' );
