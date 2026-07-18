<?php
/**
 * Family Tree frontend shortcode with Vue.js app.
 *
 * @package MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;
use MH\DevKit\Shared\AssetRegistrar;

final class FamilyTreeFrontendPresenter {

	public function __construct(
		private FamilyTreeModel $model
	) {}

	public function register(): void {
		add_shortcode( 'mhdk_family_tree', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'id'       => '0',
				'editable' => '0',
			),
			(array) $atts,
			'mhdk_family_tree'
		);

		$post_id = (int) $atts['id'];
		$post    = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || FamilyTreeModel::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		$editable = '1' === $atts['editable'] && is_user_logged_in() && (int) $post->post_author === get_current_user_id();

		$this->enqueue_app_assets();

		return ViewRenderer::render(
			'Modules/UserTools/Features/FamilyTree/Views/frontend/app.php',
			array(
				'post_id'   => $post_id,
				'title'     => get_the_title( $post ),
				'tree_json' => wp_json_encode( $this->model->get_tree( $post_id ) ) ?: '{}',
				'editable'  => $editable,
				'rest_url'  => rest_url( 'mhdk/v1/family-tree/' . $post_id ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public function maybe_enqueue_assets(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( $post instanceof \WP_Post && has_shortcode( $post->post_content, 'mhdk_family_tree' ) ) {
			$this->enqueue_app_assets();
		}
	}

	private function enqueue_app_assets(): void {
		wp_enqueue_script(
			'mhdk-vue',
			'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js',
			array(),
			'3.4.21',
			true
		);

		AssetRegistrar::enqueue_script(
			'mhdk-family-tree',
			'Modules/UserTools/Features/FamilyTree',
			'js/family-tree-app.js',
			array( 'mhdk-vue' )
		);

		AssetRegistrar::enqueue_style(
			'mhdk-family-tree',
			'Modules/UserTools/Features/FamilyTree',
			'css/family-tree.css'
		);
	}
}
