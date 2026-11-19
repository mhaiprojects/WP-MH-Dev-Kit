<?php
/**
 * Verify Mix and Match Bundle qty packages + mix & match on factory-sales.
 *
 * Usage:
 *   ddev wp eval-file scripts/verify-industry-demo-pricing.php --url=https://factory-sales.cursor-wpdev2.ddev.site
 *
 * @package IndustryDemos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
if ( 'factory-sales.cursor-wpdev2.ddev.site' !== $host ) {
	WP_CLI::error( 'Run against factory-sales only.' );
}

if ( ! class_exists( 'MMB_Volume_Pricing' ) || ! class_exists( 'MMB_Bundle_Tags' ) ) {
	WP_CLI::error( 'Mix and Match Bundle not loaded.' );
}

/**
 * @param list<array{0:string,1:int}> $items SKU + qty.
 */
function mmb_verify_cart( array $items ): float {
	WC()->cart->empty_cart();
	foreach ( $items as $row ) {
		$id = wc_get_product_id_by_sku( $row[0] );
		if ( ! $id ) {
			WP_CLI::error( 'Missing SKU ' . $row[0] );
		}
		WC()->cart->add_to_cart( $id, $row[1] );
	}
	WC()->cart->calculate_totals();
	return (float) WC()->cart->get_total( 'edit' );
}

$failures = 0;
$cases    = array(
	array( 'QTY sauce x1', array( array( 'FS-SAUCE-VP', 1 ) ), 10.0 ),
	array( 'QTY sauce x2', array( array( 'FS-SAUCE-VP', 2 ) ), 15.0 ),
	array( 'QTY sauce x3 packing', array( array( 'FS-SAUCE-VP', 3 ) ), 25.0 ),
	array( 'QTY sauce x4', array( array( 'FS-SAUCE-VP', 4 ) ), 30.0 ),
	array( 'QTY sauce x5', array( array( 'FS-SAUCE-VP', 5 ) ), 32.0 ),
	array( 'QTY beans x4 no mix stack', array( array( 'FS-BEANS-420', 4 ) ), 6.0 ),
	array( 'MIX any3 juices+pasta', array( array( 'FS-AJ-2L', 1 ), array( 'FS-OJ-2L', 1 ), array( 'FS-PASTA-1KG', 1 ) ), 7.0 ),
	array( 'MIX any3 x2 sets', array( array( 'FS-AJ-2L', 3 ), array( 'FS-OJ-2L', 3 ) ), 14.0 ),
	array( 'MIX below threshold', array( array( 'FS-AJ-2L', 1 ), array( 'FS-OJ-2L', 1 ) ), 6.0 ),
	array( 'MIX any4', array( array( 'FS-SPAG-420', 1 ), array( 'FS-TOM-400', 1 ), array( 'FS-CORN-420', 1 ), array( 'FS-CHICK-400', 1 ) ), 5.0 ),
	array( 'QTY+peach not in mix', array( array( 'FS-PEACH-825', 2 ), array( 'FS-AJ-2L', 1 ), array( 'FS-OJ-2L', 1 ) ), 12.0 ), // peaches 6 + juices 6 = 12, mix needs 3 tagged-only so 2 juices no discount
);

foreach ( $cases as $case ) {
	[ $label, $items, $expect ] = $case;
	$total = mmb_verify_cart( $items );
	$ok    = abs( $total - $expect ) < 0.05;
	if ( ! $ok ) {
		++$failures;
		$fees = array();
		foreach ( WC()->cart->get_fees() as $fee ) {
			$fees[] = $fee->name . '=' . $fee->amount;
		}
		WP_CLI::warning( sprintf( 'FAIL %s got %.2f expect %.2f fees=[%s]', $label, $total, $expect, implode( '; ', $fees ) ) );
	} else {
		WP_CLI::log( sprintf( 'PASS %s = %.2f', $label, $total ) );
	}
}

WC()->cart->empty_cart();

if ( $failures > 0 ) {
	WP_CLI::error( sprintf( '%d pricing case(s) failed.', $failures ) );
}

WP_CLI::success( 'All pricing cases passed.' );
