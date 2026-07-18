<?php
/**
 * Children Pages list block and shortcode.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\ChildrenPages
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\ChildrenPages;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Models\ChildrenPagesModel;
use MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Presenters\ChildrenPagesBlockPresenter;
use MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Presenters\ChildrenPagesShortcodePresenter;

final class ChildrenPagesFeature implements FeatureInterface {

	public static function id(): string {
		return 'children-pages';
	}

	public static function name(): string {
		return 'Children Pages';
	}

	public function register(): void {
		$model = new ChildrenPagesModel();

		( new ChildrenPagesBlockPresenter() )->register();
		( new ChildrenPagesShortcodePresenter( $model ) )->register();
	}
}
