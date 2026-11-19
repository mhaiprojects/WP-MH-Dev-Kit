<?php
/**
 * Exhaustive qty-package matrix for all products with Mix and Match Bundle breaks.
 *
 * Usage:
 *   ddev wp eval-file scripts/verify-qty-packages-matrix.php --url=https://factory-sales.cursor-wpdev2.ddev.site
 *
 * @package IndustryDemos
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MMB_Volume_Pricing' ) ) {
	WP_CLI::error( 'Mix and Match Bundle volume pricing not loaded.' );
}

/**
 * Independent expected total (greedy packing) for assertion.
 *
 * @param list<array{qty:int,price:float}> $breaks Breaks.
 */
function mmb_expected_total( array $breaks, int $qty, float $fallback ): float {
	if ( $qty < 1 ) {
		return 0.0;
	}
	if ( array() === $breaks ) {
		return round( $fallback * $qty, 2 );
	}
	usort(
		$breaks,
		static function ( $a, $b ) {
			return $a['qty'] <=> $b['qty'];
		}
	);
	$remaining = $qty;
	$total     = 0.0;
	foreach ( array_reverse( $breaks ) as $break ) {
		if ( $break['qty'] > $remaining ) {
			continue;
		}
		$packs = intdiv( $remaining, $break['qty'] );
		if ( $packs < 1 ) {
			continue;
		}
		$total    += $packs * $break['price'];
		$remaining -= $packs * $break['qty'];
	}
	if ( $remaining > 0 ) {
		$unit = $fallback;
		foreach ( $breaks as $break ) {
			if ( 1 === $break['qty'] ) {
				$unit = $break['price'];
				break;
			}
		}
		$total += $remaining * $unit;
	}
	return round( $total, 2 );
}

$products = wc_get_products(
	array(
		'limit'  => -1,
		'status' => 'publish',
		'return' => 'objects',
	)
);

$failures = 0;
$checked  = 0;

foreach ( $products as $product ) {
	if ( ! $product instanceof WC_Product ) {
		continue;
	}
	$pid    = $product->get_id();
	$breaks = MMB_Volume_Pricing::get_breaks( $pid );
	if ( array() === $breaks ) {
		continue;
	}

	$sku      = $product->get_sku() ?: ( 'id-' . $pid );
	$fallback = (float) $product->get_regular_price();
	$max_break = max( array_column( $breaks, 'qty' ) );
	$max_qty   = max( 12, $max_break * 3 );

	WP_CLI::log( sprintf( '--- %s (%s) breaks=%s ---', $product->get_name(), $sku, wp_json_encode( $breaks ) ) );

	for ( $qty = 1; $qty <= $max_qty; $qty++ ) {
		$expect = mmb_expected_total( $breaks, $qty, $fallback );
		$got_fn = MMB_Volume_Pricing::package_total_for_qty( $pid, $qty, $fallback );

		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $pid, $qty );
		WC()->cart->calculate_totals();
		$got_cart = round( (float) WC()->cart->get_total( 'edit' ), 2 );

		++$checked;
		if ( abs( $got_fn - $expect ) > 0.001 || abs( $got_cart - $expect ) > 0.02 ) {
			++$failures;
			WP_CLI::warning( sprintf( 'FAIL %s qty=%d expect=%.2f fn=%.2f cart=%.2f', $sku, $qty, $expect, $got_fn, $got_cart ) );
		}
	}

	// set_quantity path: add 1 then bump through range.
	WC()->cart->empty_cart();
	$key = WC()->cart->add_to_cart( $pid, 1 );
	for ( $qty = 1; $qty <= min( 10, $max_qty ); $qty++ ) {
		WC()->cart->set_quantity( $key, $qty, true );
		WC()->cart->calculate_totals();
		$expect   = mmb_expected_total( $breaks, $qty, $fallback );
		$got_cart = round( (float) WC()->cart->get_total( 'edit' ), 2 );
		++$checked;
		if ( abs( $got_cart - $expect ) > 0.02 ) {
			++$failures;
			WP_CLI::warning( sprintf( 'FAIL set_qty %s qty=%d expect=%.2f cart=%.2f', $sku, $qty, $expect, $got_cart ) );
		}
	}

	// Merge: add 1 twice => qty 2 pricing.
	if ( $max_break >= 2 ) {
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $pid, 1 );
		WC()->cart->add_to_cart( $pid, 1 );
		WC()->cart->calculate_totals();
		$expect   = mmb_expected_total( $breaks, 2, $fallback );
		$got_cart = round( (float) WC()->cart->get_total( 'edit' ), 2 );
		++$checked;
		if ( abs( $got_cart - $expect ) > 0.02 ) {
			++$failures;
			WP_CLI::warning( sprintf( 'FAIL merge %s expect=%.2f cart=%.2f', $sku, $expect, $got_cart ) );
		} else {
			WP_CLI::log( sprintf( 'PASS merge two×1 => %.2f', $got_cart ) );
		}
	}
}

// Synthetic edge combos (temporary meta on sauce product).
$sauce_id = wc_get_product_id_by_sku( 'FS-SAUCE-VP' );
if ( $sauce_id ) {
	$original = get_post_meta( $sauce_id, MMB_Volume_Pricing::META_KEY, true );
	$edges    = array(
		'only-2'           => array( array( 'qty' => 2, 'price' => 15.0 ) ),
		'1-and-3'          => array( array( 'qty' => 1, 'price' => 10.0 ), array( 'qty' => 3, 'price' => 24.0 ) ),
		'2-and-4'          => array( array( 'qty' => 2, 'price' => 15.0 ), array( 'qty' => 4, 'price' => 28.0 ) ),
		'1-2-3'            => array( array( 'qty' => 1, 'price' => 5.0 ), array( 'qty' => 2, 'price' => 9.0 ), array( 'qty' => 3, 'price' => 12.0 ) ),
		'large-gap'        => array( array( 'qty' => 1, 'price' => 10.0 ), array( 'qty' => 10, 'price' => 70.0 ) ),
	);

	foreach ( $edges as $label => $breaks ) {
		update_post_meta( $sauce_id, MMB_Volume_Pricing::META_KEY, $breaks );
		WP_CLI::log( '--- edge case: ' . $label . ' ---' );
		for ( $qty = 1; $qty <= 12; $qty++ ) {
			$expect = mmb_expected_total( $breaks, $qty, 10.0 );
			WC()->cart->empty_cart();
			WC()->cart->add_to_cart( $sauce_id, $qty );
			WC()->cart->calculate_totals();
			$got = round( (float) WC()->cart->get_total( 'edit' ), 2 );
			++$checked;
			if ( abs( $got - $expect ) > 0.02 ) {
				++$failures;
				WP_CLI::warning( sprintf( 'FAIL edge %s qty=%d expect=%.2f cart=%.2f', $label, $qty, $expect, $got ) );
			}
		}
	}
	update_post_meta( $sauce_id, MMB_Volume_Pricing::META_KEY, $original );
}

WC()->cart->empty_cart();

if ( $failures > 0 ) {
	WP_CLI::error( sprintf( '%d failure(s) across %d checks.', $failures, $checked ) );
}

WP_CLI::success( sprintf( 'All qty package combos passed (%d checks).', $checked ) );
