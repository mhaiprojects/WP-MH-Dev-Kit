<?php
/**
 * Integration module — third-party API middleware.
 *
 * @package MH\DevKit\Modules\Integration
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Integration;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\ModuleAdminContributorInterface;
use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\ApiMiddlewareFeature;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareModel;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareSettingsModel;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters\ApiMiddlewareAdminPresenter;

final class IntegrationModule implements ModuleInterface, ModuleAdminContributorInterface {

	private ApiMiddlewareAdminPresenter $api_admin;

	public static function id(): string {
		return 'integration';
	}

	public static function name(): string {
		return 'Integration';
	}

	public static function description(): string {
		return __( 'Third-party API middleware: auth, scheduled pulls, and manual triggers.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			ApiMiddlewareFeature::class,
		);
	}

	public function requirements(): ModuleRequirements {
		return ModuleRequirements::none();
	}

	public function register(): void {
		$this->api_admin = new ApiMiddlewareAdminPresenter( new ApiMiddlewareModel() );
		$this->api_admin->register();

		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$api_admin = $this->api_admin;

		$registrar->add_module_menu(
			self::id(),
			__( 'Integration', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_module_hub' )
		);

		$registrar->add_module_submenu(
			ApiMiddlewareSettingsModel::SETTINGS_SLUG,
			__( 'API Middleware', AdminMenu::TEXT_DOMAIN ),
			__( 'API Middleware', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $api_admin ): void {
				$api_admin->render_page();
			},
			'manage_options'
		);
	}

	public function render_module_hub(): void {
		$model = new ApiMiddlewareModel();

		ViewRenderer::display(
			'Modules/Integration/Views/module-hub.php',
			array(
				'version'          => MH_DEV_KIT_VERSION,
				'connection_count' => count( $model->get_connections() ),
			)
		);
	}
}
