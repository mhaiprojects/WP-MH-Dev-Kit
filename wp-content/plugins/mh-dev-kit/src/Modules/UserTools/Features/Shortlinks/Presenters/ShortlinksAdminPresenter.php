<?php
/**
 * Shortlinks admin meta box.
 *
 * @package MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters;

use MH\DevKit\Modules\UserTools\Features\Shortlinks\Models\ShortlinksModel;
use MH\DevKit\Shared\AssetRegistrar;

final class ShortlinksAdminPresenter {

	public function __construct(
		private ShortlinksModel $model
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . ShortlinksModel::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'mhdk_link_page_details',
			__( 'Link Page Settings', 'mh-dev-kit' ),
			array( $this, 'render_meta_box' ),
			ShortlinksModel::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_link_page_save', 'mhdk_link_page_nonce' );

		$slug  = $this->model->get_slug( (int) $post->ID );
		$bio   = $this->model->get_bio( (int) $post->ID );
		$links = $this->model->get_links( (int) $post->ID );

		if ( array() === $links ) {
			$links = array(
				array( 'label' => '', 'url' => '' ),
			);
		}

		include MH_DEV_KIT_PATH . 'src/Modules/UserTools/Features/Shortlinks/Views/admin/meta-box.php';
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if ( ! isset( $_POST['mhdk_link_page_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_link_page_nonce'] ) ), 'mhdk_link_page_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$slug = isset( $_POST['mhdk_link_slug'] ) ? sanitize_title( wp_unslash( (string) $_POST['mhdk_link_slug'] ) ) : '';
		$bio  = isset( $_POST['mhdk_link_bio'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['mhdk_link_bio'] ) ) : '';

		$raw_links = isset( $_POST['mhdk_link_items'] ) && is_array( $_POST['mhdk_link_items'] )
			? wp_unslash( $_POST['mhdk_link_items'] )
			: array();

		update_post_meta( $post_id, ShortlinksModel::META_SLUG, $slug );
		update_post_meta( $post_id, ShortlinksModel::META_BIO, $bio );
		update_post_meta( $post_id, ShortlinksModel::META_LINKS, $this->model->sanitize_links( $raw_links ) );
	}

	public function enqueue_admin_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || ShortlinksModel::POST_TYPE !== $screen->post_type ) {
			return;
		}

		AssetRegistrar::enqueue_script(
			'mhdk-shortlinks-admin',
			'Modules/UserTools/Features/Shortlinks',
			'js/shortlinks-admin.js',
			array(),
			true
		);
	}
}
