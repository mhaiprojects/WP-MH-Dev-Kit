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

		$result = Temp_App_Bundle_Tags::calculate_for_cart( WC()->cart );
		$deals  = isset( $result['deals'] ) && is_array( $result['deals'] ) ? $result['deals'] : array();

		/** @var array<string,int> $in_deal_qty Units allocated to a deal (sets or leftover). */
		$in_deal_qty = array();
		/** @var list<array{key:string,qty:int,deal_label:string,need:int}> $incomplete_items */
		$incomplete_items = array();

		foreach ( $deals as $deal ) {
			$tag         = isset( $deal['tag'] ) ? (string) $deal['tag'] : '';
			$label       = isset( $deal['label'] ) ? (string) $deal['label'] : '';
			$price_cents = isset( $deal['price_cents'] ) ? (int) $deal['price_cents'] : 0;
			$set_units   = isset( $deal['qty'] ) ? (int) $deal['qty'] : 0;
			$sets        = isset( $deal['sets'] ) && is_array( $deal['sets'] ) ? $deal['sets'] : array();
			$need        = isset( $deal['need'] ) ? (int) $deal['need'] : 0;
			$leftover    = isset( $deal['leftover'] ) && is_array( $deal['leftover'] ) ? $deal['leftover'] : array();
			$total_sets  = count( $sets );

			foreach ( $sets as $set ) {
				$saving_cents  = isset( $set['saving_cents'] ) ? (int) $set['saving_cents'] : 0;
				$regular_cents = isset( $set['regular_cents'] ) ? (int) $set['regular_cents'] : 0;
				$index         = isset( $set['index'] ) ? (int) $set['index'] : 0;
				$raw_items     = isset( $set['items'] ) && is_array( $set['items'] ) ? $set['items'] : array();
				$reassigned    = isset( $set['reassigned_keys'] ) && is_array( $set['reassigned_keys'] ) ? $set['reassigned_keys'] : array();
				$items         = self::items_for_display( $raw_items );

				foreach ( $items as $row ) {
					$key = $row['key'];
					if ( ! isset( $in_deal_qty[ $key ] ) ) {
						$in_deal_qty[ $key ] = 0;
					}
					$in_deal_qty[ $key ] += (int) $row['qty'];
				}

				$saves = $saving_cents / 100;

				if ( $saving_cents > 0 ) {
					$subheading = sprintf(
						/* translators: 1: saving 2: regular total 3: deal price */
						__( 'Saves %1$s on this set (regular %2$s, deal price %3$s)', 'temp-app' ),
						self::format_plain_price( $saves ),
						self::format_plain_price( $regular_cents / 100 ),
						self::format_plain_price( $price_cents / 100 )
					);
				} else {
					$subheading = sprintf(
						/* translators: 1: regular total 2: deal price */
						__( 'No saving on this set (regular %1$s is already at or below the deal price %2$s)', 'temp-app' ),
						self::format_plain_price( $regular_cents / 100 ),
						self::format_plain_price( $price_cents / 100 )
					);
				}

				$reassign_note = self::reassigned_units_note( $raw_items, $reassigned, $set );
				if ( '' !== $reassign_note ) {
					$subheading .= ' ' . $reassign_note;
				}

				$groups[] = array(
					'id'            => $tag . '-' . $index,
					'type'          => 'set',
					'heading'       => sprintf(
						/* translators: 1: deal label 2: set index 3: total sets */
						__( '%1$s — Set %2$d of %3$d', 'temp-app' ),
						$label,
						$index,
						$total_sets
					),
					'subheading'    => $subheading,
					'items'         => $items,
					'saves'         => $saves,
					'saving_cents'  => $saving_cents,
					'set_units'     => $set_units,
				);
			}

			if ( array() !== $leftover ) {
				$left_items = self::items_for_display( $leftover );
				foreach ( $left_items as $row ) {
					$key = $row['key'];
					if ( ! isset( $in_deal_qty[ $key ] ) ) {
						$in_deal_qty[ $key ] = 0;
					}
					$in_deal_qty[ $key ] += (int) $row['qty'];
					$incomplete_items[]  = array(
						'key'        => $key,
						'qty'        => (int) $row['qty'],
						'deal_label' => $label,
						'need'       => $need,
					);
				}
			}
		}

		if ( array() !== $incomplete_items ) {
			$groups[] = array(
				'id'         => 'incomplete',
				'type'       => 'incomplete',
				'heading'    => __( 'Incomplete deals', 'temp-app' ),
				'subheading' => __( 'These items are not in a complete set and get no discount', 'temp-app' ),
				'items'      => $incomplete_items,
			);
		}

		$unassigned = isset( $result['unassigned_keys'] ) && is_array( $result['unassigned_keys'] )
			? $result['unassigned_keys']
			: array();
		$unassigned_map = array_fill_keys( $unassigned, true );

		$other_items = array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
			$line_qty = isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
			if ( $line_qty < 1 ) {
				continue;
			}
			$key = (string) $cart_item_key;

			if ( isset( $unassigned_map[ $key ] ) ) {
				$other_items[] = array(
					'key' => $key,
					'qty' => $line_qty,
				);
				continue;
			}

			$used       = $in_deal_qty[ $key ] ?? 0;
			$unassigned_units = $line_qty - $used;
			if ( $unassigned_units > 0 ) {
				$other_items[] = array(
					'key' => $key,
					'qty' => $unassigned_units,
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
	 * @param list<array{key:string,qty:int}> $raw_items       Set items with qty.
	 * @param list<string>                    $reassigned_keys Foreign cart keys.
	 * @param array<string,mixed>             $set             Calculator set row.
	 */
	private static function reassigned_units_note( array $raw_items, array $reassigned_keys, array $set ): string {
		if ( array() === $reassigned_keys ) {
			return '';
		}

		$foreign_key_map = array_fill_keys( $reassigned_keys, true );
		$moved_qty       = 0;
		foreach ( $raw_items as $row ) {
			$key = isset( $row['key'] ) ? (string) $row['key'] : '';
			if ( isset( $foreign_key_map[ $key ] ) ) {
				$moved_qty += isset( $row['qty'] ) ? (int) $row['qty'] : 0;
			}
		}
		if ( $moved_qty < 1 ) {
			return '';
		}

		$other_label = isset( $set['reassigned_from_label'] ) ? (string) $set['reassigned_from_label'] : '';
		if ( '' === $other_label ) {
			$other_label = __( 'another deal', 'temp-app' );
		}

		return sprintf(
			/* translators: 1: unit count 2: deal label items were moved from */
			__( '(includes %1$d moved from %2$s)', 'temp-app' ),
			$moved_qty,
			$other_label
		);
	}

	/**
	 * Strip calculator-only fields from set / leftover item rows.
	 *
	 * @param list<array{key:string,qty:int,product_id?:int,unit_price_cents?:int}> $raw Items.
	 * @return list<array{key:string,qty:int}>
	 */
	private static function items_for_display( array $raw ): array {
		$out = array();
		foreach ( $raw as $row ) {
			$out[] = array(
				'key' => (string) $row['key'],
				'qty' => (int) $row['qty'],
			);
		}
		return $out;
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