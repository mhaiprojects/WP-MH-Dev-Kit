<?php
/**
 * QR Code admin meta box and list columns.
 *
 * @package MH\DevKit\Modules\UserTools\Features\QrCode\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\QrCode\Presenters;

use MH\DevKit\Modules\UserTools\Features\QrCode\Models\QrCodeModel;

final class QrCodeAdminPresenter {

	public function __construct(
		private QrCodeModel $model
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . QrCodeModel::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
		add_filter( 'manage_' . QrCodeModel::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . QrCodeModel::POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'mhdk_qr_code_details',
			__( 'QR Code Details', 'mh-dev-kit' ),
			array( $this, 'render_meta_box' ),
			QrCodeModel::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_qr_code_save', 'mhdk_qr_code_nonce' );

		$target = $this->model->get_target( (int) $post->ID );
		$size   = $this->model->get_size( (int) $post->ID );

		include MH_DEV_KIT_PATH . 'src/Modules/UserTools/Features/QrCode/Views/admin/meta-box.php';
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		if ( ! isset( $_POST['mhdk_qr_code_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_qr_code_nonce'] ) ), 'mhdk_qr_code_save' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$target = isset( $_POST['mhdk_qr_target'] ) ? esc_url_raw( wp_unslash( (string) $_POST['mhdk_qr_target'] ) ) : '';
		$size   = isset( $_POST['mhdk_qr_size'] ) ? max( 128, min( 512, (int) $_POST['mhdk_qr_size'] ) ) : 256;

		update_post_meta( $post_id, QrCodeModel::META_TARGET, $target );
		update_post_meta( $post_id, QrCodeModel::META_SIZE, (string) $size );
	}

	/**
	 * @param string[] $columns List table columns.
	 * @return string[]
	 */
	public function columns( array $columns ): array {
		$columns['mhdk_qr_target'] = __( 'Target URL', 'mh-dev-kit' );

		return $columns;
	}

	public function column_content( string $column, int $post_id ): void {
		if ( 'mhdk_qr_target' !== $column ) {
			return;
		}

		echo esc_html( $this->model->get_target( $post_id ) );
	}
}
