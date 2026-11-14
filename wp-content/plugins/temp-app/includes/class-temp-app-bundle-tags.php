<?php
/**
 * "Buy any N for $X" deals linked by product tag.
 *
 * Option `temp_app_tag_bundles` shape:
 * [
 *   [
 *     'tag'   => 'deal-any-3',
 *     'qty'   => 3,
 *     'price' => 8.00,
 *     'label' => 'Any 3 for $8',
 *   ],
 * ]
 *
 * PoC rules:
 * - Products with TempApp quantity packages are excluded from mix & match
 *   (avoids stacking two discount engines on the same SKU).
 * - Discount base uses regular (list) price, not volume-adjusted cart price.
 * - Complete sets are built from the dearest eligible units first; leftover
 *   units (the cheapest) receive no discount.
 * - Per-set saving is clamped at zero (never negative).
 * - A product tagged for two deals is counted only toward the first deal
 *   in configuration order.
 *
 * @package TempApp
 */

declare(strict_types=1);

final class Temp_App_Bundle_Tags {

	public const OPTION_KEY = 'temp_app_tag_bundles';

	public static function init(): void {
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'apply_bundle_discount' ), 20 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_bundle_notice' ), 26 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'render_loop_badge' ), 15 );
		add_action( 'woocommerce_before_cart', array( __CLASS__, 'render_cart_hint' ) );
		add_action( 'woocommerce_before_checkout_form', array( __CLASS__, 'render_cart_hint' ), 5 );
	}

	/**
	 * @return list<array{tag:string,qty:int,price:float,label:string}>
	 */
	public static function get_bundles(): array {
		$raw = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$out = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$tag   = isset( $row['tag'] ) ? sanitize_title( (string) $row['tag'] ) : '';
			$qty   = isset( $row['qty'] ) ? (int) $row['qty'] : 0;
			$price = isset( $row['price'] ) ? (float) $row['price'] : 0.0;
			$label = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
			if ( '' === $tag || $qty < 2 || $price <= 0 ) {
				continue;
			}
			if ( '' === $label ) {
				$label = sprintf( 'Any %d for $%s', $qty, number_format( $price, 2 ) );
			}
			$out[] = array(
				'tag'   => $tag,
				'qty'   => $qty,
				'price' => $price,
				'label' => $label,
			);
		}

		return $out;
	}

	/**
	 * @param list<array{tag:string,qty:int,price:float,label:string}> $bundles Bundles.
	 */
	public static function save_bundles( array $bundles ): void {
		update_option( self::OPTION_KEY, array_values( $bundles ), false );
	}

	/**
	 * Whether a product is eligible for mix & match (has tag, no qty packages).
	 */
	public static function product_eligible_for_mix( int $product_id, string $tag ): bool {
		if ( $product_id <= 0 || ! has_term( $tag, 'product_tag', $product_id ) ) {
			return false;
		}
		if ( class_exists( 'Temp_App_Volume_Pricing' ) && array() !== Temp_App_Volume_Pricing::get_breaks( $product_id ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Build calculator input from the live cart.
	 *
	 * @return array{lines:list<array<string,mixed>>,bundles:list<array<string,mixed>>}
	 */
	public static function build_calculator_input( WC_Cart $cart ): array {
		$config_bundles = self::get_bundles();
		$bundles        = array();
		foreach ( $config_bundles as $bundle ) {
			$bundles[] = array(
				'tag'         => $bundle['tag'],
				'qty'         => $bundle['qty'],
				'price_cents' => Temp_App_Deal_Calculator::to_cents( $bundle['price'] ),
				'label'       => $bundle['label'],
			);
		}

		$lines = array();
		foreach ( $cart->get_cart() as $cart_item_key => $item ) {
			$product_id = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
			$line_qty   = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
			if ( $product_id <= 0 || $line_qty < 1 ) {
				continue;
			}
			if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
				continue;
			}

			$regular = (float) $item['data']->get_regular_price();
			if ( $regular <= 0 ) {
				$regular = (float) $item['data']->get_price();
			}
			$unit_cents = Temp_App_Deal_Calculator::to_cents( $regular );

			$tags = array();
			foreach ( $config_bundles as $bundle ) {
				if ( self::product_eligible_for_mix( $product_id, $bundle['tag'] ) ) {
					$tags[] = $bundle['tag'];
				}
			}

			$lines[] = array(
				'key'              => (string) $cart_item_key,
				'product_id'       => $product_id,
				'unit_price_cents' => $unit_cents,
				'qty'              => $line_qty,
				'tags'             => $tags,
			);
		}

		return array(
			'lines'   => $lines,
			'bundles' => $bundles,
		);
	}

	/**
	 * Run the shared deal calculator for a cart (no memoisation).
	 *
	 * @return array{deals:list<array<string,mixed>>,unassigned_keys:list<string>}
	 */
	public static function calculate_for_cart( ?WC_Cart $cart = null ): array {
		if ( null === $cart ) {
			if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
				return array(
					'deals'           => array(),
					'unassigned_keys' => array(),
				);
			}
			$cart = WC()->cart;
		}

		$input = self::build_calculator_input( $cart );
		if ( array() === $input['lines'] && array() === $input['bundles'] ) {
			return array(
				'deals'           => array(),
				'unassigned_keys' => array(),
			);
		}

		return Temp_App_Deal_Calculator::calculate( $input['lines'], $input['bundles'] );
	}

	public static function apply_bundle_discount( WC_Cart $cart ): void {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		$result = self::calculate_for_cart( $cart );
		foreach ( $result['deals'] as $deal ) {
			$saving_cents = isset( $deal['saving_cents'] ) ? (int) $deal['saving_cents'] : 0;
			$sets         = isset( $deal['sets'] ) && is_array( $deal['sets'] ) ? $deal['sets'] : array();
			if ( $saving_cents <= 0 || array() === $sets ) {
				continue;
			}

			$label = isset( $deal['label'] ) ? (string) $deal['label'] : '';
			$count = count( $sets );

			$cart->add_fee(
				sprintf(
					/* translators: 1: deal label 2: number of sets */
					__( '%1$s (%2$dx)', 'temp-app' ),
					$label,
					$count
				),
				-1 * ( $saving_cents / 100 ),
				false
			);
		}
	}

	public static function render_bundle_notice(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$pid  = $product->get_id();
		$tags = wp_get_post_terms( $pid, 'product_tag', array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $tags ) || array() === $tags ) {
			return;
		}

		if ( class_exists( 'Temp_App_Volume_Pricing' ) && array() !== Temp_App_Volume_Pricing::get_breaks( $pid ) ) {
			return;
		}

		$matches = array();
		foreach ( self::get_bundles() as $bundle ) {
			if ( in_array( $bundle['tag'], $tags, true ) ) {
				$matches[] = $bundle['label'];
			}
		}
		if ( array() === $matches ) {
			return;
		}

		echo '<div class="temp-app-tag-bundle-notice" style="margin:1em 0;padding:0.75em 1em;background:#ecfdf5;border-left:3px solid #059669;">';
		echo '<strong>' . esc_html__( 'Mix & match deal', 'temp-app' ) . '</strong><ul style="margin:0.5em 0 0;">';
		foreach ( $matches as $label ) {
			echo '<li>' . esc_html( $label ) . '</li>';
		}
		echo '</ul><p style="margin:0.5em 0 0;font-size:0.9em;">' . esc_html__( 'Add any mix of products with this deal tag. Discount appears as a cart fee once you hit the quantity.', 'temp-app' ) . '</p>';
		echo '</div>';
	}

	public static function render_loop_badge(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$pid = $product->get_id();
		if ( class_exists( 'Temp_App_Volume_Pricing' ) && array() !== Temp_App_Volume_Pricing::get_breaks( $pid ) ) {
			return;
		}
		$tags = wp_get_post_terms( $pid, 'product_tag', array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $tags ) || array() === $tags ) {
			return;
		}
		foreach ( self::get_bundles() as $bundle ) {
			if ( in_array( $bundle['tag'], $tags, true ) ) {
				echo '<span class="temp-app-deal-badge" style="display:inline-block;background:#059669;color:#fff;font-size:11px;padding:2px 8px;margin:4px 0;">'
					. esc_html( $bundle['label'] )
					. '</span>';
				return;
			}
		}
	}

	public static function render_cart_hint(): void {
		if ( ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}

		$result = self::calculate_for_cart( WC()->cart );
		$hints  = array();

		foreach ( $result['deals'] as $deal ) {
			$label  = isset( $deal['label'] ) ? (string) $deal['label'] : '';
			$sets   = isset( $deal['sets'] ) && is_array( $deal['sets'] ) ? $deal['sets'] : array();
			$need   = isset( $deal['need'] ) ? (int) $deal['need'] : 0;
			$left   = isset( $deal['leftover_qty'] ) ? (int) $deal['leftover_qty'] : 0;

			if ( array() === $sets && $left > 0 && $need > 0 ) {
				$hints[] = sprintf(
					/* translators: 1: need 2: label */
					__( 'Add %1$d more tagged item(s) to unlock %2$s.', 'temp-app' ),
					$need,
					$label
				);
				continue;
			}

			if ( array() === $sets ) {
				continue;
			}

			$hints[] = sprintf(
				/* translators: 1: label 2: sets */
				__( 'Mix & match active: %1$s × %2$d set(s).', 'temp-app' ),
				$label,
				count( $sets )
			);

			if ( $left > 0 && $need > 0 ) {
				$hints[] = sprintf(
					/* translators: 1: need 2: label */
					__( 'Add %1$d more tagged item(s) to complete another %2$s set.', 'temp-app' ),
					$need,
					$label
				);
			}
		}

		if ( array() === $hints ) {
			return;
		}
		echo '<div class="woocommerce-info temp-app-mix-hint">';
		echo esc_html( implode( ' ', $hints ) );
		echo '</div>';
	}
}