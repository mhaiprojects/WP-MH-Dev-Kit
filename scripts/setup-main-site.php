<?php
/**
 * Configure the main (base) multisite site identity.
 *
 * Usage: ddev wp eval-file scripts/setup-main-site.php
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name = 'Cursor WPMS1-1';

update_option( 'blogname', $site_name );
update_option( 'blogdescription', 'MH Dev Kit multisite — main site' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::success( sprintf( 'Main site renamed to "%s".', $site_name ) );
}
