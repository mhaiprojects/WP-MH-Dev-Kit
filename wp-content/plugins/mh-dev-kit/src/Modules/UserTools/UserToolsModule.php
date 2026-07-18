<?php
/**
 * UserTools module — per-user QR, vCard, shortlinks, family tree.
 *
 * @package MH\DevKit\Modules\UserTools
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\ModuleAdminContributorInterface;
use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\FamilyTreeFeature;
use MH\DevKit\Modules\UserTools\Features\QrCode\QrCodeFeature;
use MH\DevKit\Modules\UserTools\Features\Shortlinks\ShortlinksFeature;
use MH\DevKit\Modules\UserTools\Features\VCard\VCardFeature;

final class UserToolsModule implements ModuleInterface, ModuleAdminContributorInterface {

	public const CONTENT_SLUG = 'mhdk-user-tools';

	public static function id(): string {
		return 'user-tools';
	}

	public static function name(): string {
		return 'User Tools';
	}

	public static function description(): string {
		return __( 'User-scoped tools: QR codes, vCards, Linktree-style shortlinks, and family tree builder.', 'mh-dev-kit' );
	}

	public static function features(): array {
		return array(
			QrCodeFeature::class,
			VCardFeature::class,
			ShortlinksFeature::class,
			FamilyTreeFeature::class,
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
			__( 'User Tools', AdminMenu::TEXT_DOMAIN ),
			__( 'User Tools', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'render_content_hub' ),
			'read'
		);
	}

	public function render_content_hub(): void {
		ViewRenderer::display(
			'Modules/UserTools/Views/content-hub.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}
}
