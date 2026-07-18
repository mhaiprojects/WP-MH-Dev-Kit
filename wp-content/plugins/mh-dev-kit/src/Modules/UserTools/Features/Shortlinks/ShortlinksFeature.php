<?php
/**
 * Shortlinks (Linktree-style) feature.
 *
 * @package MH\DevKit\Modules\UserTools\Features\Shortlinks
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\Shortlinks;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\UserTools\Features\Shortlinks\Models\ShortlinksModel;
use MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters\ShortlinksAdminPresenter;
use MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters\ShortlinksFrontendPresenter;
use MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters\ShortlinksPostTypePresenter;

final class ShortlinksFeature implements FeatureInterface {

	public static function id(): string {
		return 'shortlinks';
	}

	public static function name(): string {
		return 'Shortlinks Manager';
	}

	public function register(): void {
		$model = new ShortlinksModel();

		( new ShortlinksPostTypePresenter( $model ) )->register();
		( new ShortlinksAdminPresenter( $model ) )->register();
		( new ShortlinksFrontendPresenter( $model ) )->register();
	}
}
