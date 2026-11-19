<?php
/**
 * Shared helpers for industry demo seed scripts.
 *
 * @package IndustryDemos
 */

declare(strict_types=1);

final class IndustryDemoHelpers {

	public const BV = '4.27.7';

	public static function assert_host( string $expected_host ): void {
		$current_host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
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
	}

	public static function apply_builder_meta( int $post_id ): void {
		update_post_meta( $post_id, '_et_pb_use_builder', 'on' );
		update_post_meta( $post_id, '_et_pb_page_layout', 'et_full_width_page' );
		update_post_meta( $post_id, '_et_pb_built_for_post_type', 'page' );
		update_post_meta( $post_id, '_et_pb_ab_current_shortcode', 'off' );
		update_post_meta( $post_id, '_et_pb_enable_shortcode_tracking', '' );
	}

	public static function upsert_page( string $slug, string $title, string $layout, string $excerpt = '' ): int {
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
		self::apply_builder_meta( $post_id );

		return $post_id;
	}

	/**
	 * @param array<string, int>   $page_ids Map slug => ID.
	 * @param list<array{type:string,slug?:string,title?:string,url?:string}> $nav
	 */
	public static function rebuild_primary_menu( string $menu_name, array $page_ids, array $nav ): void {
		$menu    = wp_get_nav_menu_object( $menu_name );
		$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

		if ( $menu_id <= 0 ) {
			WP_CLI::warning( 'Could not create primary menu.' );
			return;
		}

		$locations                   = get_theme_mod( 'nav_menu_locations', array() );
		$locations                   = is_array( $locations ) ? $locations : array();
		$locations['primary-menu']   = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );

		$existing_items = wp_get_nav_menu_items( $menu_id );
		if ( is_array( $existing_items ) ) {
			foreach ( $existing_items as $item ) {
				if ( $item instanceof WP_Post ) {
					wp_delete_post( (int) $item->ID, true );
				}
			}
		}

		$position = 0;
		foreach ( $nav as $node ) {
			++$position;
			if ( ( $node['type'] ?? '' ) === 'link' ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'    => $node['title'] ?? '',
						'menu-item-url'      => home_url( (string) ( $node['url'] ?? '/' ) ),
						'menu-item-type'     => 'custom',
						'menu-item-status'   => 'publish',
						'menu-item-position' => $position,
					)
				);
				continue;
			}

			$slug = $node['slug'] ?? '';
			if ( '' === $slug || ! isset( $page_ids[ $slug ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $node['title'] ?? $slug,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_ids[ $slug ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $position,
				)
			);
		}
	}

	/**
	 * @param array<string, mixed> $attrs Shortcode attributes.
	 */
	public static function sc( string $tag, array $attrs = array(), string $inner = '' ): string {
		$parts = array( $tag, '_builder_version="' . self::BV . '"' );
		foreach ( $attrs as $key => $value ) {
			if ( null === $value || '' === $value ) {
				continue;
			}
			$parts[] = sprintf( '%s="%s"', $key, htmlspecialchars( (string) $value, ENT_QUOTES ) );
		}
		$open = '[' . implode( ' ', $parts ) . ']';
		if ( '' === $inner && str_ends_with( $tag, '/' ) === false ) {
			// Self-closing style for empty modules that typically self-close.
			return $open . '[/' . $tag . ']';
		}
		return $open . $inner . '[/' . $tag . ']';
	}

	public static function section( string $inner, array $attrs = array() ): string {
		return self::sc( 'et_pb_section', $attrs, $inner );
	}

	public static function row( string $inner, array $attrs = array() ): string {
		$attrs = array_merge( array( 'column_structure' => '4_4' ), $attrs );
		return self::sc( 'et_pb_row', $attrs, $inner );
	}

	public static function column( string $type, string $inner, array $attrs = array() ): string {
		$attrs = array_merge( array( 'type' => $type ), $attrs );
		return self::sc( 'et_pb_column', $attrs, $inner );
	}

	public static function text( string $content, array $attrs = array() ): string {
		return self::sc( 'et_pb_text', $attrs, $content );
	}

	public static function button( string $label, string $url, array $attrs = array() ): string {
		$attrs = array_merge(
			array(
				'button_text' => $label,
				'button_url'  => $url,
			),
			$attrs
		);
		return self::sc( 'et_pb_button', $attrs );
	}

	public static function blurb( string $title, string $content, array $attrs = array() ): string {
		$attrs = array_merge( array( 'title' => $title ), $attrs );
		return self::sc( 'et_pb_blurb', $attrs, $content );
	}

	public static function fullwidth_header( string $title, string $subhead, array $attrs = array() ): string {
		$attrs = array_merge(
			array(
				'title'   => $title,
				'subhead' => $subhead,
			),
			$attrs
		);
		return self::sc( 'et_pb_fullwidth_header', $attrs );
	}

	/**
	 * Ensure a product category exists; return term_id.
	 */
	public static function ensure_product_cat( string $slug, string $name, int $parent = 0 ): int {
		$existing = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $existing instanceof WP_Term ) {
			return (int) $existing->term_id;
		}
		$result = wp_insert_term(
			$name,
			'product_cat',
			array(
				'slug'   => $slug,
				'parent' => $parent,
			)
		);
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( $result->get_error_message() );
			return 0;
		}
		return (int) $result['term_id'];
	}

	/**
	 * Ensure a product tag exists; return term_id.
	 */
	public static function ensure_product_tag( string $slug, string $name ): int {
		$existing = get_term_by( 'slug', $slug, 'product_tag' );
		if ( $existing instanceof WP_Term ) {
			return (int) $existing->term_id;
		}
		$result = wp_insert_term(
			$name,
			'product_tag',
			array(
				'slug' => $slug,
			)
		);
		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( $result->get_error_message() );
			return 0;
		}
		return (int) $result['term_id'];
	}

	/**
	 * Create or update a simple WooCommerce product.
	 *
	 * @param array{
	 *   slug:string,
	 *   name:string,
	 *   price:string|float,
	 *   regular_price?:string|float,
	 *   description?:string,
	 *   short_description?:string,
	 *   cats?:list<int>,
	 *   tags?:list<int>,
	 *   sku?:string,
	 *   stock?:int|null,
	 *   qty_breaks?:list<array{qty:int,price:float}>,
	 *   meta?:array<string,mixed>
	 * } $data Product data.
	 */
	public static function upsert_product( array $data ): int {
		if ( ! function_exists( 'wc_get_product_id_by_sku' ) ) {
			WP_CLI::error( 'WooCommerce is required to seed products.' );
		}

		$slug = $data['slug'];
		$existing = get_page_by_path( $slug, OBJECT, 'product' );
		$product  = $existing instanceof WP_Post ? wc_get_product( $existing->ID ) : null;

		if ( ! $product instanceof WC_Product ) {
			$product = new WC_Product_Simple();
		}

		$product->set_name( $data['name'] );
		$product->set_slug( $slug );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_description( $data['description'] ?? '' );
		$product->set_short_description( $data['short_description'] ?? '' );
		$regular = (string) ( $data['regular_price'] ?? $data['price'] );
		$product->set_regular_price( $regular );
		$product->set_price( (string) $data['price'] );
		if ( ! empty( $data['sku'] ) ) {
			$product->set_sku( (string) $data['sku'] );
		}
		if ( array_key_exists( 'stock', $data ) && null !== $data['stock'] ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( (int) $data['stock'] );
			$product->set_stock_status( 'instock' );
		} else {
			$product->set_manage_stock( false );
			$product->set_stock_status( 'instock' );
		}
		if ( array_key_exists( 'cats', $data ) ) {
			$product->set_category_ids( array_map( 'intval', (array) $data['cats'] ) );
		}
		if ( array_key_exists( 'tags', $data ) ) {
			$product->set_tag_ids( array_map( 'intval', (array) $data['tags'] ) );
		}

		$product_id = $product->save();
		if ( $product_id <= 0 ) {
			WP_CLI::warning( 'Failed to save product: ' . $data['name'] );
			return 0;
		}

		if ( ! empty( $data['qty_breaks'] ) && is_array( $data['qty_breaks'] ) ) {
			update_post_meta( $product_id, '_temp_app_qty_breaks', $data['qty_breaks'] );
		} else {
			delete_post_meta( $product_id, '_temp_app_qty_breaks' );
		}

		if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {
			foreach ( $data['meta'] as $key => $value ) {
				update_post_meta( $product_id, (string) $key, $value );
			}
		}

		return $product_id;
	}

	/**
	 * Create WooCommerce pages if missing and set options.
	 */
	public static function ensure_woocommerce_pages(): void {
		if ( ! function_exists( 'wc_create_pages' ) && ! function_exists( 'WC' ) ) {
			return;
		}

		$pages = array(
			'shop'      => array(
				'title'   => 'Shop',
				'content' => '',
				'option'  => 'woocommerce_shop_page_id',
			),
			'cart'      => array(
				'title'   => 'Cart',
				'content' => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
				'option'  => 'woocommerce_cart_page_id',
			),
			'checkout'  => array(
				'title'   => 'Checkout',
				'content' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
				'option'  => 'woocommerce_checkout_page_id',
			),
			'my-account'=> array(
				'title'   => 'My account',
				'content' => '<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->',
				'option'  => 'woocommerce_myaccount_page_id',
			),
		);

		foreach ( $pages as $slug => $cfg ) {
			$existing_id = (int) get_option( $cfg['option'], 0 );
			if ( $existing_id > 0 && get_post( $existing_id ) ) {
				continue;
			}
			$page = get_page_by_path( $slug );
			if ( $page instanceof WP_Post ) {
				update_option( $cfg['option'], $page->ID );
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_title'   => $cfg['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => $cfg['content'],
				),
				true
			);
			if ( ! is_wp_error( $id ) ) {
				update_option( $cfg['option'], (int) $id );
			}
		}

		update_option( 'woocommerce_cart_redirect_after_add', 'no' );
		update_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' );
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );
		update_option( 'woocommerce_ship_to_countries', 'disabled' );
		// WooCommerce 9+ "coming soon" mode blocks the storefront for visitors.
		update_option( 'woocommerce_coming_soon', 'no' );
		update_option( 'woocommerce_store_pages_only', 'no' );

		// Prefer classic shortcode cart/checkout so package pricing is visible without Blocks quirks.
		foreach ( array( 'woocommerce_cart_page_id' => '[woocommerce_cart]', 'woocommerce_checkout_page_id' => '[woocommerce_checkout]' ) as $option => $shortcode ) {
			$page_id = (int) get_option( $option, 0 );
			if ( $page_id <= 0 ) {
				continue;
			}
			$content = (string) get_post_field( 'post_content', $page_id );
			if ( false !== strpos( $content, 'woocommerce/cart' ) || false !== strpos( $content, 'woocommerce/checkout' ) || '' === trim( $content ) ) {
				wp_update_post(
					array(
						'ID'           => $page_id,
						'post_content' => '<!-- wp:shortcode -->' . $shortcode . '<!-- /wp:shortcode -->',
					)
				);
			}
		}
	}

	public static function set_front_page( int $page_id ): void {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_id );
	}

	/**
	 * Apply a few Divi theme accents for the demo brand.
	 */
	public static function apply_divi_colors( string $accent, string $ink, string $nav_bg ): void {
		if ( ! function_exists( 'et_update_option' ) ) {
			return;
		}
		et_update_option( 'accent_color', $accent );
		et_update_option( 'header_color', $ink );
		et_update_option( 'link_color', $accent );
		et_update_option( 'primary_nav_bg', $nav_bg );
		et_update_option( 'menu_link', '#ffffff' );
		et_update_option( 'divi_fixed_nav', 'on' );
	}
}
