<?php
/**
 * Gutenberg block registration.
 *
 * @package MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\BlocksModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\PostTypesModel;
use MH\DevKit\Shared\AssetRegistrar;

final class BlocksPresenter {

	private string $blocks_path;

	public function __construct(
		private BlocksModel $blocks,
		private MhdkPocSettingsModel $settings
	) {
		$this->blocks_path = MH_DEV_KIT_PATH . 'src/Modules/Core/Features/MhdkPoc/Assets/blocks/';
	}

	public function register(): void {
		add_action( 'init', array( $this, 'register_block_assets' ), 9 );
		add_action( 'init', array( $this, 'register_blocks' ), 10 );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		add_filter( 'block_categories', array( $this, 'register_block_category_legacy' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_frontend_assets' ) );
	}

	public function register_block_assets(): void {
		AssetRegistrar::register_script(
			'mhdk-poc-block-inspector',
			'Modules/Core/Features/MhdkPoc',
			'js/block-inspector.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-components',
				'wp-block-editor',
				'wp-i18n',
				'wp-hooks',
				'wp-server-side-render',
			)
		);

		AssetRegistrar::register_script(
			'mhdk-poc-blocks-editor',
			'Modules/Core/Features/MhdkPoc',
			'js/blocks-editor.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
				'wp-server-side-render',
				'mhdk-poc-block-inspector',
			)
		);
	}

	public function register_blocks(): void {
		$blocks = array( 'hospitality-menu', 'web-links', 'callout' );

		foreach ( $blocks as $block ) {
			$path = $this->blocks_path . $block;

			if ( ! is_dir( $path ) ) {
				continue;
			}

			register_block_type(
				$path,
				array(
					'editor_script' => 'mhdk-poc-blocks-editor',
				)
			);
		}
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	public function register_block_category( array $categories, \WP_Block_Editor_Context $context ): array {
		unset( $context );

		return $this->prepend_block_category( $categories );
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	public function register_block_category_legacy( array $categories, \WP_Post $post ): array {
		unset( $post );

		return $this->prepend_block_category( $categories );
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	private function prepend_block_category( array $categories ): array {
		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && AdminMenu::BLOCK_CATEGORY === $category['slug'] ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			array(
				'slug'  => AdminMenu::BLOCK_CATEGORY,
				'title' => __( 'MHDK', AdminMenu::TEXT_DOMAIN ),
				'icon'  => null,
			)
		);

		return $categories;
	}

	public function enqueue_editor_assets(): void {
		wp_enqueue_script( 'mhdk-poc-blocks-editor' );

		wp_localize_script(
			'mhdk-poc-block-inspector',
			'mhdkPoc',
			array(
				'hospitalityMenus' => $this->blocks->get_post_types()->get_hospitality_menus_for_select(),
			)
		);

		AssetRegistrar::enqueue_style(
			'mhdk-poc-frontend',
			'Modules/Core/Features/MhdkPoc',
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
			'mhdk-poc-frontend',
			'Modules/Core/Features/MhdkPoc',
			'css/foundation-frontend.css'
		);
	}

	public function get_blocks_model(): BlocksModel {
		return $this->blocks;
	}
}
