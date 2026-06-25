<?php
/**
 * MHDK-POC feature entry.
 *
 * @package MH\DevKit\Features\MhdkPoc
 */

declare(strict_types=1);

namespace MH\DevKit\Features\MhdkPoc;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\FeatureAdminContributorInterface;
use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Features\MhdkPoc\Models\BlocksModel;
use MH\DevKit\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Features\MhdkPoc\Models\PostTypesModel;
use MH\DevKit\Features\MhdkPoc\Presenters\AdminMetaPresenter;
use MH\DevKit\Features\MhdkPoc\Presenters\BlocksPresenter;
use MH\DevKit\Features\MhdkPoc\Presenters\FrontendPresenter;
use MH\DevKit\Features\MhdkPoc\Presenters\PocSettingsPresenter;
use MH\DevKit\Features\MhdkPoc\Presenters\PostTypesPresenter;

final class MhdkPocFeature implements FeatureInterface, FeatureAdminContributorInterface {

	private MhdkPocSettingsModel $settings;

	private PostTypesModel $post_types;

	private BlocksModel $blocks;

	public static function id(): string {
		return 'mhdk-poc';
	}

	public static function name(): string {
		return 'MHDK-POC';
	}

	public function register(): void {
		$this->settings   = new MhdkPocSettingsModel();
		$this->post_types = new PostTypesModel();
		$this->blocks     = new BlocksModel( $this->settings, $this->post_types );

		( new PocSettingsPresenter( $this->settings ) )->register();
		( new PostTypesPresenter( $this->post_types ) )->register();
		( new AdminMetaPresenter( $this->post_types ) )->register();
		( new BlocksPresenter( $this->blocks, $this->settings ) )->register();
		( new FrontendPresenter( $this->settings, $this->post_types ) )->register();
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$settings = $this->settings;

		$registrar->add_settings_submenu(
			MhdkPocSettingsModel::SETTINGS_SLUG,
			__( 'MHDK-POC', AdminMenu::TEXT_DOMAIN ),
			__( 'MHDK-POC Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $settings ): void {
				( new PocSettingsPresenter( $settings ) )->render_settings();
			}
		);
	}
}
