<?php
/**
 * Family Tree admin meta box.
 *
 * @package MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters;

use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;

final class FamilyTreeAdminPresenter {

	public function __construct(
		private FamilyTreeModel $model
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . FamilyTreeModel::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'mhdk_family_tree_data',
			__( 'Tree Data (JSON)', 'mh-dev-kit' ),
			array( $this, 'render_meta_box' ),
			FamilyTreeModel::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_family_tree_save', 'mhdk_family_tree_nonce' );

		$tree_json = wp_json_encode( $this->model->get_tree( (int) $post->ID ), JSON_PRETTY_PRINT );

		include MH_DEV_KIT_PATH . 'src/Modules/UserTools/Features/FamilyTree/Views/admin/meta-box.php';
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['mhdk_family_tree_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_family_tree_nonce'] ) ), 'mhdk_family_tree_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw = isset( $_POST['mhdk_family_tree_json'] ) ? wp_unslash( (string) $_POST['mhdk_family_tree_json'] ) : '';

		update_post_meta( $post_id, FamilyTreeModel::META_TREE, $this->model->sanitize_tree( $raw ) );
	}
}
