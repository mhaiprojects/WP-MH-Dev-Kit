<?php
/**
 * Helpers for user-scoped custom post types.
 *
 * @package MH\DevKit\Shared
 */

declare(strict_types=1);

namespace MH\DevKit\Shared;

final class UserScopedCptHelper {

	/**
	 * @return array<string, mixed>
	 */
	public static function author_caps_args( string $singular, string $plural, string $menu_icon = 'dashicons-admin-users' ): array {
		return array(
			'labels'              => array(
				'name'          => $plural,
				'singular_name' => $singular,
				'add_new_item'  => sprintf(
					/* translators: %s: singular post type name */
					__( 'Add New %s', 'mh-dev-kit' ),
					$singular
				),
				'edit_item'     => sprintf(
					/* translators: %s: singular post type name */
					__( 'Edit %s', 'mh-dev-kit' ),
					$singular
				),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_icon'           => $menu_icon,
			'supports'            => array( 'title', 'author' ),
		);
	}

	public static function current_user_owns_post( int $post_id ): bool {
		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		return (int) $post->post_author === get_current_user_id();
	}

	public static function can_manage_own(): bool {
		return is_user_logged_in() && current_user_can( 'read' );
	}
}
