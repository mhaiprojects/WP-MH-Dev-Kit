<?php
/**
 * Plugin Name:       MH Dev Kit
 * Description:       Monolithic WordPress development plugin with modular features.
 * Version:           0.5.0
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

define( 'MH_DEV_KIT_VERSION', '0.5.0' );
define( 'MH_DEV_KIT_FILE', __FILE__ );
define( 'MH_DEV_KIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MH_DEV_KIT_URL', plugin_dir_url( __FILE__ ) );

/**
 * Admin notice when Composer dependencies are missing.
 */
function mhdk_missing_vendor_notice(): void {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__(
			'MH Dev Kit requires Composer dependencies. Run: ddev exec "cd wp-content/plugins/mh-dev-kit && composer install"',
			'mh-dev-kit'
		)
	);
}

$autoload = MH_DEV_KIT_PATH . 'vendor/autoload.php';

if ( ! is_readable( $autoload ) ) {
	add_action( 'admin_notices', 'mhdk_missing_vendor_notice' );
	return;
}

require_once $autoload;

add_action( 'plugins_loaded', static function (): void {
	MH\DevKit\Core\Plugin::boot();
} );

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
