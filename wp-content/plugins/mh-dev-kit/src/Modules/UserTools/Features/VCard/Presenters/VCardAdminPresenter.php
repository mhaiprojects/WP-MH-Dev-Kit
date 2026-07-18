<?php
/**
 * vCard admin meta box.
 *
 * @package MH\DevKit\Modules\UserTools\Features\VCard\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\VCard\Presenters;

use MH\DevKit\Modules\UserTools\Features\VCard\Models\VCardModel;

final class VCardAdminPresenter {

	public function __construct(
		private VCardModel $model
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . VCardModel::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'mhdk_vcard_details',
			__( 'Contact Details', 'mh-dev-kit' ),
			array( $this, 'render_meta_box' ),
			VCardModel::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_vcard_save', 'mhdk_vcard_nonce' );

		$fields = $this->model->get_fields( (int) $post->ID );

		include MH_DEV_KIT_PATH . 'src/Modules/UserTools/Features/VCard/Views/admin/meta-box.php';
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['mhdk_vcard_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_vcard_nonce'] ) ), 'mhdk_vcard_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( VCardModel::FIELDS as $key => $meta_key ) {
			$raw = isset( $_POST[ 'mhdk_vcard_' . $key ] ) ? wp_unslash( (string) $_POST[ 'mhdk_vcard_' . $key ] ) : '';

			if ( 'email' === $key ) {
				$value = sanitize_email( $raw );
			} elseif ( 'url' === $key ) {
				$value = esc_url_raw( $raw );
			} else {
				$value = sanitize_text_field( $raw );
			}

			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
