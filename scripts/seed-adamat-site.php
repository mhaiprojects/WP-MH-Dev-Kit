<?php
/**
 * Seed adamat subsite with Divi Builder layouts and primary navigation.
 *
 * @package MH_Dev_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/AdamatDiviLayouts.php';
require_once __DIR__ . '/lib/AdamatMediaAssets.php';

/**
 * @return int Post ID.
 */
function adamat_upsert_divi_page(
	string $slug,
	string $title,
	string $layout,
	string $excerpt = '',
	string $featured_media_key = ''
): int {
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

	AdamatDiviLayouts::apply_builder_meta( $post_id );

	if ( '' !== $featured_media_key ) {
		AdamatDiviLayouts::set_page_featured_image( $post_id, $featured_media_key );
	}

	return $post_id;
}

AdamatMediaAssets::seed();
AdamatDiviLayouts::apply_theme_options();
AdamatDiviLayouts::seed_global_layouts();

$home_id     = adamat_upsert_divi_page( 'home', 'Home', AdamatDiviLayouts::home_layout(), 'Australian agency — digital and AI solutions for growing businesses.', 'hero' );
$about_id    = adamat_upsert_divi_page( 'about-us', 'About Us', AdamatDiviLayouts::about_layout(), 'About adamat — Australian digital studio serving a global market.', 'about-team' );
$services_id = adamat_upsert_divi_page( 'services', 'Services', AdamatDiviLayouts::services_layout(), 'Web design, eCommerce, AI, product data, hosting, and graphic design.', 'hero' );
$projects_id = adamat_upsert_divi_page( 'projects-showcase', 'Projects Showcase', AdamatDiviLayouts::projects_layout(), 'Selected client and internal projects.', 'project-corporate' );
$contact_id  = adamat_upsert_divi_page( 'contact', 'Contact', AdamatDiviLayouts::contact_layout(), 'Get in touch with adamat.', 'contact' );

// Retire standalone service pages from public view (content lives on Services page).
$retire_slugs = array(
	'web-design',
	'ecommerce',
	'ai-services',
	'product-data-management',
	'domains-hosting',
	'graphic-design',
);

foreach ( $retire_slugs as $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post ) {
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_status' => 'draft',
			)
		);
	}
}

$sample = get_page_by_path( 'sample-page' );
if ( $sample instanceof WP_Post ) {
	wp_update_post(
		array(
			'ID'          => $sample->ID,
			'post_status' => 'draft',
		)
	);
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', 0 );
update_option( 'blogname', 'adamat' );
update_option( 'blogdescription', 'Digital & AI agency — web, eCommerce, product data, hosting & brand design · Australia & worldwide' );

AdamatDiviLayouts::apply_footer_widgets();

$menu_name = 'Adamat Primary';
$menu      = wp_get_nav_menu_object( $menu_name );
$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

if ( $menu_id > 0 ) {
	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( is_array( $existing_items ) ) {
		foreach ( $existing_items as $item ) {
			wp_delete_post( (int) $item->ID, true );
		}
	}

	$menu_pages = array(
		array( 'title' => 'Home', 'object_id' => $home_id ),
		array( 'title' => 'About Us', 'object_id' => $about_id ),
		array( 'title' => 'Services', 'object_id' => $services_id ),
		array( 'title' => 'Projects Showcase', 'object_id' => $projects_id ),
		array( 'title' => 'Contact', 'object_id' => $contact_id ),
	);

	foreach ( $menu_pages as $item ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $item['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $item['object_id'],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	$locations                 = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary-menu'] = $menu_id;
	$locations['footer-menu']  = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

WP_CLI::success(
	sprintf(
		'Adamat Divi site seeded. Menu: Home, About Us, Services, Projects Showcase, Contact. Footer widgets updated. Front page ID %d.',
		$home_id
	)
);
