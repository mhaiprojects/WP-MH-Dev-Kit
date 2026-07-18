<?php
/**
 * Query child pages for list display.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Models;

final class ChildrenPagesModel {

	/**
	 * @param array<string, mixed> $atts Parsed shortcode/block attributes.
	 * @return \WP_Post[]
	 */
	public function get_children( array $atts, ?int $context_post_id = null ): array {
		$parent_id = $this->resolve_parent_id( $atts, $context_post_id );

		if ( $parent_id <= 0 ) {
			return array();
		}

		$orderby = sanitize_key( (string) ( $atts['orderby'] ?? 'menu_order' ) );
		$order   = strtoupper( sanitize_key( (string) ( $atts['order'] ?? 'ASC' ) ) );

		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'ASC';
		}

		$allowed_orderby = array( 'menu_order', 'title', 'date', 'ID' );

		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'menu_order';
		}

		$query = new \WP_Query(
			array(
				'post_type'      => 'page',
				'post_parent'    => $parent_id,
				'posts_per_page' => max( 1, min( 100, (int) ( $atts['limit'] ?? 50 ) ) ),
				'orderby'        => $orderby,
				'order'          => $order,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			)
		);

		return $query->posts;
	}

	/**
	 * @param array<string, mixed> $atts Attributes.
	 */
	public function resolve_parent_id( array $atts, ?int $context_post_id = null ): int {
		if ( ! empty( $atts['parent_id'] ) ) {
			return max( 0, (int) $atts['parent_id'] );
		}

		if ( null !== $context_post_id && $context_post_id > 0 ) {
			return $context_post_id;
		}

		if ( is_singular( 'page' ) ) {
			return (int) get_queried_object_id();
		}

		return 0;
	}

	/**
	 * @param \WP_Post[] $posts Child pages.
	 */
	public function render_list( array $posts, string $list_class = 'mhdk-children-pages' ): string {
		if ( array() === $posts ) {
			return '';
		}

		$items = array();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$items[] = sprintf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( get_permalink( $post ) ),
				esc_html( get_the_title( $post ) )
			);
		}

		return sprintf(
			'<ul class="%1$s">%2$s</ul>',
			esc_attr( $list_class ),
			implode( '', $items )
		);
	}
}
