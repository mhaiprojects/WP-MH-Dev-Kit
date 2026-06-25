<?php
/**
 * Foundation feature entry.
 *
 * @package MH\DevKit\Features\Foundation
 */

declare(strict_types=1);

namespace MH\DevKit\Features\Foundation;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Features\Foundation\Models\BlocksModel;
use MH\DevKit\Features\Foundation\Models\FoundationSettingsModel;
use MH\DevKit\Features\Foundation\Models\PostTypesModel;
use MH\DevKit\Features\Foundation\Presenters\AdminMetaPresenter;
use MH\DevKit\Features\Foundation\Presenters\BlocksPresenter;
use MH\DevKit\Features\Foundation\Presenters\ContentMenuPresenter;
use MH\DevKit\Features\Foundation\Presenters\FrontendPresenter;
use MH\DevKit\Features\Foundation\Presenters\PostTypesPresenter;
use MH\DevKit\Features\Foundation\Presenters\SettingsPresenter;

final class FoundationFeature implements FeatureInterface {

	public static function id(): string {
		return 'foundation';
	}

	public static function name(): string {
		return 'Foundation';
	}

	public function register(): void {
		$settings = new FoundationSettingsModel();
		$post_types = new PostTypesModel();
		$blocks = new BlocksModel( $settings, $post_types );

		( new ContentMenuPresenter() )->register();
		( new SettingsPresenter( $settings ) )->register();
		( new PostTypesPresenter( $post_types ) )->register();
		( new AdminMetaPresenter( $post_types ) )->register();
		( new BlocksPresenter( $blocks, $settings ) )->register();
		( new FrontendPresenter( $settings, $post_types ) )->register();
	}
}
