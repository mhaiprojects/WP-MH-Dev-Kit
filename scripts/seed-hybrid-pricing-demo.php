<?php
/**
 * Seed hybrid B2B/B2C pricing demo on the main dev site.
 *
 * @package MH_Dev_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce is not active on this site.' );
}

$expected_host = 'cursor-wpdev2.ddev.site';
$current_host  = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
if ( $current_host !== $expected_host ) {
	WP_CLI::error( 'Run on main dev site only (cursor-wpdev2.ddev.site).' );
}

use MH\DevKit\Modules\Commerce\Features\HybridPricing\HybridPricingFeature;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\PriceListModel;

( new HybridPricingFeature() )->register_roles();

$settings_model = new HybridPricingSettingsModel();
update_option(
	HybridPricingSettingsModel::OPTION_KEY,
	array_merge(
		$settings_model->get_defaults(),
		array(
			'hide_prices_guest'        => true,
			'hide_prices_b2c'          => true,
			'hide_b2b_only_from_b2c'   => true,
			'apply_custom_prices_cart' => true,
		)
	),
	false
);

$price_lists = new PriceListModel();

$category_rules = array();
$category_discounts = array(
	'uncategorized' => 5,
);

$terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'number'     => 5,
	)
);

if ( is_array( $terms ) ) {
	foreach ( $terms as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$category_rules[] = array(
			'type'             => 'category',
			'term_id'          => (int) $term->term_id,
			'discount_percent' => 10 + ( (int) $term->term_id % 5 ) * 2,
		);
	}
}

$product_rules = array();
$products      = wc_get_products(
	array(
		'limit'  => 3,
		'status' => 'publish',
		'orderby'=> 'ID',
		'order'  => 'ASC',
	)
);

foreach ( $products as $index => $product ) {
	$product_rules[] = array(
		'type'        => 'product',
		'product_id'  => (int) $product->get_id(),
		'fixed_price' => wc_format_decimal( max( 1, (float) $product->get_regular_price() * 0.75 ) ),
	);
	if ( 0 === $index ) {
		update_post_meta( (int) $product->get_id(), '_mhdk_b2b_only', 'yes' );
		WP_CLI::log( 'Marked B2B-only: ' . $product->get_name() );
	}
	if ( 1 === $index ) {
		update_post_meta( (int) $product->get_id(), '_mhdk_hide_price_b2c', 'yes' );
		WP_CLI::log( 'Hide price from B2C: ' . $product->get_name() );
	}
}

$price_lists->save_all_lists(
	array(
		array(
			'id'      => 'wholesale-default',
			'name'    => 'Wholesale Default',
			'enabled' => true,
			'rules'   => array_merge( $category_rules, $product_rules ),
		),
	)
);

$b2b_login = 'mhdk_b2b_demo';
$b2b_user  = get_user_by( 'login', $b2b_login );
if ( ! $b2b_user instanceof WP_User ) {
	$user_id = wp_insert_user(
		array(
			'user_login'   => $b2b_login,
			'user_pass'    => 'demo-b2b-pass',
			'user_email'   => 'b2b-demo@cursor-wpdev2.ddev.site',
			'display_name' => 'B2B Demo Customer',
			'role'         => HybridPricingSettingsModel::B2B_ROLE,
		)
	);
	if ( is_wp_error( $user_id ) ) {
		WP_CLI::warning( $user_id->get_error_message() );
	} else {
		WP_CLI::log( "Created B2B demo user: {$b2b_login} / demo-b2b-pass" );
	}
} else {
	$b2b_user->set_role( HybridPricingSettingsModel::B2B_ROLE );
	wp_update_user( $b2b_user );
	WP_CLI::log( "B2B demo user exists: {$b2b_login}" );
}

WP_CLI::success(
	sprintf(
		'Hybrid pricing demo seeded: %d category rules, %d product rules.',
		count( $category_rules ),
		count( $product_rules )
	)
);
