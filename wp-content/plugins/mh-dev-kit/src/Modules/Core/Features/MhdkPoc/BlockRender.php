<?php
/**
 * Block render helpers for dynamic blocks.
 *
 * @package MH\DevKit\Modules\Core\Features\MhdkPoc
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Core\Features\MhdkPoc;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\BlocksModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;

final class BlockRender {

	private static ?BlocksModel $blocks = null;

	private static function blocks(): BlocksModel {
		if ( null === self::$blocks ) {
			$settings   = new MhdkPocSettingsModel();
			$post_types = new Models\PostTypesModel();
			self::$blocks = new BlocksModel( $settings, $post_types );
		}

		return self::$blocks;
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 */
	public static function hospitality_menu( array $attributes ): string {
		$menu_id     = isset( $attributes['menuId'] ) ? (int) $attributes['menuId'] : 0;
		$show_prices = ! empty( $attributes['showPrices'] );
		$data        = self::blocks()->get_hospitality_menu_data( $menu_id, $show_prices );

		if ( null === $data ) {
			return ViewRenderer::render(
				'Modules/Core/Features/MhdkPoc/Views/blocks/placeholder.php',
				array(
					'message' => __( 'Select a Hospitality Menu in the block settings.', 'mh-dev-kit' ),
				)
			);
		}

		return ViewRenderer::render( 'Modules/Core/Features/MhdkPoc/Views/blocks/hospitality-menu.php', $data );
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 */
	public static function web_links( array $attributes ): string {
		$limit  = isset( $attributes['limit'] ) ? (int) $attributes['limit'] : 6;
		$layout = isset( $attributes['layout'] ) ? (string) $attributes['layout'] : 'list';
		$data   = self::blocks()->get_web_links_data( $limit, $layout );

		return ViewRenderer::render( 'Modules/Core/Features/MhdkPoc/Views/blocks/web-links.php', $data );
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 */
	public static function callout( array $attributes ): string {
		$title   = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
		$content = isset( $attributes['content'] ) ? (string) $attributes['content'] : '';
		$style   = isset( $attributes['style'] ) ? (string) $attributes['style'] : '';

		return ViewRenderer::render(
			'Modules/Core/Features/MhdkPoc/Views/blocks/callout.php',
			array(
				'title'   => $title,
				'content' => $content,
				'style'   => self::blocks()->resolve_callout_style( $style ),
			)
		);
	}
}
