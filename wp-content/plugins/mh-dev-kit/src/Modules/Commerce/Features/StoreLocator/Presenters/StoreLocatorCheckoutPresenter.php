<?php
/**
 * WooCommerce checkout store selection with distance display.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters;

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class StoreLocatorCheckoutPresenter {

	public const CHECKOUT_FIELD = 'mhdk_pickup_store_id';

	public const ORDER_META_STORE = '_mhdk_pickup_store_id';

	public const ORDER_META_DISTANCE = '_mhdk_pickup_store_distance_m';

	public function __construct(
		private StoreLocatorModel $stores,
		private StoreLocatorSettingsModel $settings,
		private ?InventoryModel $inventory = null
	) {}

	public function register(): void {
		if ( ! $this->settings->checkout_enabled() ) {
			return;
		}

		add_action( 'woocommerce_after_order_notes', array( $this, 'render_checkout_field' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout_field' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_order_meta' ), 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'render_admin_order_meta' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_assets' ) );
	}

	public function render_checkout_field( \WC_Checkout $checkout ): void {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}

		$options = array( '' => __( 'Select a pickup store…', 'mh-dev-kit' ) );
		$stock   = array();

		foreach ( $this->stores->get_active() as $store ) {
			$store_id = (string) $store['id'];
			$label    = sprintf(
				'%s — %s',
				(string) $store['name'],
				$this->stores->format_address( $store )
			);

			if ( null !== $this->inventory && function_exists( 'WC' ) && WC()->cart ) {
				$cart_stock = $this->inventory->get_minimum_cart_stock_at_store( WC()->cart, $store_id );
				$stock[ $store_id ] = $cart_stock;

				if ( $cart_stock['available'] ) {
					$label .= sprintf(
						' (%s)',
						sprintf(
							/* translators: %d: minimum stock across cart items */
							_n( '%d in stock', '%d in stock', $cart_stock['minimum'], 'mh-dev-kit' ),
							$cart_stock['minimum']
						)
					);
				} else {
					$label .= ' (' . __( 'Out of stock', 'mh-dev-kit' ) . ')';
				}
			}

			$options[ $store_id ] = $label;
		}

		echo '<div class="mhdk-checkout-store-locator" data-nearby-url="' . esc_url( rest_url( 'mhdk/v1/stores/nearby' ) ) . '">';
		echo '<h3>' . esc_html__( 'Pickup store', 'mh-dev-kit' ) . '</h3>';

		woocommerce_form_field(
			self::CHECKOUT_FIELD,
			array(
				'type'     => 'select',
				'class'    => array( 'form-row-wide' ),
				'label'    => __( 'Store', 'mh-dev-kit' ),
				'options'  => $options,
				'required' => false,
			),
			$checkout->get_value( self::CHECKOUT_FIELD )
		);

		echo '<p class="mhdk-checkout-distance" aria-live="polite"></p>';
		echo '<input type="hidden" name="mhdk_pickup_distance_m" value="" />';
		echo '</div>';
	}

	public function validate_checkout_field(): void {
		$store_id = isset( $_POST[ self::CHECKOUT_FIELD ] )
			? sanitize_key( wp_unslash( (string) $_POST[ self::CHECKOUT_FIELD ] ) )
			: '';

		if ( '' === $store_id ) {
			return;
		}

		if ( null === $this->stores->get_by_id( $store_id ) ) {
			wc_add_notice( __( 'Please select a valid pickup store.', 'mh-dev-kit' ), 'error' );
			return;
		}

		if ( null === $this->inventory || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$issues = $this->inventory->get_cart_stock_issues( WC()->cart, $store_id );

		foreach ( $issues as $issue ) {
			wc_add_notice(
				sprintf(
					/* translators: 1: product name, 2: available quantity, 3: requested quantity */
					__( '%1$s has only %2$d units at the selected store (you requested %3$d).', 'mh-dev-kit' ),
					(string) $issue['name'],
					(int) $issue['available'],
					(int) $issue['requested']
				),
				'error'
			);
		}
	}

	public function save_order_meta( \WC_Order $order, array $data ): void {
		unset( $data );

		$store_id = isset( $_POST[ self::CHECKOUT_FIELD ] )
			? sanitize_key( wp_unslash( (string) $_POST[ self::CHECKOUT_FIELD ] ) )
			: '';

		if ( '' === $store_id ) {
			return;
		}

		$order->update_meta_data( self::ORDER_META_STORE, $store_id );

		$distance = isset( $_POST['mhdk_pickup_distance_m'] )
			? max( 0, (int) $_POST['mhdk_pickup_distance_m'] )
			: 0;

		if ( $distance > 0 ) {
			$order->update_meta_data( self::ORDER_META_DISTANCE, $distance );
		}
	}

	public function render_admin_order_meta( \WC_Order $order ): void {
		$store_id = (string) $order->get_meta( self::ORDER_META_STORE );

		if ( '' === $store_id ) {
			return;
		}

		$store = $this->stores->get_by_id( $store_id );

		if ( null === $store ) {
			return;
		}

		$distance = (int) $order->get_meta( self::ORDER_META_DISTANCE );

		echo '<p><strong>' . esc_html__( 'Pickup store', 'mh-dev-kit' ) . ':</strong> ';
		echo esc_html( (string) $store['name'] );
		echo '<br /><span class="description">' . esc_html( $this->stores->format_address( $store ) ) . '</span>';

		if ( $distance > 0 ) {
			printf(
				'<br /><span class="description">%s</span>',
				esc_html(
					sprintf(
						/* translators: 1: distance km */
						__( 'Customer distance: %1$s km', 'mh-dev-kit' ),
						number_format_i18n( $distance / 1000, 1 )
					)
				)
			);
		}

		echo '</p>';
	}

	public function enqueue_checkout_assets(): void {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-store-locator',
			'Modules/Commerce/Features/StoreLocator',
			'css/store-locator.css'
		);

		AssetRegistrar::enqueue_script(
			'mhdk-store-locator-checkout',
			'Modules/Commerce/Features/StoreLocator',
			'js/store-locator-checkout.js',
			array( 'jquery' ),
			true
		);

		$store_stock = array();

		if ( null !== $this->inventory && function_exists( 'WC' ) && WC()->cart ) {
			foreach ( $this->stores->get_active() as $store ) {
				$store_id = (string) $store['id'];
				$store_stock[ $store_id ] = $this->inventory->get_minimum_cart_stock_at_store( WC()->cart, $store_id );
			}
		}

		wp_localize_script(
			'mhdk-store-locator-checkout',
			'mhdkStoreLocatorCheckout',
			array(
				'fieldId'    => self::CHECKOUT_FIELD,
				'restUrl'    => rest_url( 'mhdk/v1/stores/' ),
				'nearbyUrl'  => rest_url( 'mhdk/v1/stores/nearby' ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'storeStock' => $store_stock,
				'i18n'       => array(
					'waiting'     => __( 'Calculating distance to store…', 'mh-dev-kit' ),
					'error'       => __( 'Could not calculate distance.', 'mh-dev-kit' ),
					'result'      => __( 'About %1$s km from this store.', 'mh-dev-kit' ),
					'prompt'      => __( 'Allow location to show distance to the selected store.', 'mh-dev-kit' ),
					'outOfStock'  => __( 'Out of stock at this store', 'mh-dev-kit' ),
					'sorting'     => __( 'Sorting stores by distance…', 'mh-dev-kit' ),
				),
			)
		);
	}
}
