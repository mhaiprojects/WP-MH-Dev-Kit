<?php
/**
 * SiteTools module — site-wide admin utilities and calculators.
 *
 * @package MH\DevKit\Modules\SiteTools
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\ModuleAdminContributorInterface;
use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;
use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\BackendRestrictOriginFeature;
use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models\RestrictOriginSettingsModel;
use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Presenters\RestrictOriginSettingsPresenter;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\MapDistanceFeature;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\Models\MapDistanceSettingsModel;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\Presenters\MapDistanceSettingsPresenter;

final class SiteToolsModule implements ModuleInterface, ModuleAdminContributorInterface {

	private RestrictOriginSettingsPresenter $restrict_origin_settings;

	private MapDistanceSettingsPresenter $map_distance_settings;

	public static function id(): string {
		return 'site-tools';
	}

	public static function name(): string {
		return 'Site Tools';
	}

	public static function description(): string {
		return __( 'Site-wide utilities: backend origin guard and map distance calculator.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			BackendRestrictOriginFeature::class,
			MapDistanceFeature::class,
		);
	}

	public function requirements(): ModuleRequirements {
		return ModuleRequirements::none();
	}

	public function register(): void {
		$this->restrict_origin_settings = new RestrictOriginSettingsPresenter( new RestrictOriginSettingsModel() );
		$this->map_distance_settings    = new MapDistanceSettingsPresenter( new MapDistanceSettingsModel() );

		$this->restrict_origin_settings->register();
		$this->map_distance_settings->register();

		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$restrict_origin_settings = $this->restrict_origin_settings;
		$map_distance_settings    = $this->map_distance_settings;

		$registrar->add_submenu(
			RestrictOriginSettingsModel::SETTINGS_SLUG,
			__( 'Restrict Origin', AdminMenu::TEXT_DOMAIN ),
			__( 'Backend Restrict Origin', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $restrict_origin_settings ): void {
				$restrict_origin_settings->render_settings();
			}
		);

		$registrar->add_submenu(
			MapDistanceSettingsModel::SETTINGS_SLUG,
			__( 'Map Distance', AdminMenu::TEXT_DOMAIN ),
			__( 'Map Distance Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $map_distance_settings ): void {
				$map_distance_settings->render_settings();
			}
		);
	}
}
