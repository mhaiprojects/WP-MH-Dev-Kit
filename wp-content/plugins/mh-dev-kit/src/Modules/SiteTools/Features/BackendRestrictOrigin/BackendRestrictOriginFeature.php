<?php
/**
 * Backend Restrict Origin security feature.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models\RestrictOriginSettingsModel;
use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Presenters\RestrictOriginPresenter;
use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Presenters\RestrictOriginSettingsPresenter;

final class BackendRestrictOriginFeature implements FeatureInterface {

	public static function id(): string {
		return 'backend-restrict-origin';
	}

	public static function name(): string {
		return 'Backend Restrict Origin';
	}

	public function register(): void {
		$settings = new RestrictOriginSettingsModel();

		( new RestrictOriginSettingsPresenter( $settings ) )->register();
		( new RestrictOriginPresenter( $settings ) )->register();
	}
}
