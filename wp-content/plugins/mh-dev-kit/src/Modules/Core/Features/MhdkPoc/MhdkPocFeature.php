<?php
/**
 * MHDK-POC feature entry.
 *
 * @package MH\DevKit\Modules\Core\Features\MhdkPoc
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Core\Features\MhdkPoc;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\BlocksModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\PostTypesModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters\AdminMetaPresenter;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters\BlocksPresenter;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters\FrontendPresenter;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters\PostTypesPresenter;

final class MhdkPocFeature implements FeatureInterface {

	public static function id(): string {
		return 'mhdk-poc';
	}

	public static function name(): string {
		return 'MHDK-POC';
	}

	public function register(): void {
		$settings   = new MhdkPocSettingsModel();
		$post_types = new PostTypesModel();
		$blocks     = new BlocksModel( $settings, $post_types );

		( new PostTypesPresenter( $post_types ) )->register();
		( new AdminMetaPresenter( $post_types ) )->register();
		( new BlocksPresenter( $blocks, $settings ) )->register();
		( new FrontendPresenter( $settings, $post_types ) )->register();
	}
}
