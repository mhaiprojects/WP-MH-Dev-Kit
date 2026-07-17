<?php
/**
 * Product queries and cart validation for Order Menu.
 *
 * @package MH\DevKit\Modules\Commerce\Features\OrderMenu\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\OrderMenu\Models;

final class OrderMenuModel {

	public const MAX_PRODUCTS = 100;

	public const SESSION_FLAG = 'mhdk_order_menu_used';

	private const MENU_TOKEN_SALT = 'mhdk-order-menu-context';

	public function __construct(
		private OrderMenuSettingsModel $settings = new OrderMenuSettingsModel(),
		private SavedMenuModel $saved_menus = new SavedMenuModel()
	) {}

	public function max_qty_per_line(): int {
		return $this->settings->max_qty_per_line();
	}

	/**
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return array<int, \WC_Product>
	 */
	public function get_products( array $atts ): array {
		$args = array(
			'status'  => 'publish',
			'limit'   => self::MAX_PRODUCTS,
			'return'  => 'objects',
		);

		$orderby = sanitize_key( (string) ( $atts['orderby'] ?? 'title' ) );
		$order   = strtoupper( (string) ( $atts['order'] ?? 'ASC' ) ) === 'DESC' ? 'DESC' : 'ASC';

		if ( 'price' === $orderby ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_price';
			$args['order']    = $order;
		} else {
			$args['orderby'] = 'title';
			$args['order']   = $order;
		}

		if ( ! empty( $atts['menu'] ) ) {
			$ids = $this->saved_menus->get_product_ids_for_menu( $atts['menu'] );
			if ( array() === $ids ) {
				return array();
			}
			$args['include'] = $ids;
		} elseif ( ! empty( $atts['ids'] ) ) {
			$ids = $this->parse_ids( $atts['ids'] );
			if ( array() === $ids ) {
				return array();
			}
			$args['include'] = $ids;
		} elseif ( ! empty( $atts['slugs'] ) ) {
			$slugs = $this->parse_slugs( $atts['slugs'] );
			if ( array() === $slugs ) {
				return array();
			}
			$args['slug'] = $slugs;
		} elseif ( ! empty( $atts['category'] ) ) {
			$args['category'] = array( sanitize_title( $atts['category'] ) );
		}

		$products = wc_get_products( $args );

		$filtered = array_values(
			array_filter(
				$products,
				function ( $product ): bool {
					if ( ! $product instanceof \WC_Product || ! $product->is_purchasable() ) {
						return false;
					}

					return $this->is_seasonally_available( $product->get_id() );
				}
			)
		);

		if ( ! empty( $atts['menu'] ) && ! empty( $args['include'] ) ) {
			$filtered = $this->sort_by_include_order( $filtered, $args['include'] );
		}

		return $filtered;
	}

	/**
	 * @param array<int, \WC_Product> $products Products.
	 * @param array<int, int>         $order    Product ID order.
	 * @return array<int, \WC_Product>
	 */
	private function sort_by_include_order( array $products, array $order ): array {
		$map = array();

		foreach ( $products as $product ) {
			$map[ $product->get_id() ] = $product;
		}

		$sorted = array();

		foreach ( $order as $id ) {
			if ( isset( $map[ $id ] ) ) {
				$sorted[] = $map[ $id ];
			}
		}

		return $sorted;
	}

	/**
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return array<string, string>
	 */
	public function resolve_filter_atts( array $atts ): array {
		$normalized = $this->normalize_atts( $atts );

		if ( '' === $normalized['category'] && '' === $normalized['menu'] && '' === $normalized['ids'] && '' === $normalized['slugs'] ) {
			$normalized['category'] = (string) $this->settings->get( 'default_category', '' );
		}

		return $normalized;
	}

	/**
	 * @param array<string, string> $atts        Resolved filter attributes.
	 * @param array<int, int>       $product_ids Product IDs shown on the menu.
	 */
	public function create_menu_token( array $atts, array $product_ids ): string {
		$ids = array_values(
			array_filter(
				array_map( 'absint', $product_ids ),
				static fn( int $id ): bool => $id > 0
			)
		);
		sort( $ids );

		$payload = wp_json_encode(
			array(
				'atts' => $this->normalize_atts( $atts ),
				'ids'  => $ids,
			)
		);

		if ( ! is_string( $payload ) ) {
			return '';
		}

		$encoded = rtrim( strtr( base64_encode( $payload ), '+/', '-_' ), '=' );
		$sig     = hash_hmac( 'sha256', $encoded, wp_salt( self::MENU_TOKEN_SALT ) );

		return $encoded . '.' . $sig;
	}

	/**
	 * @return array{atts: array<string, string>, product_ids: array<int, int>}|null
	 */
	public function verify_menu_token( string $token ): ?array {
		$token = trim( $token );

		if ( '' === $token || ! str_contains( $token, '.' ) ) {
			return null;
		}

		[ $encoded, $sig ] = explode( '.', $token, 2 );

		if ( '' === $encoded || '' === $sig ) {
			return null;
		}

		$expected = hash_hmac( 'sha256', $encoded, wp_salt( self::MENU_TOKEN_SALT ) );

		if ( ! hash_equals( $expected, $sig ) ) {
			return null;
		}

		$padded   = strtr( $encoded, '-_', '+/' );
		$mod      = strlen( $padded ) % 4;
		$decoded  = base64_decode( $mod ? $padded . str_repeat( '=', 4 - $mod ) : $padded, true );
		$payload  = is_string( $decoded ) ? json_decode( $decoded, true ) : null;

		if ( ! is_array( $payload ) || ! isset( $payload['atts'], $payload['ids'] ) || ! is_array( $payload['atts'] ) || ! is_array( $payload['ids'] ) ) {
			return null;
		}

		$product_ids = array_values(
			array_filter(
				array_map( 'absint', $payload['ids'] ),
				static fn( int $id ): bool => $id > 0
			)
		);

		if ( array() === $product_ids ) {
			return null;
		}

		return array(
			'atts'        => $this->normalize_atts( $payload['atts'] ),
			'product_ids' => $product_ids,
		);
	}

	/**
	 * @param array<string, string> $atts              Filter attributes (for cart qty scope).
	 * @param array<int, int>       $quantities        Product ID => quantity.
	 * @param array<int, int>|null  $allowed_product_ids Verified allowlist; when set, only these IDs may be added.
	 * @return array{added: int, skipped: int, messages: array<int, string>, cart_quantities: array<int, int>}
	 */
	public function add_to_cart( array $atts, array $quantities, ?array $allowed_product_ids = null ): array {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array(
				'added'           => 0,
				'skipped'         => 0,
				'messages'        => array( __( 'Cart is unavailable.', 'mh-dev-kit' ) ),
				'cart_quantities' => array(),
			);
		}

		if ( null !== $allowed_product_ids ) {
			$allowed_ids = array_flip(
				array_values(
					array_filter(
						array_map( 'absint', $allowed_product_ids ),
						static fn( int $id ): bool => $id > 0
					)
				)
			);
		} else {
			$allowed     = $this->get_products( $atts );
			$allowed_ids = array_flip(
				array_map(
					static fn( \WC_Product $product ): int => $product->get_id(),
					$allowed
				)
			);
		}

		$added    = 0;
		$skipped  = 0;
		$messages = array();
		$max_qty  = $this->max_qty_per_line();

		foreach ( $quantities as $product_id => $qty ) {
			$product_id = (int) $product_id;
			$qty        = (int) $qty;

			if ( $qty <= 0 ) {
				continue;
			}

			if ( $qty > $max_qty ) {
				$qty = $max_qty;
			}

			if ( ! isset( $allowed_ids[ $product_id ] ) ) {
				++$skipped;
				continue;
			}

			if ( ! $this->is_in_stock( $product_id ) ) {
				++$skipped;
				$messages[] = sprintf(
					/* translators: %d: product ID */
					__( 'Product #%d is out of stock.', 'mh-dev-kit' ),
					$product_id
				);
				continue;
			}

			$result = WC()->cart->add_to_cart( $product_id, $qty );

			if ( $result ) {
				++$added;
				$this->mark_order_menu_session();
			} else {
				++$skipped;
				$messages[] = sprintf(
					/* translators: %d: product ID */
					__( 'Could not add product #%d to cart.', 'mh-dev-kit' ),
					$product_id
				);
			}
		}

		$product_ids = array_keys( $allowed_ids );

		return array(
			'added'           => $added,
			'skipped'         => $skipped,
			'messages'        => $messages,
			'cart_quantities' => $this->get_cart_quantities( $product_ids ),
		);
	}

	public function mark_order_menu_session(): void {
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->set( self::SESSION_FLAG, '1' );
		}
	}

	/**
	 * @return array<int, int>
	 */
	public function sanitize_quantities( array $raw ): array {
		$clean   = array();
		$max_qty = $this->max_qty_per_line();

		foreach ( $raw as $key => $value ) {
			if ( ! is_numeric( $key ) ) {
				continue;
			}

			$product_id = (int) $key;
			$qty        = (int) $value;

			if ( $product_id <= 0 || $qty <= 0 ) {
				continue;
			}

			$clean[ $product_id ] = min( $qty, $max_qty );
		}

		return $clean;
	}

	/**
	 * @param array<int, int> $product_ids Product IDs.
	 * @return array<int, int>
	 */
	public function get_cart_quantities( array $product_ids ): array {
		$quantities = array_fill_keys( $product_ids, 0 );

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return $quantities;
		}

		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id = (int) ( $item['product_id'] ?? 0 );

			if ( isset( $quantities[ $product_id ] ) ) {
				$quantities[ $product_id ] += (int) ( $item['quantity'] ?? 0 );
			}
		}

		return $quantities;
	}

	/**
	 * @return array<int, int> Product ID => qty from last completed order.
	 */
	public function get_last_order_quantities(): array {
		if ( ! is_user_logged_in() ) {
			return array();
		}

		$orders = wc_get_orders(
			array(
				'customer_id' => get_current_user_id(),
				'status'      => array( 'completed', 'processing' ),
				'limit'       => 1,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);

		if ( array() === $orders ) {
			return array();
		}

		$order = $orders[0];

		if ( ! $order instanceof \WC_Order ) {
			return array();
		}

		$quantities = array();

		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof \WC_Order_Item_Product ) {
				continue;
			}

			$product_id = (int) $item->get_product_id();

			if ( $product_id > 0 ) {
				$quantities[ $product_id ] = (int) $item->get_quantity();
			}
		}

		return $quantities;
	}

	public function menu_description( \WC_Product $product ): string {
		$override = get_post_meta( $product->get_id(), OrderMenuSettingsModel::META_MENU_DESCRIPTION, true );

		if ( is_string( $override ) && '' !== $override ) {
			return wp_strip_all_tags( $override );
		}

		$short = $product->get_short_description();

		if ( '' !== $short ) {
			return wp_strip_all_tags( $short );
		}

		return wp_trim_words( wp_strip_all_tags( $product->get_description() ), 24, '…' );
	}

	public function parse_unit( \WC_Product $product ): string {
		$name = $product->get_name();

		if ( preg_match( '/\(([^)]+)\)\s*$/', $name, $matches ) ) {
			return trim( $matches[1] );
		}

		return '';
	}

	/**
	 * @return array{label: string, class: string, purchasable: bool}
	 */
	public function get_stock_badge( \WC_Product $product ): array {
		if ( ! $product->managing_stock() && 'instock' === $product->get_stock_status() ) {
			return array(
				'label'       => __( 'In stock', 'mh-dev-kit' ),
				'class'       => 'in-stock',
				'purchasable' => true,
			);
		}

		$status = $product->get_stock_status();

		if ( 'outofstock' === $status ) {
			return array(
				'label'       => __( 'Out of stock', 'mh-dev-kit' ),
				'class'       => 'out-of-stock',
				'purchasable' => false,
			);
		}

		if ( 'onbackorder' === $status ) {
			return array(
				'label'       => __( 'On backorder', 'mh-dev-kit' ),
				'class'       => 'on-backorder',
				'purchasable' => true,
			);
		}

		$qty = $product->get_stock_quantity();

		if ( null !== $qty && $qty <= 5 ) {
			return array(
				'label'       => __( 'Low stock', 'mh-dev-kit' ),
				'class'       => 'low-stock',
				'purchasable' => true,
			);
		}

		return array(
			'label'       => __( 'In stock', 'mh-dev-kit' ),
			'class'       => 'in-stock',
			'purchasable' => true,
		);
	}

	public function is_in_stock( int $product_id ): bool {
		$product = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product ) {
			return false;
		}

		return $this->get_stock_badge( $product )['purchasable'];
	}

	public function is_seasonally_available( int $product_id ): bool {
		$from  = get_post_meta( $product_id, OrderMenuSettingsModel::META_AVAILABLE_FROM, true );
		$until = get_post_meta( $product_id, OrderMenuSettingsModel::META_AVAILABLE_UNTIL, true );

		if ( ( ! is_string( $from ) || '' === $from ) && ( ! is_string( $until ) || '' === $until ) ) {
			return true;
		}

		$today = gmdate( 'Y-m-d' );

		if ( is_string( $from ) && '' !== $from && $today < $from ) {
			return false;
		}

		if ( is_string( $until ) && '' !== $until && $today > $until ) {
			return false;
		}

		return true;
	}

	/**
	 * @param array<int, \WC_Product> $products Products.
	 * @return array<string, array<int, array<string, mixed>>>
	 */
	public function group_by_category( array $products ): array {
		$groups = array(
			'_uncategorized' => array(
				'label' => __( 'General', 'mh-dev-kit' ),
				'rows'  => array(),
			),
		);

		foreach ( $products as $product ) {
			$terms = get_the_terms( $product->get_id(), 'product_cat' );
			$key   = '_uncategorized';
			$label = __( 'General', 'mh-dev-kit' );

			if ( is_array( $terms ) && isset( $terms[0] ) && $terms[0] instanceof \WP_Term ) {
				$key   = $terms[0]->slug;
				$label = $terms[0]->name;
			}

			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array(
					'label' => $label,
					'rows'  => array(),
				);
			}

			$groups[ $key ]['rows'][] = $product;
		}

		return array_filter(
			$groups,
			static fn( array $group ): bool => array() !== $group['rows']
		);
	}

	/**
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return array<string, string>
	 */
	public function normalize_atts( array $atts ): array {
		return array(
			'category'   => sanitize_title( (string) ( $atts['category'] ?? '' ) ),
			'ids'        => sanitize_text_field( (string) ( $atts['ids'] ?? '' ) ),
			'slugs'      => sanitize_text_field( (string) ( $atts['slugs'] ?? '' ) ),
			'menu'       => sanitize_title( (string) ( $atts['menu'] ?? '' ) ),
			'orderby'    => sanitize_key( (string) ( $atts['orderby'] ?? 'title' ) ),
			'order'      => strtoupper( (string) ( $atts['order'] ?? 'ASC' ) ) === 'DESC' ? 'DESC' : 'ASC',
			'show_image' => (string) ( $atts['show_image'] ?? '' ),
		);
	}

	/**
	 * @return array<int, int>
	 */
	private function parse_ids( string $raw ): array {
		$parts = preg_split( '/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY );

		if ( ! is_array( $parts ) ) {
			return array();
		}

		$ids = array_map( 'absint', $parts );

		return array_values( array_filter( $ids, static fn( int $id ): bool => $id > 0 ) );
	}

	/**
	 * @return array<int, string>
	 */
	private function parse_slugs( string $raw ): array {
		$parts = preg_split( '/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY );

		if ( ! is_array( $parts ) ) {
			return array();
		}

		return array_values(
			array_filter(
				array_map( 'sanitize_title', $parts ),
				static fn( string $slug ): bool => '' !== $slug
			)
		);
	}
}
