<?php
/**
 * ContentTools module — page authoring blocks, shortcodes, and local ad pages.
 *
 * @package MH\DevKit\Modules\ContentTools
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\ModuleAdminContributorInterface;
use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\ContentTools\Features\ChildrenPages\ChildrenPagesFeature;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\LocalAdPageFeature;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageSettingsModel;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPageSettingsPresenter;
use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\MarkdownEditorFeature;

final class ContentToolsModule implements ModuleInterface, ModuleAdminContributorInterface {

	private LocalAdPageSettingsPresenter $local_ad_settings;

	public const CONTENT_SLUG = 'mhdk-content-tools';

	public static function id(): string {
		return 'content-tools';
	}

	public static function name(): string {
		return 'Content Tools';
	}

	public static function description(): string {
		return __( 'Content authoring and landing pages: children lists, Markdown block, local ad pages.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			ChildrenPagesFeature::class,
			MarkdownEditorFeature::class,
			LocalAdPageFeature::class,
		);
	}

	public function requirements(): ModuleRequirements {
		return ModuleRequirements::none();
	}

	public function register(): void {
		$page_settings              = new LocalAdPageSettingsModel();
		$pages                      = new LocalAdPageModel( $page_settings );
		$this->local_ad_settings    = new LocalAdPageSettingsPresenter( $page_settings, $pages );
		$this->local_ad_settings->register();

		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$local_ad_settings = $this->local_ad_settings;

		$registrar->add_module_menu(
			self::id(),
			__( 'Content Tools', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_content_hub' ),
			'edit_posts'
		);

		$registrar->add_module_submenu(
			LocalAdPageSettingsModel::SETTINGS_SLUG,
			__( 'Local Ad Pages', AdminMenu::TEXT_DOMAIN ),
			__( 'Local Ad Page Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $local_ad_settings ): void {
				$local_ad_settings->render_settings();
			}
		);
	}

	public function render_content_hub(): void {
		ViewRenderer::display(
			'Modules/ContentTools/Views/content-hub.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}
}
