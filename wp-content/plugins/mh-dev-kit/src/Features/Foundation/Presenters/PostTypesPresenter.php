<?php
/**
 * Registers custom post types and post meta.
 *
 * @package MH\DevKit\Features\Foundation\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\Foundation\Presenters;

use MH\DevKit\Features\Foundation\Models\PostTypesModel;

final class PostTypesPresenter {

	public function __construct(
		private PostTypesModel $post_types
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_post_types(): void {
		register_post_type(
			PostTypesModel::HOSPITALITY_MENU,
			$this->post_types->get_hospitality_menu_args()
		);

		register_post_type(
			PostTypesModel::WEB_LINK,
			$this->post_types->get_web_link_args()
		);
	}

	public function register_meta(): void {
		register_post_meta(
			PostTypesModel::HOSPITALITY_MENU,
			PostTypesModel::META_SUBTITLE,
			array(
				'single'       => true,
				'type'         => 'string',
				'show_in_rest' => true,
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);

		register_post_meta(
			PostTypesModel::HOSPITALITY_MENU,
			PostTypesModel::META_MENU_NOTE,
			array(
				'single'       => true,
				'type'         => 'string',
				'show_in_rest' => true,
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);

		register_post_meta(
			PostTypesModel::WEB_LINK,
			PostTypesModel::META_URL,
			array(
				'single'       => true,
				'type'         => 'string',
				'show_in_rest' => true,
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);

		register_post_meta(
			PostTypesModel::WEB_LINK,
			PostTypesModel::META_DESC,
			array(
				'single'       => true,
				'type'         => 'string',
				'show_in_rest' => true,
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);

		register_post_meta(
			PostTypesModel::WEB_LINK,
			PostTypesModel::META_NEW_TAB,
			array(
				'single'       => true,
				'type'         => 'boolean',
				'show_in_rest' => true,
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			)
		);
	}
}
