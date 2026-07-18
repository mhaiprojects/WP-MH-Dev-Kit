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
use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\MarkdownEditorFeature;

final class ContentToolsModule implements ModuleInterface, ModuleAdminContributorInterface {

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
		foreach ( self::features() as $feature_class ) {
			( new $feature_class() )->register();
		}
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$registrar->add_submenu(
			self::CONTENT_SLUG,
			__( 'Content Tools', AdminMenu::TEXT_DOMAIN ),
			__( 'Content Tools', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_content_hub' ),
			'edit_posts'
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
