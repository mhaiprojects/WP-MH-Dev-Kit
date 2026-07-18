<?php
/**
 * Local Ad Page admin meta box.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters;

use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;

final class LocalAdPageAdminPresenter {

	public function __construct(
		private LocalAdPageModel $model
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . LocalAdPageModel::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'mhdk_local_ad_location',
			__( 'Location & Ad Content', 'mh-dev-kit' ),
			array( $this, 'render_meta_box' ),
			LocalAdPageModel::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_local_ad_save', 'mhdk_local_ad_nonce' );

		$location = $this->model->get_location( (int) $post->ID );
		$headline = (string) get_post_meta( $post->ID, LocalAdPageModel::META_HEADLINE, true );
		$body     = (string) get_post_meta( $post->ID, LocalAdPageModel::META_BODY, true );
		$url      = $this->model->get_public_url( (int) $post->ID );

		include MH_DEV_KIT_PATH . 'src/Modules/ContentTools/Features/LocalAdPage/Views/admin/meta-box.php';
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['mhdk_local_ad_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_local_ad_nonce'] ) ), 'mhdk_local_ad_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$location = $this->model->sanitize_location(
			array(
				'country'  => isset( $_POST['mhdk_local_country'] ) ? wp_unslash( (string) $_POST['mhdk_local_country'] ) : '',
				'state'    => isset( $_POST['mhdk_local_state'] ) ? wp_unslash( (string) $_POST['mhdk_local_state'] ) : '',
				'suburb'   => isset( $_POST['mhdk_local_suburb'] ) ? wp_unslash( (string) $_POST['mhdk_local_suburb'] ) : '',
				'postcode' => isset( $_POST['mhdk_local_postcode'] ) ? wp_unslash( (string) $_POST['mhdk_local_postcode'] ) : '',
			)
		);

		update_post_meta( $post_id, LocalAdPageModel::META_COUNTRY, $location['country'] );
		update_post_meta( $post_id, LocalAdPageModel::META_STATE, $location['state'] );
		update_post_meta( $post_id, LocalAdPageModel::META_SUBURB, $location['suburb'] );
		update_post_meta( $post_id, LocalAdPageModel::META_POSTCODE, $location['postcode'] );
		update_post_meta( $post_id, LocalAdPageModel::META_HEADLINE, sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_local_headline'] ?? '' ) ) ) );
		update_post_meta( $post_id, LocalAdPageModel::META_BODY, wp_kses_post( wp_unslash( (string) ( $_POST['mhdk_local_body'] ?? '' ) ) ) );
	}
}
