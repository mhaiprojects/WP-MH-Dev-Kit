<?php
/**
 * vCard generator feature.
 *
 * @package MH\DevKit\Modules\UserTools\Features\VCard
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\VCard;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\UserTools\Features\VCard\Models\VCardModel;
use MH\DevKit\Modules\UserTools\Features\VCard\Presenters\VCardAdminPresenter;
use MH\DevKit\Modules\UserTools\Features\VCard\Presenters\VCardDownloadPresenter;
use MH\DevKit\Modules\UserTools\Features\VCard\Presenters\VCardFrontendPresenter;
use MH\DevKit\Modules\UserTools\Features\VCard\Presenters\VCardPostTypePresenter;

final class VCardFeature implements FeatureInterface {

	public static function id(): string {
		return 'vcard';
	}

	public static function name(): string {
		return 'vCard Generator';
	}

	public function register(): void {
		$model = new VCardModel();

		( new VCardPostTypePresenter( $model ) )->register();
		( new VCardAdminPresenter( $model ) )->register();
		( new VCardDownloadPresenter( $model ) )->register();
		( new VCardFrontendPresenter( $model ) )->register();
	}
}
