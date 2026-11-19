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
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_editor_assets' ) );
		add_filter( 'allowed_block_types_all', array( $this, 'ensure_block_allowed' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_frontend_assets' ) );

		if ( did_action( 'init' ) ) {
			$this->register_block_assets();
			$this->register_block();
		}
	}

	public function register_block_assets(): void {
		static $registered = false;

		if ( $registered ) {
			return;
		}

		$registered = true;

		wp_register_script(
			'mhdk-marked',
			'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
			array(),
			'9.1.6',
			true
		);

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
				'wp-dom-ready',
				'mhdk-marked',
			)
		);

		$style_path = MH_DEV_KIT_PATH . 'src/Modules/ContentTools/Features/MarkdownEditor/Assets/css/markdown.css';
		$style_url  = MH_DEV_KIT_URL . 'src/Modules/ContentTools/Features/MarkdownEditor/Assets/css/markdown.css';

		if ( is_readable( $style_path ) ) {
			wp_register_style(
				'mhdk-markdown-editor',
				$style_url,
				array(),
				(string) filemtime( $style_path )
			);
		}
	}

	public function register_block(): void {
		static $registered = false;

		if ( $registered ) {
			return;
		}

		$path = $this->blocks_path . 'markdown';

		if ( ! is_dir( $path ) ) {
			return;
		}

		$this->register_block_assets();

		register_block_type(
			$path,
			array(
				'editor_script' => 'mhdk-markdown-editor',
				'editor_style'  => 'mhdk-markdown-editor',
			)
		);

		$registered = true;
	}

	public function enqueue_editor_assets(): void {
		if ( is_admin() ) {
			if ( function_exists( 'wp_should_load_block_editor_scripts_and_styles' ) && ! wp_should_load_block_editor_scripts_and_styles() ) {
				return;
			}
		} elseif ( function_exists( 'wp_should_load_separate_core_block_assets' ) && ! wp_should_load_separate_core_block_assets() ) {
			return;
		}

		$this->register_block_assets();

		wp_enqueue_script( 'mhdk-marked' );
		wp_enqueue_script( 'mhdk-markdown-editor' );
		wp_enqueue_style( 'mhdk-markdown-editor' );
	}

	/**
	 * Keep the Markdown block available in every block editor context.
	 *
	 * @param bool|array<int, string>     $allowed_block_types Allowed blocks.
	 * @param \WP_Block_Editor_Context    $context             Editor context.
	 * @return bool|array<int, string>
	 */
	public function ensure_block_allowed( $allowed_block_types, \WP_Block_Editor_Context $context ) {
		unset( $context );

		if ( true === $allowed_block_types ) {
			return true;
		}

		if ( ! is_array( $allowed_block_types ) ) {
			return $allowed_block_types;
		}

		if ( ! in_array( MarkdownModel::BLOCK_NAME, $allowed_block_types, true ) ) {
			$allowed_block_types[] = MarkdownModel::BLOCK_NAME;
		}

		return $allowed_block_types;
	}

	public function maybe_enqueue_frontend_assets(): void {
		if ( is_admin() || ! has_block( MarkdownModel::BLOCK_NAME ) ) {
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
