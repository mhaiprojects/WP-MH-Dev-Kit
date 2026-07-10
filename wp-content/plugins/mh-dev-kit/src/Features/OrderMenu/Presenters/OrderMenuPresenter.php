<?php
/**
 * Order Menu shortcode, assets, and bulk cart handler.
 *
 * @package MH\DevKit\Features\OrderMenu\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Features\OrderMenu\Models\OrderMenuModel;
use MH\DevKit\Shared\AssetRegistrar;

final class OrderMenuPresenter {

	private const POC_PAGE_SLUG = 'bulk-order-menu';

	private const POC_OPTION    = 'mhdk_order_menu_poc_page_id';

	private OrderMenuModel $model;

	public function __construct() {
		$this->model = new OrderMenuModel();
	}

	public function register(): void {
		add_shortcode( 'mhdk_order_menu', array( $this, 'render_shortcode' ) );
		add_action( 'init', array( $this, 'maybe_create_poc_page' ) );
		add_action( 'template_redirect', array( $this, 'handle_cart_submission' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'category' => '',
				'ids'      => '',
				'slugs'    => '',
			),
			(array) $atts,
			'mhdk_order_menu'
		);

		$normalized = $this->model->normalize_atts( $atts );
		$products   = $this->model->get_products( $normalized );

		if ( array() === $products ) {
			return '<p class="mhdk-order-menu-empty">' . esc_html__( 'No products available for this menu.', 'mh-dev-kit' ) . '</p>';
		}

		$rows = array();
		$ids  = array();

		foreach ( $products as $product ) {
			$ids[] = $product->get_id();
		}

		$cart_quantities = $this->model->get_cart_quantities( $ids );

		foreach ( $products as $product ) {
			$product_id = $product->get_id();
			$rows[]     = array(
				'id'          => $product_id,
				'name'        => $product->get_name(),
				'description' => $this->model->menu_description( $product ),
				'price_html'  => $product->get_price_html(),
				'price'       => $product->get_price(),
				'cart_qty'    => $cart_quantities[ $product_id ] ?? 0,
			);
		}

		return ViewRenderer::render(
			'OrderMenu/Views/order-menu.php',
			array(
				'rows'       => $rows,
				'atts'       => $normalized,
				'cart_url'   => wc_get_cart_url(),
				'form_action'=> $this->current_url(),
			)
		);
	}

	public function maybe_create_poc_page(): void {
		if ( get_option( self::POC_OPTION ) ) {
			return;
		}

		$existing = get_page_by_path( self::POC_PAGE_SLUG );

		if ( $existing instanceof \WP_Post ) {
			update_option( self::POC_OPTION, $existing->ID, false );
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Bulk Order Menu', 'mh-dev-kit' ),
				'post_name'    => self::POC_PAGE_SLUG,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => implode(
					"\n\n",
					array(
						'<p>' . esc_html__( 'Order multiple Shepparton local produce lines from one menu table. Enter quantities and add everything to your cart, then checkout with cash on delivery.', 'mh-dev-kit' ) . '</p>',
						'[mhdk_order_menu category="shepparton-local-produce"]',
					)
				),
			),
			true
		);

		if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
			update_option( self::POC_OPTION, $page_id, false );
		}
	}

	public function handle_cart_submission(): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		$is_bulk   = ! empty( $_POST['mhdk_order_menu_submit'] );
		$single_id = isset( $_POST['mhdk_order_menu_single'] ) ? absint( $_POST['mhdk_order_menu_single'] ) : 0;

		if ( ! $is_bulk && $single_id <= 0 ) {
			return;
		}

		if (
			empty( $_POST['mhdk_order_menu_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_order_menu_nonce'] ) ), 'mhdk_order_menu_bulk_add' )
		) {
			wp_die( esc_html__( 'Security check failed.', 'mh-dev-kit' ), esc_html__( 'Order Menu', 'mh-dev-kit' ), array( 'response' => 403 ) );
		}

		$atts = $this->model->normalize_atts(
			array(
				'category' => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_category'] ?? '' ) ) ),
				'ids'      => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_ids'] ?? '' ) ) ),
				'slugs'    => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_slugs'] ?? '' ) ) ),
			)
		);

		$raw_qty = isset( $_POST['mhdk_qty'] ) && is_array( $_POST['mhdk_qty'] )
			? wp_unslash( $_POST['mhdk_qty'] )
			: array();

		if ( $single_id > 0 ) {
			$raw_qty = array(
				$single_id => $raw_qty[ $single_id ] ?? 0,
			);
		}

		$quantities = $this->model->sanitize_quantities( $raw_qty );
		$result     = $this->model->add_to_cart( $atts, $quantities );

		$redirect = wp_get_referer() ?: home_url( '/' );

		if ( $result['added'] > 0 ) {
			if ( $single_id > 0 ) {
				wc_add_notice( __( 'Item added to your cart.', 'mh-dev-kit' ), 'success' );
			} else {
				wc_add_notice(
					sprintf(
						/* translators: %d: number of line items added */
						_n( '%d item added to your cart.', '%d items added to your cart.', $result['added'], 'mh-dev-kit' ),
						$result['added']
					),
					'success'
				);
			}
		} elseif ( 0 === $result['added'] ) {
			wc_add_notice(
				$single_id > 0
					? __( 'Enter a quantity before adding this item.', 'mh-dev-kit' )
					: __( 'Enter a quantity for at least one product.', 'mh-dev-kit' ),
				'notice'
			);
		}

		foreach ( $result['messages'] as $message ) {
			wc_add_notice( $message, 'error' );
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	public function enqueue_assets(): void {
		if ( ! is_singular() ) {
			return;
		}

		global $post;

		if ( ! $post instanceof \WP_Post || ! has_shortcode( $post->post_content, 'mhdk_order_menu' ) ) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-order-menu',
			'OrderMenu',
			'css/order-menu.css'
		);

		AssetRegistrar::enqueue_script(
			'mhdk-order-menu',
			'OrderMenu',
			'js/order-menu.js',
			array(),
			false
		);
	}

	private function current_url(): string {
		global $wp;

		if ( isset( $wp->request ) ) {
			return home_url( add_query_arg( array(), $wp->request ) );
		}

		return home_url( '/' );
	}
}
