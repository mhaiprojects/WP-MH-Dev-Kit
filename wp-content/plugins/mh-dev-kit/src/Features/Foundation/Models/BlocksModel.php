<?php
/**
 * Block queries and attribute helpers.
 *
 * @package MH\DevKit\Features\Foundation\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Features\Foundation\Models;

final class BlocksModel {

	public function __construct(
		private FoundationSettingsModel $settings,
		private PostTypesModel $post_types
	) {}

	public function get_settings(): FoundationSettingsModel {
		return $this->settings;
	}

	public function get_post_types(): PostTypesModel {
		return $this->post_types;
	}

	public function resolve_callout_style( string $style ): string {
		$allowed = array( 'info', 'warning', 'success' );
		$style   = sanitize_key( $style );

		if ( in_array( $style, $allowed, true ) ) {
			return $style;
		}

		return (string) $this->settings->get( 'default_callout_style', 'info' );
	}

	public function get_hospitality_menu_data( int $menu_id, bool $show_prices ): ?array {
		$post = $this->post_types->get_hospitality_menu( $menu_id );

		if ( null === $post ) {
			return null;
		}

		return array(
			'post'          => $post,
			'subtitle'      => (string) get_post_meta( $post->ID, PostTypesModel::META_SUBTITLE, true ),
			'menu_note'     => (string) get_post_meta( $post->ID, PostTypesModel::META_MENU_NOTE, true ),
			'currency'      => (string) $this->settings->get( 'menu_currency_symbol', '$' ),
			'show_prices'   => $show_prices,
			'thumbnail_url' => get_the_post_thumbnail_url( $post, 'medium' ),
		);
	}

	/**
	 * @return array{items: array<int, array<string, mixed>>, layout: string}
	 */
	public function get_web_links_data( int $limit, string $layout ): array {
		$default_new_tab = (bool) $this->settings->get( 'web_links_new_tab', true );
		$posts           = $this->post_types->get_web_links( $limit );
		$items           = array();

		foreach ( $posts as $post ) {
			$items[] = array(
				'id'          => $post->ID,
				'title'       => get_the_title( $post ),
				'url'         => $this->post_types->get_web_link_url( $post->ID ),
				'description' => $this->post_types->get_web_link_description( $post->ID ),
				'new_tab'     => $this->post_types->web_link_opens_new_tab( $post->ID, $default_new_tab ),
				'thumbnail'   => get_the_post_thumbnail_url( $post, 'thumbnail' ),
			);
		}

		return array(
			'items'  => $items,
			'layout' => in_array( $layout, array( 'list', 'grid' ), true ) ? $layout : 'list',
		);
	}
}
