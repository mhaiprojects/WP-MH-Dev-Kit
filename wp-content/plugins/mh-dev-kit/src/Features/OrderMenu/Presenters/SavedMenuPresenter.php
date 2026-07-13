<?php
/**
 * Registers saved menu CPT.
 *
 * @package MH\DevKit\Features\OrderMenu\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu\Presenters;

use MH\DevKit\Features\OrderMenu\Models\SavedMenuModel;

final class SavedMenuPresenter {

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . SavedMenuModel::POST_TYPE, array( $this, 'save_meta' ), 10, 2 );
	}

	public function register_post_type(): void {
		register_post_type(
			SavedMenuModel::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Saved Menus', 'mh-dev-kit' ),
					'singular_name' => __( 'Saved Menu', 'mh-dev-kit' ),
					'add_new_item'  => __( 'Add Saved Menu', 'mh-dev-kit' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'mhdk-content',
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'has_archive'         => false,
			)
		);
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'mhdk_saved_menu_products',
			__( 'Menu products', 'mh-dev-kit' ),
			array( $this, 'render_meta_box' ),
			SavedMenuModel::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( 'mhdk_saved_menu_save', 'mhdk_saved_menu_nonce' );
		$ids   = get_post_meta( $post->ID, SavedMenuModel::META_PRODUCT_IDS, true );
		$value = is_array( $ids ) ? implode( ',', array_map( 'absint', $ids ) ) : '';

		echo '<p><label for="mhdk_menu_product_ids">' . esc_html__( 'Product IDs (comma-separated, ordered)', 'mh-dev-kit' ) . '</label></p>';
		printf(
			'<input type="text" class="widefat" id="mhdk_menu_product_ids" name="mhdk_menu_product_ids" value="%s" />',
			esc_attr( $value )
		);
		printf(
			'<p class="description">%s</p>',
			esc_html__( 'Shortcode: [mhdk_order_menu menu="' . $post->post_name . '"]', 'mh-dev-kit' )
		);
	}

	public function save_meta( int $post_id, \WP_Post $post ): void {
		unset( $post );

		if (
			empty( $_POST['mhdk_saved_menu_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['mhdk_saved_menu_nonce'] ) ), 'mhdk_saved_menu_save' )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw = sanitize_text_field( wp_unslash( (string) ( $_POST['mhdk_menu_product_ids'] ?? '' ) ) );
		$ids = array_values(
			array_filter(
				array_map( 'absint', preg_split( '/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY ) ?: array() ),
				static fn( int $id ): bool => $id > 0
			)
		);

		update_post_meta( $post_id, SavedMenuModel::META_PRODUCT_IDS, $ids );
	}
}
