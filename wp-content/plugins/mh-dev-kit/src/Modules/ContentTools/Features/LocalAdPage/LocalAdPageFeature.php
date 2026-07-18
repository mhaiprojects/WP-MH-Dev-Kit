<?php
/**
 * My Local Ad Page feature.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPageAdminPresenter;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPageFrontendPresenter;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPagePostTypePresenter;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPageRestPresenter;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPageSeedPresenter;

final class LocalAdPageFeature implements FeatureInterface {

	public static function id(): string {
		return 'local-ad-page';
	}

	public static function name(): string {
		return 'My Local Ad Page';
	}

	public function register(): void {
		$model = new LocalAdPageModel();

		( new LocalAdPagePostTypePresenter( $model ) )->register();
		( new LocalAdPageAdminPresenter( $model ) )->register();
		( new LocalAdPageFrontendPresenter( $model ) )->register();
		( new LocalAdPageRestPresenter( $model ) )->register();
		( new LocalAdPageSeedPresenter( $model ) )->register();
	}
}
