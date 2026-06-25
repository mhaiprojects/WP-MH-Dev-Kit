<?php
/**
 * Admin meta boxes and list table columns.
 *
 * @package MH\DevKit\Features\MhdkPoc\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\MhdkPoc\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Features\MhdkPoc\Models\PostTypesModel;

final class AdminMetaPresenter {

	public function __construct(
		private PostTypesModel $post_types
	) {}

	public function register(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
		add_filter( 'manage_' . PostTypesModel::WEB_LINK . '_posts_columns', array( $this, 'web_link_columns' ) );
		add_action( 'manage_' . PostTypesModel::WEB_LINK . '_posts_custom_column', array( $this, 'web_link_column_content' ), 10, 2 );
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'mhdk-hospitality-menu-details',
			__( 'Menu Details', 'mh-dev-kit' ),
			array( $this, 'render_hospitality_meta_box' ),
			PostTypesModel::HOSPITALITY_MENU,
			'normal',
			'high'
		);

		add_meta_box(
			'mhdk-web-link-details',
			__( 'Link Details', 'mh-dev-kit' ),
			array( $this, 'render_web_link_meta_box' ),
			PostTypesModel::WEB_LINK,
			'normal',
			'high'
		);
	}

	public function render_hospitality_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_save_hospitality_menu', 'mhdk_hospitality_nonce' );

		ViewRenderer::display(
			'MhdkPoc/Views/admin/hospitality-menu-meta.php',
			array(
				'subtitle'  => get_post_meta( $post->ID, PostTypesModel::META_SUBTITLE, true ),
				'menu_note' => get_post_meta( $post->ID, PostTypesModel::META_MENU_NOTE, true ),
			)
		);
	}

	public function render_web_link_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_save_web_link', 'mhdk_web_link_nonce' );

		ViewRenderer::display(
			'MhdkPoc/Views/admin/web-link-meta.php',
			array(
				'url'         => get_post_meta( $post->ID, PostTypesModel::META_URL, true ),
				'description' => get_post_meta( $post->ID, PostTypesModel::META_DESC, true ),
				'new_tab'     => (bool) get_post_meta( $post->ID, PostTypesModel::META_NEW_TAB, true ),
			)
		);
	}

	public function save_meta( int $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		if ( PostTypesModel::HOSPITALITY_MENU === $post->post_type ) {
			$this->save_hospitality_meta( $post_id );
		}

		if ( PostTypesModel::WEB_LINK === $post->post_type ) {
			$this->save_web_link_meta( $post_id );
		}
	}

	private function save_hospitality_meta( int $post_id ): void {
		if ( ! isset( $_POST['mhdk_hospitality_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mhdk_hospitality_nonce'] ) ), 'mhdk_save_hospitality_menu' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$subtitle = isset( $_POST['mhdk_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['mhdk_subtitle'] ) ) : '';
		$note     = isset( $_POST['mhdk_menu_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mhdk_menu_note'] ) ) : '';

		update_post_meta( $post_id, PostTypesModel::META_SUBTITLE, $subtitle );
		update_post_meta( $post_id, PostTypesModel::META_MENU_NOTE, $note );
	}

	private function save_web_link_meta( int $post_id ): void {
		if ( ! isset( $_POST['mhdk_web_link_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mhdk_web_link_nonce'] ) ), 'mhdk_save_web_link' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$url  = isset( $_POST['mhdk_url'] ) ? esc_url_raw( wp_unslash( $_POST['mhdk_url'] ) ) : '';
		$desc = isset( $_POST['mhdk_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mhdk_description'] ) ) : '';
		$tab  = ! empty( $_POST['mhdk_open_new_tab'] );

		update_post_meta( $post_id, PostTypesModel::META_URL, $url );
		update_post_meta( $post_id, PostTypesModel::META_DESC, $desc );
		update_post_meta( $post_id, PostTypesModel::META_NEW_TAB, $tab );
	}

	/**
	 * @param array<string, string> $columns List table columns.
	 * @return array<string, string>
	 */
	public function web_link_columns( array $columns ): array {
		$new = array();

		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['mhdk_url'] = __( 'URL', 'mh-dev-kit' );
			}
		}

		return $new;
	}

	public function web_link_column_content( string $column, int $post_id ): void {
		if ( 'mhdk_url' !== $column ) {
			return;
		}

		$url = $this->post_types->get_web_link_url( $post_id );

		if ( '' === $url ) {
			echo '&mdash;';
			return;
		}

		printf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( $url ),
			esc_html( wp_parse_url( $url, PHP_URL_HOST ) ?: $url )
		);
	}
}
