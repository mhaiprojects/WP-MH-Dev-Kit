<?php
/**
 * Inventory admin settings and bulk overview.
 *
 * @package MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class InventoryAdminPresenter {

	public function __construct(
		private InventoryModel $inventory,
		private InventorySettingsModel $settings,
		private StoreLocatorModel $stores
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_mhdk_save_inventory', array( $this, 'handle_save_inventory' ) );
		add_action( 'admin_post_mhdk_save_inventory_matrix', array( $this, 'handle_save_matrix' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_inventory',
			InventorySettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_inventory_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Track stock per store and show availability linked to the Store Locator.', 'mh-dev-kit' ) . '</p>';
			},
			InventorySettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'show_store_availability_on_product',
			__( 'Product page availability', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_show_on_product' ),
			InventorySettingsModel::SETTINGS_SLUG,
			'mhdk_inventory_main'
		);

		add_settings_field(
			'low_stock_threshold',
			__( 'Low stock threshold', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_low_stock' ),
			InventorySettingsModel::SETTINGS_SLUG,
			'mhdk_inventory_main'
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, InventorySettingsModel::SETTINGS_SLUG ) ) {
			return;
		}

		if ( function_exists( 'wc_enqueue_js' ) ) {
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}

		AssetRegistrar::enqueue_script(
			'mhdk-inventory-admin',
			'Modules/Commerce/Features/InventoryManagement',
			'js/inventory-admin.js',
			array( 'jquery', 'wc-enhanced-select' ),
			true
		);
	}

	public function render_settings(): void {
		$products = function_exists( 'wc_get_products' )
			? wc_get_products(
				array(
					'limit'  => 50,
					'status' => 'publish',
					'orderby'=> 'title',
					'order'  => 'ASC',
				)
			)
			: array();

		$selected_product = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0;

		if ( $selected_product <= 0 && ! empty( $products ) ) {
			$selected_product = (int) $products[0]->get_id();
		}

		ViewRenderer::display(
			'Modules/Commerce/Features/InventoryManagement/Views/settings-page.php',
			array(
				'settings'         => $this->settings,
				'stores'           => $this->stores->get_active(),
				'products'         => $products,
				'inventory'        => $this->inventory,
				'selected_product' => $selected_product,
				'locator_url'      => admin_url( 'admin.php?page=' . StoreLocatorSettingsModel::SETTINGS_SLUG ),
			)
		);
	}

	public function handle_save_inventory(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'mh-dev-kit' ) );
		}

		check_admin_referer( 'mhdk_save_inventory' );

		$product_id = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
		$levels_raw = isset( $_POST['store_stock'] ) && is_array( $_POST['store_stock'] )
			? wp_unslash( $_POST['store_stock'] )
			: array();

		if ( $product_id <= 0 ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . InventorySettingsModel::SETTINGS_SLUG . '&error=1' ) );
			exit;
		}

		$levels = array();

		foreach ( $levels_raw as $store_id => $qty ) {
			$levels[ (string) $store_id ] = (int) $qty;
		}

		$this->inventory->save_levels_for_product( $product_id, $levels );

		wp_safe_redirect(
			admin_url(
				'admin.php?page=' . InventorySettingsModel::SETTINGS_SLUG
				. '&product_id=' . $product_id
				. '&updated=1'
			)
		);
		exit;
	}

	public function handle_save_matrix(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'mh-dev-kit' ) );
		}

		check_admin_referer( 'mhdk_save_inventory_matrix' );

		$matrix_raw = isset( $_POST['matrix'] ) && is_array( $_POST['matrix'] )
			? wp_unslash( $_POST['matrix'] )
			: array();

		foreach ( $matrix_raw as $product_id => $levels_raw ) {
			if ( ! is_array( $levels_raw ) ) {
				continue;
			}

			$levels = array();

			foreach ( $levels_raw as $store_id => $qty ) {
				$levels[ (string) $store_id ] = (int) $qty;
			}

			$this->inventory->save_levels_for_product( (int) $product_id, $levels );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . InventorySettingsModel::SETTINGS_SLUG . '&matrix_saved=1' ) );
		exit;
	}

	public function field_show_on_product(): void {
		SettingsFieldRenderer::checkbox(
			InventorySettingsModel::OPTION_KEY . '[show_store_availability_on_product]',
			$this->settings->show_on_product(),
			__( 'Show per-store stock list on single product pages.', 'mh-dev-kit' )
		);
	}

	public function field_low_stock(): void {
		SettingsFieldRenderer::number(
			InventorySettingsModel::OPTION_KEY . '[low_stock_threshold]',
			$this->settings->low_stock_threshold(),
			0,
			9999,
			__( 'Quantities at or below this value are flagged as low stock in admin and on the storefront.', 'mh-dev-kit' )
		);
	}
}
