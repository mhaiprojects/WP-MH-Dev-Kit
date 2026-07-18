<?php
/**
 * Local Ad Page REST API for AI agents.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters;

use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;

final class LocalAdPageRestPresenter {

	public function __construct(
		private LocalAdPageModel $model
	) {}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'mhdk/v1',
			'/local-ads',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_ads' ),
					'permission_callback' => array( $this, 'can_read' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_ad' ),
					'permission_callback' => array( $this, 'can_write' ),
				),
			)
		);

		register_rest_route(
			'mhdk/v1',
			'/local-ads/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_ad' ),
					'permission_callback' => array( $this, 'can_read' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_ad' ),
					'permission_callback' => array( $this, 'can_write' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_ad' ),
					'permission_callback' => array( $this, 'can_write' ),
				),
			)
		);

		register_rest_route(
			'mhdk/v1',
			'/local-ads/(?P<id>\d+)/upload',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'upload_media' ),
				'permission_callback' => array( $this, 'can_write' ),
			)
		);
	}

	public function can_read(): bool {
		return current_user_can( 'edit_posts' );
	}

	public function can_write(): bool {
		return current_user_can( 'edit_posts' );
	}

	public function list_ads(): \WP_REST_Response {
		$query = new \WP_Query(
			array(
				'post_type'      => LocalAdPageModel::POST_TYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 100,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		$items = array();

		foreach ( $query->posts as $post ) {
			if ( $post instanceof \WP_Post ) {
				$items[] = $this->model->to_rest_item( $post );
			}
		}

		return new \WP_REST_Response( $items, 200 );
	}

	public function get_ad( \WP_REST_Request $request ): \WP_REST_Response {
		$post = get_post( (int) $request->get_param( 'id' ) );

		if ( ! $post instanceof \WP_Post || LocalAdPageModel::POST_TYPE !== $post->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Not found' ), 404 );
		}

		return new \WP_REST_Response( $this->model->to_rest_item( $post ), 200 );
	}

	public function create_ad( \WP_REST_Request $request ): \WP_REST_Response {
		$data = $request->get_json_params();

		$post_id = wp_insert_post(
			array(
				'post_type'    => LocalAdPageModel::POST_TYPE,
				'post_title'   => sanitize_text_field( (string) ( $data['title'] ?? __( 'Local Ad Page', 'mh-dev-kit' ) ) ),
				'post_content' => wp_kses_post( (string) ( $data['content'] ?? '' ) ),
				'post_status'  => sanitize_key( (string) ( $data['status'] ?? 'draft' ) ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new \WP_REST_Response( array( 'message' => $post_id->get_error_message() ), 400 );
		}

		$this->save_meta_from_request( (int) $post_id, $data );

		$post = get_post( (int) $post_id );

		return new \WP_REST_Response( $this->model->to_rest_item( $post ), 201 );
	}

	public function update_ad( \WP_REST_Request $request ): \WP_REST_Response {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || LocalAdPageModel::POST_TYPE !== $post->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Not found' ), 404 );
		}

		$data = $request->get_json_params();
		$update = array( 'ID' => $post_id );

		if ( isset( $data['title'] ) ) {
			$update['post_title'] = sanitize_text_field( (string) $data['title'] );
		}

		if ( isset( $data['content'] ) ) {
			$update['post_content'] = wp_kses_post( (string) $data['content'] );
		}

		if ( isset( $data['status'] ) ) {
			$update['post_status'] = sanitize_key( (string) $data['status'] );
		}

		wp_update_post( $update );
		$this->save_meta_from_request( $post_id, $data );

		return new \WP_REST_Response( $this->model->to_rest_item( get_post( $post_id ) ), 200 );
	}

	public function delete_ad( \WP_REST_Request $request ): \WP_REST_Response {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || LocalAdPageModel::POST_TYPE !== $post->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Not found' ), 404 );
		}

		wp_trash_post( $post_id );

		return new \WP_REST_Response( array( 'deleted' => true ), 200 );
	}

	public function upload_media( \WP_REST_Request $request ): \WP_REST_Response {
		$post_id = (int) $request->get_param( 'id' );
		$post    = get_post( $post_id );

		if ( ! $post instanceof \WP_Post || LocalAdPageModel::POST_TYPE !== $post->post_type ) {
			return new \WP_REST_Response( array( 'message' => 'Not found' ), 404 );
		}

		$files = $request->get_file_params();

		if ( empty( $files['file'] ) ) {
			return new \WP_REST_Response( array( 'message' => 'No file uploaded.' ), 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_handle_upload( 'file', $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return new \WP_REST_Response( array( 'message' => $attachment_id->get_error_message() ), 400 );
		}

		set_post_thumbnail( $post_id, $attachment_id );

		return new \WP_REST_Response(
			array(
				'attachment_id' => $attachment_id,
				'url'           => wp_get_attachment_url( $attachment_id ),
			),
			201
		);
	}

	/**
	 * @param array<string, mixed> $data Request data.
	 */
	private function save_meta_from_request( int $post_id, array $data ): void {
		if ( isset( $data['location'] ) && is_array( $data['location'] ) ) {
			$location = $this->model->sanitize_location( $data['location'] );

			update_post_meta( $post_id, LocalAdPageModel::META_COUNTRY, $location['country'] );
			update_post_meta( $post_id, LocalAdPageModel::META_STATE, $location['state'] );
			update_post_meta( $post_id, LocalAdPageModel::META_SUBURB, $location['suburb'] );
			update_post_meta( $post_id, LocalAdPageModel::META_POSTCODE, $location['postcode'] );
		}

		if ( isset( $data['headline'] ) ) {
			update_post_meta( $post_id, LocalAdPageModel::META_HEADLINE, sanitize_text_field( (string) $data['headline'] ) );
		}

		if ( isset( $data['body'] ) ) {
			update_post_meta( $post_id, LocalAdPageModel::META_BODY, wp_kses_post( (string) $data['body'] ) );
		}
	}
}
