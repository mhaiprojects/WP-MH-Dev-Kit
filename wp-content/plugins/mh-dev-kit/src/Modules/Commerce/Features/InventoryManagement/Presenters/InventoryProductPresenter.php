<?php
/**
 * Product edit screen stock-by-store meta box.
 *
 * @package MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Shared\AssetRegistrar;

final class InventoryProductPresenter {

	public function __construct(
		private InventoryModel $inventory,
		private InventorySettingsModel $settings
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_frontend_availability' ), 25 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	public function register_meta_box(): void {
		add_meta_box(
			'mhdk-inventory-by-store',
			__( 'Stock by store', 'mh-dev-kit' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_inventory_product', 'mhdk_inventory_product_nonce' );

		ViewRenderer::display(
			'Modules/Commerce/Features/InventoryManagement/Views/product-meta-box.php',
			array(
				'stores'       => ( new StoreLocatorModel() )->get_active(),
				'levels'       => $this->inventory->get_levels_for_product( (int) $post->ID ),
				'product_id'   => (int) $post->ID,
				'bulk_edit_url'=> admin_url( 'admin.php?page=' . InventorySettingsModel::SETTINGS_SLUG . '&product_id=' . (int) $post->ID ),
			)
		);
	}

	public function save_meta_box( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['mhdk_inventory_product_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_inventory_product_nonce'] ) ), 'mhdk_inventory_product' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$levels_raw = isset( $_POST['mhdk_store_stock'] ) && is_array( $_POST['mhdk_store_stock'] )
			? wp_unslash( $_POST['mhdk_store_stock'] )
			: array();

		$levels = array();

		foreach ( $levels_raw as $store_id => $qty ) {
			$levels[ (string) $store_id ] = (int) $qty;
		}

		$this->inventory->save_levels_for_product( $post_id, $levels );
	}

	public function enqueue_frontend_assets(): void {
		if ( ! function_exists( 'is_product' ) || ! is_product() || ! $this->settings->show_on_product() ) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-store-locator',
			'Modules/Commerce/Features/StoreLocator',
			'css/store-locator.css'
		);
	}

	public function render_frontend_availability(): void {
		if ( ! $this->settings->show_on_product() || ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$product_id    = (int) $product->get_id();
		$stores        = $this->inventory->get_stores_with_stock( $product_id );
		$has_any_stock = ! empty( $stores );

		ViewRenderer::display(
			'Modules/Commerce/Features/InventoryManagement/Views/product-availability.php',
			array(
				'stores'        => $has_any_stock ? $stores : array(),
				'threshold'     => $this->settings->low_stock_threshold(),
				'product_id'    => $product_id,
				'has_any_stock' => $has_any_stock,
			)
		);
	}
}
