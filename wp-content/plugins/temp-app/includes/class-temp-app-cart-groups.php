<?php
/**
 * Display-only cart grouping for mix & match bundle deals.
 *
 * @package TempApp
 */

declare(strict_types=1);

final class Temp_App_Cart_Groups {

	public static function init(): void {
		add_filter( 'woocommerce_cart_item_class', array( __CLASS__, 'cart_item_class' ), 10, 3 );
		add_action( 'woocommerce_cart_contents', array( __CLASS__, 'render_groups_data_row' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 25 );
	}

	/**
	 * Whether grouping UI should run (bundles configured and at least one eligible cart line).
	 */
	public static function should_group(): bool {
		if ( array() === Temp_App_Bundle_Tags::get_bundles() ) {
			return false;
		}
		if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
			return false;
		}

		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
			if ( $product_id <= 0 ) {
				continue;
			}
			foreach ( Temp_App_Bundle_Tags::get_bundles() as $bundle ) {
				if ( Temp_App_Bundle_Tags::product_eligible_for_mix( $product_id, $bundle['tag'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param string $class         Row class string.
	 * @param array  $cart_item     Cart item.
	 * @param string $cart_item_key Cart item key.
	 */
	public static function cart_item_class( string $class, array $cart_item, string $cart_item_key ): string {
		if ( ! is_cart() || ! self::should_group() ) {
			return $class;
		}

		$safe_key = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $cart_item_key );
		if ( '' === $safe_key ) {
			return $class;
		}

		return $class . ' temp-app-ci-' . $safe_key;
	}

	public static function enqueue_assets(): void {
		if ( ! is_cart() || ! self::should_group() ) {
			return;
		}

		$url = plugins_url( 'assets/', TEMP_APP_FILE );

		wp_enqueue_style(
			'temp-app-cart-groups',
			$url . 'css/cart-groups.css',
			array(),
			TEMP_APP_VERSION
		);

		wp_enqueue_script(
			'temp-app-cart-groups',
			$url . 'js/cart-groups.js',
			array( 'jquery' ),
			TEMP_APP_VERSION,
			true
		);

		$computed = self::compute_groups();
		wp_localize_script(
			'temp-app-cart-groups',
			'TempAppCartGroups',
			array(
				'groups' => $computed['groups'],
			)
		);
	}

	public static function render_groups_data_row(): void {
		if ( ! self::should_group() ) {
			return;
		}

		$payload = wp_json_encode(
			self::compute_groups(),
			JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
		);
		if ( false === $payload ) {
			return;
		}

		$colspan = 6;
		echo '<tr class="temp-app-groups-data" style="display:none;" aria-hidden="true">';
		echo '<td colspan="' . esc_attr( (string) $colspan ) . '">';
		echo '<script type="application/json" id="temp-app-cart-groups-data">';
		echo $payload; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON_HEX_* encoded.
		echo '</script>';
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * @return array{groups:list<array<string,mixed>>}
	 */
	public static function compute_groups(): array {
		$groups = array();

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array( 'groups' => $groups );
		}

		$bundles = Temp_App_Bundle_Tags::get_bundles();
		if ( array() === $bundles ) {
			return array( 'groups' => $groups );
		}

		/** @var array<string,int> $assigned_qty Units already allocated to an earlier bundle (display only). */
		$assigned_qty = array();

		foreach ( $bundles as $bundle ) {
			$n     = $bundle['qty'];
			$tag   = $bundle['tag'];
			$label = $bundle['label'];
			$price = (float) $bundle['price'];

			/** @var list<array{key:string,qty:int,unit:float}> $blocks */
			$blocks = array();

			// Same basis as Temp_App_Bundle_Tags::apply_bundle_discount(): average regular price of all eligible units.
			$all_qty = 0;
			$all_sum = 0.0;

			foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
				$product_id = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
				$line_qty   = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
				if ( $product_id <= 0 || $line_qty < 1 ) {
					continue;
				}
				if ( ! Temp_App_Bundle_Tags::product_eligible_for_mix( $product_id, $tag ) ) {
					continue;
				}

				if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
					continue;
				}

				$regular = (float) $item['data']->get_regular_price();
				if ( $regular <= 0 ) {
					$regular = (float) $item['data']->get_price();
				}
				$all_qty += $line_qty;
				$all_sum += $regular * $line_qty;

				$used      = $assigned_qty[ $cart_item_key ] ?? 0;
				$remaining = $line_qty - $used;
				if ( $remaining < 1 ) {
					continue;
				}

				$blocks[] = array(
					'key'  => $cart_item_key,
					'qty'  => $remaining,
					'unit' => $regular,
				);
			}

			if ( array() === $blocks ) {
				continue;
			}

			$pool_qty = 0;
			foreach ( $blocks as $block ) {
				$pool_qty += $block['qty'];
			}

			$total_sets = intdiv( $pool_qty, $n );
			$unit_avg   = $all_qty > 0 ? ( $all_sum / $all_qty ) : 0.0;
			// Per-set share of the cart fee, so the headings add up to the discount line in the totals.
			$saves = max( 0.0, round( $unit_avg * $n - $price, wc_get_price_decimals() ) );

			for ( $set_index = 1; $set_index <= $total_sets; $set_index++ ) {
				$taken = self::take_units_from_blocks( $blocks, $n );
				if ( $taken['qty'] < 1 ) {
					break;
				}

				$groups[] = array(
					'id'         => $tag . '-' . $set_index,
					'type'       => 'set',
					'heading'    => sprintf(
						/* translators: 1: deal label 2: set index 3: total sets */
						__( '%1$s — Set %2$d of %3$d', 'temp-app' ),
						$label,
						$set_index,
						$total_sets
					),
					'subheading' => sprintf(
						/* translators: 1: saving 2: deal price */
						__( 'Saves %1$s on this set (deal price %2$s)', 'temp-app' ),
						self::format_plain_price( $saves ),
						self::format_plain_price( $price )
					),
					'items'      => $taken['items'],
					'saves'      => $saves,
				);

				self::mark_assigned( $assigned_qty, $taken['items'] );
			}

			$leftover_qty = 0;
			foreach ( $blocks as $block ) {
				$leftover_qty += $block['qty'];
			}

			if ( $leftover_qty > 0 ) {
				$left_items = array();
				foreach ( $blocks as $block ) {
					if ( $block['qty'] < 1 ) {
						continue;
					}
					$left_items[] = array(
						'key' => $block['key'],
						'qty' => $block['qty'],
					);
				}

				$need = $n - $leftover_qty;
				if ( $need < 1 ) {
					$need = 0;
				}

				$groups[] = array(
					'id'         => $tag . '-leftover',
					'type'       => 'leftover',
					'heading'    => sprintf(
						/* translators: %s: deal label */
						__( '%1$s — Not yet in a deal', 'temp-app' ),
						$label
					),
					'subheading' => sprintf(
						/* translators: %d: items still needed */
						__( 'Add %d more eligible item(s) to complete another set', 'temp-app' ),
						$need
					),
					'items'      => $left_items,
					'need'       => $need,
				);

				self::mark_assigned( $assigned_qty, $left_items );
			}
		}

		$other_items = array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
			$line_qty = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
			if ( $line_qty < 1 ) {
				continue;
			}
			$used       = $assigned_qty[ $cart_item_key ] ?? 0;
			$unassigned = $line_qty - $used;
			if ( $unassigned > 0 ) {
				$other_items[] = array(
					'key' => $cart_item_key,
					'qty' => $unassigned,
				);
			}
		}

		if ( array() !== $other_items ) {
			$groups[] = array(
				'id'         => 'other',
				'type'       => 'other',
				'heading'    => __( 'Other items', 'temp-app' ),
				'subheading' => __( 'Not part of a mix & match deal', 'temp-app' ),
				'items'      => $other_items,
			);
		}

		return array( 'groups' => $groups );
	}

	/**
	 * @param list<array{key:string,qty:int,unit:float}> $blocks Mutable blocks.
	 * @param int                                         $take   Units to take.
	 * @return array{qty:int,regular_sum:float,items:list<array{key:string,qty:int}>}
	 */
	private static function take_units_from_blocks( array &$blocks, int $take ): array {
		$need         = $take;
		$regular_sum  = 0.0;
		$qty_taken    = 0;
		$items_by_key = array();

		foreach ( $blocks as &$block ) {
			if ( $need < 1 ) {
				break;
			}
			if ( $block['qty'] < 1 ) {
				continue;
			}
			$slice = min( $block['qty'], $need );
			$key   = $block['key'];

			if ( ! isset( $items_by_key[ $key ] ) ) {
				$items_by_key[ $key ] = 0;
			}
			$items_by_key[ $key ] += $slice;

			$regular_sum += $block['unit'] * $slice;
			$qty_taken   += $slice;
			$block['qty'] -= $slice;
			$need        -= $slice;
		}
		unset( $block );

		$items = array();
		foreach ( $items_by_key as $key => $qty ) {
			$items[] = array(
				'key' => $key,
				'qty' => $qty,
			);
		}

		return array(
			'qty'          => $qty_taken,
			'regular_sum'  => $regular_sum,
			'items'        => $items,
		);
	}

	/**
	 * @param array<string,int>                          $assigned_qty Assigned quantities map.
	 * @param list<array{key:string,qty:int}> $items        Items to mark.
	 */
	private static function mark_assigned( array &$assigned_qty, array $items ): void {
		foreach ( $items as $row ) {
			$key = $row['key'];
			if ( ! isset( $assigned_qty[ $key ] ) ) {
				$assigned_qty[ $key ] = 0;
			}
			$assigned_qty[ $key ] += (int) $row['qty'];
		}
	}

	private static function format_plain_price( float $amount ): string {
		$symbol   = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );
		$decimals = wc_get_price_decimals();
		return $symbol . number_format(
			$amount,
			$decimals,
			wc_get_price_decimal_separator(),
			wc_get_price_thousand_separator()
		);
	}
}