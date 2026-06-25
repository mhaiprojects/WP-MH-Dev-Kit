<?php
/**
 * Gutenberg block registration.
 *
 * @package MH\DevKit\Features\Foundation\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\Foundation\Presenters;

use MH\DevKit\Features\Foundation\Models\BlocksModel;
use MH\DevKit\Features\Foundation\Models\FoundationSettingsModel;
use MH\DevKit\Features\Foundation\Models\PostTypesModel;
use MH\DevKit\Shared\AssetRegistrar;

final class BlocksPresenter {

	private string $blocks_path;

	public function __construct(
		private BlocksModel $blocks,
		private FoundationSettingsModel $settings
	) {
		$this->blocks_path = MH_DEV_KIT_PATH . 'src/Features/Foundation/Assets/blocks/';
	}

	public function register(): void {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_frontend_assets' ) );
	}

	public function register_blocks(): void {
		$blocks = array( 'hospitality-menu', 'web-links', 'callout' );

		foreach ( $blocks as $block ) {
			$path = $this->blocks_path . $block;

			if ( is_dir( $path ) ) {
				register_block_type( $path );
			}
		}
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	public function register_block_category( array $categories, \WP_Block_Editor_Context $context ): array {
		unset( $context );

		array_unshift(
			$categories,
			array(
				'slug'  => 'mh-dev-kit',
				'title' => __( 'MH Dev Kit', 'mh-dev-kit' ),
				'icon'  => null,
			)
		);

		return $categories;
	}

	public function enqueue_editor_assets(): void {
		AssetRegistrar::enqueue_script(
			'mhdk-foundation-block-inspector',
			'Foundation',
			'js/block-inspector.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n', 'wp-hooks' )
		);

		wp_localize_script(
			'mhdk-foundation-block-inspector',
			'mhdkFoundation',
			array(
				'hospitalityMenus' => $this->blocks->get_post_types()->get_hospitality_menus_for_select(),
			)
		);

		AssetRegistrar::enqueue_style(
			'mhdk-foundation-frontend',
			'Foundation',
			'css/foundation-frontend.css'
		);
	}

	public function maybe_enqueue_frontend_assets(): void {
		if ( is_admin() || ! $this->settings->get( 'enable_frontend_css', true ) ) {
			return;
		}

		if (
			! is_singular( array( PostTypesModel::HOSPITALITY_MENU, PostTypesModel::WEB_LINK ) )
			&& ! is_post_type_archive( array( PostTypesModel::HOSPITALITY_MENU, PostTypesModel::WEB_LINK ) )
			&& ! has_block( 'mh-dev-kit/hospitality-menu' )
			&& ! has_block( 'mh-dev-kit/web-links' )
			&& ! has_block( 'mh-dev-kit/callout' )
		) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-foundation-frontend',
			'Foundation',
			'css/foundation-frontend.css'
		);
	}

	public function get_blocks_model(): BlocksModel {
		return $this->blocks;
	}
}
