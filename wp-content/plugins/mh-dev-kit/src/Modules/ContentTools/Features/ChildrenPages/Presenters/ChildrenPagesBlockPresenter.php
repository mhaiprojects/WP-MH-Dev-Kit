<?php
/**
 * Children Pages block registration and assets.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Presenters;

use MH\DevKit\Shared\AssetRegistrar;
use MH\DevKit\Shared\BlockCategoryHelper;

final class ChildrenPagesBlockPresenter {

	private string $blocks_path;

	public function __construct() {
		$this->blocks_path = MH_DEV_KIT_PATH . 'src/Modules/ContentTools/Features/ChildrenPages/Assets/blocks/';
	}

	public function register(): void {
		BlockCategoryHelper::register();

		add_action( 'init', array( $this, 'register_block_assets' ), 9 );
		add_action( 'init', array( $this, 'register_block' ), 10 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_frontend_assets' ) );
	}

	public function register_block_assets(): void {
		AssetRegistrar::register_script(
			'mhdk-children-pages-editor',
			'Modules/ContentTools/Features/ChildrenPages',
			'js/blocks-editor.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
				'wp-server-side-render',
			)
		);
	}

	public function register_block(): void {
		$path = $this->blocks_path . 'children-pages';

		if ( ! is_dir( $path ) ) {
			return;
		}

		register_block_type(
			$path,
			array(
				'editor_script' => 'mhdk-children-pages-editor',
			)
		);
	}

	public function enqueue_editor_assets(): void {
		wp_enqueue_script( 'mhdk-children-pages-editor' );
	}

	public function maybe_enqueue_frontend_assets(): void {
		if ( is_admin() || ! has_block( 'mh-dev-kit/children-pages' ) ) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-children-pages',
			'Modules/ContentTools/Features/ChildrenPages',
			'css/children-pages.css'
		);
	}
}
