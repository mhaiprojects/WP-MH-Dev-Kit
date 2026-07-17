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
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuSettingsModel;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\OrderMenuFeature;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters\OrderMenuAdminPresenter;

final class CommerceModule implements ModuleInterface, ModuleAdminContributorInterface {

	private OrderMenuAdminPresenter $admin;

	public static function id(): string {
		return 'commerce';
	}

	public static function name(): string {
		return 'Commerce';
	}

	public static function description(): string {
		return __( 'WooCommerce features including bulk Order Menu and saved menus.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			OrderMenuFeature::class,
		);
	}

	public function requirements(): ModuleRequirements {
		return ModuleRequirements::needs_plugins( array( 'woocommerce/woocommerce.php' ) );
	}

	public function register(): void {
		$this->admin = new OrderMenuAdminPresenter();
		$this->admin->register();

		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$admin = $this->admin;

		$registrar->add_submenu(
			OrderMenuSettingsModel::SETTINGS_SLUG,
			__( 'Order Menu', AdminMenu::TEXT_DOMAIN ),
			__( 'Order Menu Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $admin ): void {
				$admin->render_settings_page();
			}
		);

		$registrar->add_submenu(
			AdminMenu::COMMERCE_CONTENT_SLUG,
			__( 'Commerce Content', AdminMenu::TEXT_DOMAIN ),
			__( 'Commerce Content', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_content_hub' ),
			'edit_posts'
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
