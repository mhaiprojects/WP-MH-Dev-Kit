<?php
/**
 * Core module — MHDK-POC reference only.
 *
 * @package MH\DevKit\Modules\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Core;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\ModuleAdminContributorInterface;
use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Core\Features\MhdkPoc\MhdkPocFeature;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters\PocSettingsPresenter;

final class CoreModule implements ModuleInterface, ModuleAdminContributorInterface {

	private MhdkPocSettingsModel $settings;

	private PocSettingsPresenter $poc_settings;

	public static function id(): string {
		return 'core';
	}

	public static function name(): string {
		return 'Core';
	}

	public static function description(): string {
		return __( 'MHDK-POC reference: settings patterns, CPTs, blocks, and content admin.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			MhdkPocFeature::class,
		);
	}

	public function requirements(): ModuleRequirements {
		return ModuleRequirements::none();
	}

	public function register(): void {
		$this->settings     = new MhdkPocSettingsModel();
		$this->poc_settings = new PocSettingsPresenter( $this->settings );

		$this->poc_settings->register();

		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$poc_settings = $this->poc_settings;

		$registrar->add_submenu(
			MhdkPocSettingsModel::SETTINGS_SLUG,
			__( 'MHDK-POC', AdminMenu::TEXT_DOMAIN ),
			__( 'MHDK-POC Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $poc_settings ): void {
				$poc_settings->render_settings();
			}
		);

		$registrar->add_submenu(
			AdminMenu::CORE_CONTENT_SLUG,
			__( 'Content', AdminMenu::TEXT_DOMAIN ),
			__( 'MHDK Content', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_content_hub' ),
			'edit_posts'
		);
	}

	public function render_content_hub(): void {
		ViewRenderer::display(
			'Modules/Core/Views/content-hub.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}
}
