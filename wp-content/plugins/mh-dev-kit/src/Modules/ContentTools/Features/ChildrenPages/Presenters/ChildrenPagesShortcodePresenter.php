<?php
/**
 * Children Pages shortcode.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Presenters;

use MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Models\ChildrenPagesModel;
use MH\DevKit\Shared\AssetRegistrar;

final class ChildrenPagesShortcodePresenter {

	public function __construct(
		private ChildrenPagesModel $model
	) {}

	public function register(): void {
		add_shortcode( 'mhdk_children_pages', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		$parsed = shortcode_atts(
			array(
				'parent_id' => '0',
				'orderby'   => 'menu_order',
				'order'     => 'ASC',
				'limit'     => '50',
			),
			is_array( $atts ) ? $atts : array(),
			'mhdk_children_pages'
		);

		$this->enqueue_assets();

		$children = $this->model->get_children( $parsed );

		return $this->model->render_list( $children );
	}

	public function maybe_enqueue_assets(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'mhdk_children_pages' ) ) {
			$this->enqueue_assets();
		}
	}

	private function enqueue_assets(): void {
		AssetRegistrar::enqueue_style(
			'mhdk-children-pages',
			'Modules/ContentTools/Features/ChildrenPages',
			'css/children-pages.css'
		);
	}
}
