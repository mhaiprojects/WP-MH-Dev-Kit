<?php
/**
 * Markdown Editor block registration and assets.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Presenters;

use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models\MarkdownModel;
use MH\DevKit\Shared\AssetRegistrar;
use MH\DevKit\Shared\BlockCategoryHelper;

final class MarkdownBlockPresenter {

	private string $blocks_path;

	public function __construct(
		private MarkdownModel $model
	) {
		$this->blocks_path = MH_DEV_KIT_PATH . 'src/Modules/ContentTools/Features/MarkdownEditor/Assets/blocks/';
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
			'mhdk-markdown-editor',
			'Modules/ContentTools/Features/MarkdownEditor',
			'js/blocks-editor.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
			)
		);
	}

	public function register_block(): void {
		$path = $this->blocks_path . 'markdown';

		if ( ! is_dir( $path ) ) {
			return;
		}

		register_block_type(
			$path,
			array(
				'editor_script' => 'mhdk-markdown-editor',
			)
		);
	}

	public function enqueue_editor_assets(): void {
		wp_enqueue_script( 'mhdk-markdown-editor' );

		wp_enqueue_script(
			'mhdk-marked',
			'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
			array(),
			'9.1.6',
			true
		);
	}

	public function maybe_enqueue_frontend_assets(): void {
		if ( is_admin() || ! has_block( 'mh-dev-kit/markdown' ) ) {
			return;
		}

		wp_enqueue_script(
			'mhdk-marked',
			'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
			array(),
			'9.1.6',
			true
		);

		AssetRegistrar::enqueue_script(
			'mhdk-markdown-frontend',
			'Modules/ContentTools/Features/MarkdownEditor',
			'js/markdown-frontend.js',
			array( 'mhdk-marked' )
		);

		AssetRegistrar::enqueue_style(
			'mhdk-markdown',
			'Modules/ContentTools/Features/MarkdownEditor',
			'css/markdown.css'
		);
	}
}
