<?php
/**
 * CPT and meta key definitions.
 *
 * @package MH\DevKit\Features\MhdkPoc\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Features\MhdkPoc\Models;

final class PostTypesModel {

	public const HOSPITALITY_MENU = 'mhdk_hosp_menu';

	public const WEB_LINK = 'mhdk_web_link';

	public const META_SUBTITLE  = '_mhdk_subtitle';
	public const META_MENU_NOTE = '_mhdk_menu_note';
	public const META_URL       = '_mhdk_url';
	public const META_DESC      = '_mhdk_description';
	public const META_NEW_TAB   = '_mhdk_open_new_tab';

	/**
	 * @return array<string, mixed>
	 */
	public function get_hospitality_menu_args(): array {
		return array(
			'labels'              => array(
				'name'          => __( 'Hospitality Menus', 'mh-dev-kit' ),
				'singular_name' => __( 'Hospitality Menu', 'mh-dev-kit' ),
				'add_new_item'  => __( 'Add New Hospitality Menu', 'mh-dev-kit' ),
				'edit_item'     => __( 'Edit Hospitality Menu', 'mh-dev-kit' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions' ),
			'menu_icon'           => 'dashicons-food',
			'show_in_menu'        => MhdkPocSettingsModel::content_menu_slug(),
			'rewrite'             => array( 'slug' => 'hospitality-menu' ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_web_link_args(): array {
		return array(
			'labels'              => array(
				'name'          => __( 'Web Links', 'mh-dev-kit' ),
				'singular_name' => __( 'Web Link', 'mh-dev-kit' ),
				'add_new_item'  => __( 'Add New Web Link', 'mh-dev-kit' ),
				'edit_item'     => __( 'Edit Web Link', 'mh-dev-kit' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'thumbnail', 'revisions' ),
			'menu_icon'           => 'dashicons-admin-links',
			'show_in_menu'        => MhdkPocSettingsModel::content_menu_slug(),
			'rewrite'             => array( 'slug' => 'web-link' ),
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_hospitality_menus_for_select(): array {
		$query = new \WP_Query(
			array(
				'post_type'      => self::HOSPITALITY_MENU,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$items = array();

		foreach ( $query->posts as $post ) {
			if ( $post instanceof \WP_Post ) {
				$items[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
				);
			}
		}

		return $items;
	}

	public function get_hospitality_menu( int $menu_id ): ?\WP_Post {
		$post = get_post( $menu_id );

		if ( ! $post instanceof \WP_Post || self::HOSPITALITY_MENU !== $post->post_type ) {
			return null;
		}

		return $post;
	}

	/**
	 * @return array<int, \WP_Post>
	 */
	public function get_web_links( int $limit = 10 ): array {
		$query = new \WP_Query(
			array(
				'post_type'      => self::WEB_LINK,
				'post_status'    => 'publish',
				'posts_per_page' => max( 1, $limit ),
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		return array_values(
			array_filter(
				$query->posts,
				static fn( $post ): bool => $post instanceof \WP_Post
			)
		);
	}

	public function get_web_link_url( int $post_id ): string {
		return (string) get_post_meta( $post_id, self::META_URL, true );
	}

	public function get_web_link_description( int $post_id ): string {
		return (string) get_post_meta( $post_id, self::META_DESC, true );
	}

	public function web_link_opens_new_tab( int $post_id, bool $default ): bool {
		$value = get_post_meta( $post_id, self::META_NEW_TAB, true );

		if ( '' === $value ) {
			return $default;
		}

		return (bool) $value;
	}
}
