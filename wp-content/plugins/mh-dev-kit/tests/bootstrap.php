<?php
/**
 * PHPUnit bootstrap — loads wp-phpunit and the MH Dev Kit plugin.
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

$plugin_root = dirname( __DIR__ );
$tests_dir   = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';

if ( ! file_exists( $tests_dir . '/includes/functions.php' ) ) {
	echo "WordPress test library not found at {$tests_dir}.\n";
	echo "Run: ./scripts/test-mh-dev-kit.sh (installs the test library automatically)\n";
	exit( 1 );
}

define(
	'WP_TESTS_PHPUNIT_POLYFILLS_PATH',
	$plugin_root . '/vendor/yoast/phpunit-polyfills'
);

if ( file_exists( $plugin_root . '/tests/wp-tests-config.php' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', $plugin_root . '/tests/wp-tests-config.php' );
}

require_once $tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $plugin_root ): void {
		require $plugin_root . '/mh-dev-kit.php';
		MH\DevKit\Core\Plugin::boot();
	}
);

require $tests_dir . '/includes/bootstrap.php';
