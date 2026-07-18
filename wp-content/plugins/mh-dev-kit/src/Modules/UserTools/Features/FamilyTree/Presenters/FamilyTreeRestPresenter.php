<?php
/**
 * Family Tree REST API for saving tree data.
 *
 * @package MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters;

use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;
use MH\DevKit\Shared\UserScopedCptHelper;

final class FamilyTreeRestPresenter {

	public function __construct(
		private FamilyTreeModel $model
	) {}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'mhdk/v1',
			'/family-tree/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_tree' ),
				'permission_callback' => array( $this, 'can_save' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => static fn( $value ): bool => is_numeric( $value ) && (int) $value > 0,
					),
				),
			)
		);
	}

	public function can_save( \WP_REST_Request $request ): bool {
		if ( ! UserScopedCptHelper::can_manage_own() ) {
			return false;
		}

		$post_id = (int) $request->get_param( 'id' );

		return UserScopedCptHelper::current_user_owns_post( $post_id ) || current_user_can( 'manage_options' );
	}

	public function save_tree( \WP_REST_Request $request ): \WP_REST_Response {
		$post_id = (int) $request->get_param( 'id' );
		$body    = $request->get_json_params();
		$tree    = $body['tree'] ?? null;

		if ( ! is_array( $tree ) ) {
			return new \WP_REST_Response(
				array( 'message' => __( 'Invalid tree payload.', 'mh-dev-kit' ) ),
				400
			);
		}

		$json = wp_json_encode( $tree );

		if ( ! is_string( $json ) ) {
			return new \WP_REST_Response(
				array( 'message' => __( 'Could not encode tree.', 'mh-dev-kit' ) ),
				400
			);
		}

		update_post_meta( $post_id, FamilyTreeModel::META_TREE, $this->model->sanitize_tree( $json ) );

		return new \WP_REST_Response(
			array(
				'success' => true,
				'tree'    => $this->model->get_tree( $post_id ),
			),
			200
		);
	}
}
