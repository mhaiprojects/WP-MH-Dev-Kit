<?php
/**
 * Commerce module — WooCommerce features.
 *
 * @package MH\DevKit\Modules\Commerce
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\ModuleAdminContributorInterface;
use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\InventoryManagementFeature;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters\InventoryAdminPresenter;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuSettingsModel;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\OrderMenuFeature;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters\OrderMenuAdminPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters\StoreLocatorSettingsPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\StoreLocatorFeature;

final class CommerceModule implements ModuleInterface, ModuleAdminContributorInterface {

	private OrderMenuAdminPresenter $admin;

	private StoreLocatorSettingsPresenter $store_locator_settings;

	private InventoryAdminPresenter $inventory_admin;

	public static function id(): string {
		return 'commerce';
	}

	public static function name(): string {
		return 'Commerce';
	}

	public static function description(): string {
		return __( 'WooCommerce features: Order Menu, Store Locator, and Inventory Management.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			OrderMenuFeature::class,
			StoreLocatorFeature::class,
			InventoryManagementFeature::class,
		);
	}

	public function requirements(): ModuleRequirements {
		return ModuleRequirements::needs_plugins( array( 'woocommerce/woocommerce.php' ) );
	}

	public function register(): void {
		$this->admin                    = new OrderMenuAdminPresenter();
		$this->store_locator_settings   = new StoreLocatorSettingsPresenter(
			new StoreLocatorSettingsModel(),
			new StoreLocatorModel()
		);
		$this->inventory_admin          = new InventoryAdminPresenter(
			new InventoryModel(),
			new InventorySettingsModel()
		);

		$this->admin->register();
		$this->store_locator_settings->register();
		$this->inventory_admin->register();

		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$admin                  = $this->admin;
		$store_locator_settings = $this->store_locator_settings;
		$inventory_admin        = $this->inventory_admin;

		$registrar->add_module_menu(
			self::id(),
			__( 'Commerce', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_module_hub' )
		);

		$registrar->add_module_submenu(
			OrderMenuSettingsModel::SETTINGS_SLUG,
			__( 'Order Menu', AdminMenu::TEXT_DOMAIN ),
			__( 'Order Menu Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $admin ): void {
				$admin->render_settings_page();
			}
		);

		$registrar->add_module_submenu(
			StoreLocatorSettingsModel::SETTINGS_SLUG,
			__( 'Store Locator', AdminMenu::TEXT_DOMAIN ),
			__( 'Store Locator Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $store_locator_settings ): void {
				$store_locator_settings->render_settings();
			}
		);

		$registrar->add_module_submenu(
			InventorySettingsModel::SETTINGS_SLUG,
			__( 'Inventory', AdminMenu::TEXT_DOMAIN ),
			__( 'Inventory Management', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $inventory_admin ): void {
				$inventory_admin->render_settings();
			},
			'manage_woocommerce'
		);

		$registrar->add_module_submenu(
			AdminMenu::COMMERCE_CONTENT_SLUG,
			__( 'Commerce Content', AdminMenu::TEXT_DOMAIN ),
			__( 'Commerce Content', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_content_hub' ),
			'edit_posts'
		);
	}

	public function render_module_hub(): void {
		ViewRenderer::display(
			'Modules/Commerce/Views/module-hub.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}

	public function render_content_hub(): void {
		ViewRenderer::display(
			'Modules/Commerce/Views/content-hub.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}
}
