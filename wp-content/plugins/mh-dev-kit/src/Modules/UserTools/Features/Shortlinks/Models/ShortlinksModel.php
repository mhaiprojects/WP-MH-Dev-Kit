<?php
/**
 * Shortlinks data model.
 *
 * @package MH\DevKit\Modules\UserTools\Features\Shortlinks\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\Shortlinks\Models;

use MH\DevKit\Shared\UserScopedCptHelper;

final class ShortlinksModel {

	public const POST_TYPE = 'mhdk_link_page';

	public const META_SLUG = 'mhdk_link_slug';

	public const META_LINKS = 'mhdk_link_items';

	public const META_BIO = 'mhdk_link_bio';

	/**
	 * @return array<string, mixed>
	 */
	public function get_post_type_args(): array {
		$args = UserScopedCptHelper::author_caps_args(
			__( 'Link Page', 'mh-dev-kit' ),
			__( 'Link Pages', 'mh-dev-kit' ),
			'dashicons-admin-links'
		);

		$args['public']             = true;
		$args['publicly_queryable'] = false;

		return $args;
	}

	public function get_slug( int $post_id ): string {
		$slug = sanitize_title( (string) get_post_meta( $post_id, self::META_SLUG, true ) );

		if ( '' === $slug ) {
			$post = get_post( $post_id );
			$slug = $post instanceof \WP_Post ? sanitize_title( $post->post_name ) : '';
		}

		return $slug;
	}

	public function get_bio( int $post_id ): string {
		return (string) get_post_meta( $post_id, self::META_BIO, true );
	}

	/**
	 * @return array<int, array{label:string,url:string}>
	 */
	public function get_links( int $post_id ): array {
		$raw = get_post_meta( $post_id, self::META_LINKS, true );

		if ( ! is_array( $raw ) ) {
			return array();
		}

		$links = array();

		foreach ( $raw as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$label = sanitize_text_field( (string) ( $item['label'] ?? '' ) );
			$url   = esc_url_raw( (string) ( $item['url'] ?? '' ) );

			if ( '' === $label || '' === $url ) {
				continue;
			}

			$links[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}

		return $links;
	}

	/**
	 * @param array<int, array{label?:string,url?:string}> $items Raw link rows.
	 * @return array<int, array{label:string,url:string}>
	 */
	public function sanitize_links( array $items ): array {
		$links = array();

		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$label = sanitize_text_field( (string) ( $item['label'] ?? '' ) );
			$url   = esc_url_raw( (string) ( $item['url'] ?? '' ) );

			if ( '' === $label || '' === $url ) {
				continue;
			}

			$links[] = array(
				'label' => $label,
				'url'   => $url,
			);
		}

		return $links;
	}

	public function find_by_slug( string $slug ): ?\WP_Post {
		$slug = sanitize_title( $slug );

		if ( '' === $slug ) {
			return null;
		}

		$query = new \WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => self::META_SLUG,
						'value' => $slug,
					),
				),
				'no_found_rows'  => true,
			)
		);

		$post = $query->posts[0] ?? null;

		return $post instanceof \WP_Post ? $post : null;
	}

	public static function public_url( string $slug ): string {
		return home_url( 'l/' . sanitize_title( $slug ) . '/' );
	}
}
