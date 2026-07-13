<?php
/**
 * Order Menu shortcode, AJAX, and cart handler.
 *
 * @package MH\DevKit\Features\OrderMenu\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Features\OrderMenu\Models\OrderMenuModel;
use MH\DevKit\Features\OrderMenu\Models\OrderMenuSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class OrderMenuPresenter {

	private const POC_PAGE_SLUG = 'bulk-order-menu';

	private const POC_OPTION    = 'mhdk_order_menu_poc_page_id';

	private const AJAX_ACTION   = 'mhdk_order_menu_add';

	public function __construct(
		private OrderMenuSettingsModel $settings = new OrderMenuSettingsModel()
	) {
		$this->model = new OrderMenuModel( $this->settings );
	}

	private OrderMenuModel $model;

	public function register(): void {
		add_shortcode( 'mhdk_order_menu', array( $this, 'render_shortcode' ) );
		add_action( 'init', array( $this, 'maybe_create_poc_page' ) );
		add_action( 'template_redirect', array( $this, 'handle_cart_submission' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_' . self::AJAX_ACTION, array( $this, 'ajax_add_to_cart' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'category'   => '',
				'ids'        => '',
				'slugs'      => '',
				'menu'       => '',
				'orderby'    => 'title',
				'order'      => 'ASC',
				'show_image' => '',
			),
			(array) $atts,
			'mhdk_order_menu'
		);

		$normalized = $this->model->normalize_atts( $atts );

		if ( '' === $normalized['category'] && '' === $normalized['menu'] && '' === $normalized['ids'] && '' === $normalized['slugs'] ) {
			$normalized['category'] = (string) $this->settings->get( 'default_category', '' );
		}

		$show_image = ( 'yes' === $normalized['show_image'] ) || ( '' === $normalized['show_image'] && $this->settings->get( 'show_image' ) );

		$products = $this->model->get_products( $normalized );

		if ( array() === $products ) {
			return '<p class="mhdk-order-menu-empty">' . esc_html__( 'No products available for this menu.', 'mh-dev-kit' ) . '</p>';
		}

		$reorder_qty = array();

		if ( isset( $_GET['mhdk_reorder'] ) && '1' === (string) $_GET['mhdk_reorder'] ) {
			$reorder_qty = $this->model->get_last_order_quantities();
		}

		$product_ids     = array_map( static fn( \WC_Product $p ): int => $p->get_id(), $products );
		$cart_quantities = $this->model->get_cart_quantities( $product_ids );
		$groups          = array();

		foreach ( $this->model->group_by_category( $products ) as $key => $group ) {
			$rows = array();

			foreach ( $group['rows'] as $product ) {
				$rows[] = $this->build_row( $product, $cart_quantities, $reorder_qty, $show_image );
			}

			$groups[] = array(
				'key'   => $key,
				'label' => $group['label'],
				'rows'  => $rows,
			);
		}

		return ViewRenderer::render(
			'OrderMenu/Views/order-menu.php',
			array(
				'groups'              => $groups,
				'atts'                => $normalized,
				'cart_url'            => wc_get_cart_url(),
				'form_action'         => $this->current_url(),
				'settings'            => $this->settings->get_all(),
				'show_unit'           => (bool) $this->settings->get( 'show_unit_column', true ),
				'show_stock'          => (bool) $this->settings->get( 'show_stock_badge', true ),
				'show_image'          => $show_image,
				'max_qty'             => $this->model->max_qty_per_line(),
				'ajax_enabled'        => $this->settings->ajax_enabled(),
				'preserve_qty'        => (bool) $this->settings->get( 'preserve_qty_after_add', true ),
				'show_reorder'        => (bool) $this->settings->get( 'enable_reorder_button', true ) && is_user_logged_in(),
				'reorder_url'         => add_query_arg( 'mhdk_reorder', '1', $this->current_url() ),
				'currency_symbol'     => get_woocommerce_currency_symbol(),
			)
		);
	}

	/**
	 * @param array<int, int> $cart_quantities Cart qty map.
	 * @param array<int, int> $reorder_qty     Reorder preset.
	 * @return array<string, mixed>
	 */
	private function build_row( \WC_Product $product, array $cart_quantities, array $reorder_qty, bool $show_image ): array {
		$id         = $product->get_id();
		$stock      = $this->model->get_stock_badge( $product );
		$initial    = $reorder_qty[ $id ] ?? 0;
		$image_html = '';

		if ( $show_image ) {
			$image_html = $product->get_image( array( 48, 48 ), array( 'class' => 'mhdk-order-menu__thumb' ) );
		}

		return array(
			'id'           => $id,
			'name'         => $product->get_name(),
			'description'  => $this->model->menu_description( $product ),
			'price_html'   => $product->get_price_html(),
			'price'        => (float) $product->get_price(),
			'unit'         => $this->model->parse_unit( $product ),
			'cart_qty'     => $cart_quantities[ $id ] ?? 0,
			'initial_qty'  => $initial,
			'stock'        => $stock,
			'image_html'   => $image_html,
			'purchasable'  => $stock['purchasable'] && $product->is_purchasable(),
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
		if ( $this->settings->ajax_enabled() ) {
			return;
		}

		$this->process_cart_submission( false );
	}

	public function ajax_add_to_cart(): void {
		if ( ! $this->settings->ajax_enabled() ) {
			wp_send_json_error( array( 'message' => __( 'AJAX is disabled.', 'mh-dev-kit' ) ), 400 );
		}

		check_ajax_referer( 'mhdk_order_menu_bulk_add', 'nonce' );

		$this->process_cart_submission( true );
	}

	private function process_cart_submission( bool $is_ajax ): void {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) && ! $is_ajax ) {
			return;
		}

		$is_bulk   = ! empty( $_POST['mhdk_order_menu_submit'] ) || ( ! empty( $_POST['bulk'] ) && '1' === (string) $_POST['bulk'] );
		$single_id = isset( $_POST['mhdk_order_menu_single'] ) ? absint( $_POST['mhdk_order_menu_single'] ) : 0;

		if ( ! $is_ajax && ! $is_bulk && $single_id <= 0 ) {
			return;
		}

		if ( $is_ajax && ! $is_bulk && $single_id <= 0 && empty( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'mh-dev-kit' ) ), 400 );
		}

		if ( ! $is_ajax ) {
			if (
				empty( $_POST['mhdk_order_menu_nonce'] )
				|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_order_menu_nonce'] ) ), 'mhdk_order_menu_bulk_add' )
			) {
				wp_die( esc_html__( 'Security check failed.', 'mh-dev-kit' ), esc_html__( 'Order Menu', 'mh-dev-kit' ), array( 'response' => 403 ) );
			}
		}

		if ( $is_ajax && $single_id <= 0 && ! empty( $_POST['product_id'] ) ) {
			$single_id = absint( $_POST['product_id'] );
		}

		$atts = $this->model->normalize_atts(
			array(
				'category' => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_category'] ?? '' ) ) ),
				'ids'      => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_ids'] ?? '' ) ) ),
				'slugs'    => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_slugs'] ?? '' ) ) ),
				'menu'     => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_menu'] ?? '' ) ) ),
				'orderby'  => sanitize_key( (string) ( $_POST['mhdk_menu_orderby'] ?? 'title' ) ),
				'order'    => sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_order'] ?? 'ASC' ) ) ),
			)
		);

		$raw_qty = isset( $_POST['mhdk_qty'] ) && is_array( $_POST['mhdk_qty'] )
			? wp_unslash( $_POST['mhdk_qty'] )
			: array();

		if ( $single_id > 0 ) {
			$qty_val = $raw_qty[ $single_id ] ?? ( $_POST['quantity'] ?? 0 );
			$raw_qty = array( $single_id => $qty_val );
		}

		$quantities = $this->model->sanitize_quantities( $raw_qty );
		$result     = $this->model->add_to_cart( $atts, $quantities );

		if ( $is_ajax ) {
			if ( $result['added'] > 0 ) {
				wp_send_json_success(
					array(
						'message'         => $single_id > 0
							? __( 'Item added to your cart.', 'mh-dev-kit' )
							: sprintf(
								_n( '%d item added to your cart.', '%d items added to your cart.', $result['added'], 'mh-dev-kit' ),
								$result['added']
							),
						'cart_quantities' => $result['cart_quantities'],
						'cart_count'      => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
						'clear_inputs'    => ! $this->settings->get( 'preserve_qty_after_add', true ),
					)
				);
			}

			wp_send_json_error(
				array(
					'message'  => $single_id > 0
						? __( 'Enter a quantity before adding this item.', 'mh-dev-kit' )
						: __( 'Enter a quantity for at least one product.', 'mh-dev-kit' ),
					'messages' => $result['messages'],
				),
				400
			);
		}

		$redirect = wp_get_referer() ?: home_url( '/' );

		if ( $result['added'] > 0 ) {
			wc_add_notice(
				$single_id > 0
					? __( 'Item added to your cart.', 'mh-dev-kit' )
					: sprintf(
						_n( '%d item added to your cart.', '%d items added to your cart.', $result['added'], 'mh-dev-kit' ),
						$result['added']
					),
				'success'
			);
		} else {
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
			if ( ! has_block( 'mh-dev-kit/order-menu', $post ) ) {
				return;
			}
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
			true
		);

		wp_localize_script(
			'mhdk-order-menu',
			'mhdkOrderMenu',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'mhdk_order_menu_bulk_add' ),
				'ajaxAction'   => self::AJAX_ACTION,
				'ajaxEnabled'  => $this->settings->ajax_enabled(),
				'preserveQty'  => (bool) $this->settings->get( 'preserve_qty_after_add', true ),
				'maxQty'       => $this->model->max_qty_per_line(),
				'currency'     => get_woocommerce_currency_symbol(),
				'i18n'         => array(
					'adding'  => __( 'Adding…', 'mh-dev-kit' ),
					'addItem' => __( 'Add Item', 'mh-dev-kit' ),
					'error'   => __( 'Could not add to cart.', 'mh-dev-kit' ),
				),
			)
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
