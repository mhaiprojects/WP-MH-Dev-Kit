<?php
/**
 * Public templates and frontend assets.
 *
 * @package MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters;

use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\PostTypesModel;

final class FrontendPresenter {

	public function __construct(
		private MhdkPocSettingsModel $settings,
		private PostTypesModel $post_types
	) {}

	public function register(): void {
		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
	}

	public function template_include( string $template ): string {
		if ( is_singular( PostTypesModel::HOSPITALITY_MENU ) ) {
			$custom = MH_DEV_KIT_PATH . 'src/Modules/Core/Features/MhdkPoc/Views/templates/single-mhdk_hosp_menu.php';

			if ( is_readable( $custom ) ) {
				return $custom;
			}
		}

		if ( is_post_type_archive( PostTypesModel::HOSPITALITY_MENU ) ) {
			$custom = MH_DEV_KIT_PATH . 'src/Modules/Core/Features/MhdkPoc/Views/templates/archive-mhdk_hosp_menu.php';

			if ( is_readable( $custom ) ) {
				return $custom;
			}
		}

		if ( is_singular( PostTypesModel::WEB_LINK ) ) {
			$custom = MH_DEV_KIT_PATH . 'src/Modules/Core/Features/MhdkPoc/Views/templates/single-mhdk_web_link.php';

			if ( is_readable( $custom ) ) {
				return $custom;
			}
		}

		if ( is_post_type_archive( PostTypesModel::WEB_LINK ) ) {
			$custom = MH_DEV_KIT_PATH . 'src/Modules/Core/Features/MhdkPoc/Views/templates/archive-mhdk_web_link.php';

			if ( is_readable( $custom ) ) {
				return $custom;
			}
		}

		return $template;
	}

	/**
	 * @param array<int, string> $classes Body classes.
	 * @return array<int, string>
	 */
	public function body_class( array $classes ): array {
		if ( is_singular( PostTypesModel::HOSPITALITY_MENU ) ) {
			$classes[] = 'mhdk-hospitality-menu-single';
		}

		if ( is_singular( PostTypesModel::WEB_LINK ) ) {
			$classes[] = 'mhdk-web-link-single';
		}

		return $classes;
	}
}
