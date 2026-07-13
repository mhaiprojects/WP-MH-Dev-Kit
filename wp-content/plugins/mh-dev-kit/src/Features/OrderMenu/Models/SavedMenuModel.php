<?php
/**
 * Saved menu CPT helpers.
 *
 * @package MH\DevKit\Features\OrderMenu\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu\Models;

final class SavedMenuModel {

	public const POST_TYPE = 'mhdk_saved_menu';

	public const META_PRODUCT_IDS = '_mhdk_menu_product_ids';

	/**
	 * @return array<int, int>
	 */
	public function get_product_ids_for_menu( string $slug ): array {
		$post = get_page_by_path( sanitize_title( $slug ), OBJECT, self::POST_TYPE );

		if ( ! $post instanceof \WP_Post ) {
			return array();
		}

		$ids = get_post_meta( $post->ID, self::META_PRODUCT_IDS, true );

		if ( ! is_array( $ids ) ) {
			return array();
		}

		return array_values(
			array_filter(
				array_map( 'absint', $ids ),
				static fn( int $id ): bool => $id > 0
			)
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_all_menus(): array {
		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$menus = array();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$menus[] = array(
				'id'    => $post->ID,
				'title' => $post->post_title,
				'slug'  => $post->post_name,
			);
		}

		return $menus;
	}
}
