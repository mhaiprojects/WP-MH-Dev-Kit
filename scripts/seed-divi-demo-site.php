<?php
/**
 * Seed divi-demo subsite with comprehensive Divi Builder showcase pages.
 *
 * @package MH_Dev_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/DiviDemoLayouts.php';

$expected_host = 'divi-demo.cursor-wpdev2.ddev.site';
$current_host  = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
if ( $current_host !== $expected_host ) {
	WP_CLI::error(
		sprintf(
			'Refusing to seed: expected host %s, current host is %s. Use --url=https://%s/',
			$expected_host,
			$current_host,
			$expected_host
		)
	);
}

/**
 * @return int Post ID.
 */
function divi_demo_upsert_page( string $slug, string $title, string $layout, string $excerpt = '' ): int {
	$existing = get_page_by_path( $slug );

	$args = array(
		'post_type'    => 'page',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $layout,
		'post_excerpt' => $excerpt,
		'post_status'  => 'publish',
	);

	if ( $existing instanceof WP_Post ) {
		$args['ID'] = $existing->ID;
		$post_id    = wp_update_post( $args, true );
	} else {
		$post_id = wp_insert_post( $args, true );
	}

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::error( $post_id->get_error_message() );
	}

	$post_id = (int) $post_id;
	DiviDemoLayouts::apply_builder_meta( $post_id );

	return $post_id;
}

/**
 * @param array<string, int> $page_ids
 */
function divi_demo_rebuild_primary_menu( array $page_ids ): void {
	$menu_name = 'Divi Demo Primary';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

	if ( $menu_id <= 0 ) {
		WP_CLI::warning( 'Could not create primary menu.' );
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$locations['primary-menu'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( is_array( $existing_items ) ) {
		foreach ( $existing_items as $item ) {
			if ( $item instanceof WP_Post ) {
				wp_delete_post( (int) $item->ID, true );
			}
		}
	}

	$all_pages = DiviDemoLayouts::page_definitions();
	$position  = 0;

	foreach ( DiviDemoLayouts::primary_nav_tree() as $node ) {
		if ( 'link' === $node['type'] ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'    => $node['title'] ?? '',
					'menu-item-url'      => home_url( (string) ( $node['url'] ?? '/' ) ),
					'menu-item-type'     => 'custom',
					'menu-item-status'   => 'publish',
					'menu-item-position' => ++$position,
				)
			);
			continue;
		}

		if ( 'page' === $node['type'] ) {
			$slug = $node['slug'];
			if ( ! isset( $page_ids[ $slug ], $all_pages[ $slug ] ) ) {
				continue;
			}
			$title = $node['title'] ?? $all_pages[ $slug ]['nav_title'];
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $title,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => ++$position,
				)
			);
			continue;
		}

		if ( 'group' !== $node['type'] || empty( $node['children'] ) ) {
			continue;
		}

		$parent_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'    => $node['title'],
				'menu-item-url'      => '#',
				'menu-item-type'     => 'custom',
				'menu-item-status'   => 'publish',
				'menu-item-position' => ++$position,
			)
		);

		if ( is_wp_error( $parent_id ) ) {
			continue;
		}

		foreach ( $node['children'] as $child ) {
			$slug = $child['slug'];
			if ( ! isset( $page_ids[ $slug ], $all_pages[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $all_pages[ $slug ]['nav_title'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => (int) $parent_id,
					'menu-item-position'  => ++$position,
				)
			);
		}
	}

	WP_CLI::log( sprintf( 'Primary menu rebuilt: %s (%d top-level items)', $menu_name, count( DiviDemoLayouts::primary_nav_tree() ) ) );
}

/**
 * @param array<string, int> $page_ids
 */
function divi_demo_rebuild_footer_menu( array $page_ids ): void {
	$menu_name = 'Divi Demo Footer';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

	if ( $menu_id <= 0 ) {
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$locations['footer-menu'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( is_array( $existing_items ) ) {
		foreach ( $existing_items as $item ) {
			if ( $item instanceof WP_Post ) {
				wp_delete_post( (int) $item->ID, true );
			}
		}
	}

	$links = array(
		array( 'title' => 'Home', 'url' => home_url( '/' ) ),
		array( 'title' => 'Shop', 'url' => home_url( '/shop/' ) ),
		array( 'title' => 'Structure & Grids', 'url' => get_permalink( $page_ids['structure-and-grids'] ?? 0 ) ),
		array( 'title' => 'WooCommerce Demo', 'url' => get_permalink( $page_ids['woocommerce-modules'] ?? 0 ) ),
		array( 'title' => 'Advanced Settings', 'url' => get_permalink( $page_ids['advanced-builder-settings'] ?? 0 ) ),
	);

	$position = 0;
	foreach ( $links as $link ) {
		if ( empty( $link['url'] ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'    => $link['title'],
				'menu-item-url'      => $link['url'],
				'menu-item-type'     => 'custom',
				'menu-item-status'   => 'publish',
				'menu-item-position' => ++$position,
			)
		);
	}

	if ( function_exists( 'et_update_option' ) ) {
		et_update_option( 'footer_columns', '4' );
		et_update_option(
			'custom_footer_credits',
			'&copy; ' . gmdate( 'Y' ) . ' Divi Builder Demo — grocery WooCommerce showcase.'
		);
	}

	WP_CLI::log( 'Footer menu rebuilt: Divi Demo Footer' );
}

function divi_demo_remove_sample_page(): void {
	$sample = get_page_by_path( 'sample-page' );
	if ( $sample instanceof WP_Post ) {
		wp_trash_post( $sample->ID );
		WP_CLI::log( 'Trashed default Sample Page.' );
	}
}

DiviDemoLayouts::apply_theme_options();

$page_ids = array();
foreach ( DiviDemoLayouts::all_pages() as $slug => $meta ) {
	$page_ids[ $slug ] = divi_demo_upsert_page(
		$slug,
		$meta['title'],
		$meta['layout'],
		$meta['excerpt']
	);
	WP_CLI::log( sprintf( 'Page: %s (ID %d)', $slug, $page_ids[ $slug ] ) );
}

$home_id = $page_ids['divi-demo'] ?? 0;
if ( $home_id > 0 ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
}

divi_demo_rebuild_primary_menu( $page_ids );
divi_demo_rebuild_footer_menu( $page_ids );
divi_demo_remove_sample_page();

WP_CLI::success(
	sprintf(
		'Seeded %d Divi demo pages on %s',
		count( $page_ids ),
		home_url( '/' )
	)
);
