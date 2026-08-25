<?php
/**
 * Remove Divi demo pages and menu from the main network site (blog 1).
 * Demo content belongs on https://divi-demo.cursor-wpdev2.ddev.site only.
 *
 * @package MH_Dev_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$main_host = 'cursor-wpdev2.ddev.site';
$current   = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );

if ( $current !== $main_host ) {
	WP_CLI::error(
		sprintf(
			'Refusing cleanup: run on main site (%s), not %s.',
			$main_host,
			$current
		)
	);
}

$demo_slugs = array(
	'divi-demo',
	'structure-and-grids',
	'content-modules',
	'interactive-modules',
	'forms-and-utility',
	'blog-and-post-modules',
	'fullwidth-modules',
	'advanced-builder-settings',
);

$trashed = 0;
foreach ( $demo_slugs as $slug ) {
	$page = get_page_by_path( $slug );
	if ( ! $page instanceof WP_Post ) {
		continue;
	}
	wp_trash_post( $page->ID );
	WP_CLI::log( sprintf( 'Trashed page: %s (ID %d)', $slug, $page->ID ) );
	++$trashed;
}

$front_id = (int) get_option( 'page_on_front', 0 );
if ( $front_id > 0 ) {
	$front = get_post( $front_id );
	if ( $front instanceof WP_Post && in_array( $front->post_name, $demo_slugs, true ) ) {
		update_option( 'show_on_front', 'posts' );
		delete_option( 'page_on_front' );
		WP_CLI::log( 'Reset front page to latest posts.' );
	}
}

$menu = wp_get_nav_menu_object( 'Divi Demo Primary' );
if ( $menu ) {
	$menu_id = (int) $menu->term_id;
	wp_delete_nav_menu( $menu_id );
	WP_CLI::log( 'Deleted menu: Divi Demo Primary.' );

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( is_array( $locations ) && isset( $locations['primary-menu'] ) && (int) $locations['primary-menu'] === $menu_id ) {
		unset( $locations['primary-menu'] );
		set_theme_mod( 'nav_menu_locations', $locations );
		WP_CLI::log( 'Cleared primary-menu location on main site.' );
	}
}

WP_CLI::success( sprintf( 'Main site cleanup complete (%d demo pages trashed).', $trashed ) );
