<?php
/**
 * Map distance calculator shortcode.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\MapDistance
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\MapDistance;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\Models\MapDistanceSettingsModel;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\Presenters\MapDistancePresenter;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\Presenters\MapDistanceSettingsPresenter;

final class MapDistanceFeature implements FeatureInterface {

	public static function id(): string {
		return 'map-distance';
	}

	public static function name(): string {
		return 'Map Distance Calculator';
	}

	public function register(): void {
		$settings = new MapDistanceSettingsModel();

		( new MapDistanceSettingsPresenter( $settings ) )->register();
		( new MapDistancePresenter( $settings ) )->register();
	}
}
