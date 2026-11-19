<?php
/**
 * Local Ad Page data model.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models;

final class LocalAdPageModel {

	public const POST_TYPE = 'mhdk_local_ad';

	public const META_COUNTRY  = 'mhdk_local_country';

	public const META_STATE    = 'mhdk_local_state';

	public const META_SUBURB   = 'mhdk_local_suburb';

	public const META_POSTCODE = 'mhdk_local_postcode';

	public const META_HEADLINE = 'mhdk_local_headline';

	public const META_BODY     = 'mhdk_local_body';

	public const SEED_OPTION = 'mhdk_local_ad_shepparton_seeded';

	public function __construct(
		private LocalAdPageSettingsModel $settings = new LocalAdPageSettingsModel()
	) {}

	/**
	 * @return array<string, mixed>
	 */
	public function get_post_type_args(): array {
		return array(
			'labels'              => array(
				'name'          => __( 'Local Ad Pages', 'mh-dev-kit' ),
				'singular_name' => __( 'Local Ad Page', 'mh-dev-kit' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon'           => 'dashicons-location-alt',
		);
	}

	/**
	 * @return array<string, string>
	 */
	public function get_location( int $post_id ): array {
		return array(
			'country'  => sanitize_title( (string) get_post_meta( $post_id, self::META_COUNTRY, true ) ),
			'state'    => sanitize_title( (string) get_post_meta( $post_id, self::META_STATE, true ) ),
			'suburb'   => sanitize_title( (string) get_post_meta( $post_id, self::META_SUBURB, true ) ),
			'postcode' => sanitize_text_field( (string) get_post_meta( $post_id, self::META_POSTCODE, true ) ),
		);
	}

	/**
	 * @param array<string, mixed> $location Location fields.
	 * @param string|null            $pattern  URL pattern override.
	 */
	public function build_path( array $location, ?string $pattern = null ): string {
		$country  = sanitize_title( (string) ( $location['country'] ?? '' ) );
		$state    = sanitize_title( (string) ( $location['state'] ?? '' ) );
		$suburb   = sanitize_title( (string) ( $location['suburb'] ?? '' ) );
		$postcode = sanitize_text_field( (string) ( $location['postcode'] ?? '' ) );

		if ( '' === $country || '' === $state || '' === $suburb || '' === $postcode ) {
			return '';
		}

		$pattern = $pattern ?? $this->settings->url_pattern();

		if ( LocalAdPageSettingsModel::URL_PATTERN_SLASH === $pattern ) {
			return sprintf(
				'local/%s/%s/%s-%s',
				$country,
				$state,
				$suburb,
				$postcode
			);
		}

		return sprintf(
			'local-%s-%s-%s-%s',
			$country,
			$state,
			$suburb,
			$postcode
		);
	}

	public function get_public_url( int $post_id ): string {
		$path = $this->build_path( $this->get_location( $post_id ) );

		return '' === $path ? '' : home_url( trailingslashit( $path ) );
	}

	/**
	 * @param array<string, mixed> $input Location fields.
	 * @return array<string, string>
	 */
	public function sanitize_location( array $input ): array {
		return array(
			'country'  => sanitize_title( (string) ( $input['country'] ?? '' ) ),
			'state'    => sanitize_title( (string) ( $input['state'] ?? '' ) ),
			'suburb'   => sanitize_title( (string) ( $input['suburb'] ?? '' ) ),
			'postcode' => sanitize_text_field( (string) ( $input['postcode'] ?? '' ) ),
		);
	}

	public function find_by_path( string $country, string $state, string $suburb, string $postcode ): ?\WP_Post {
		$query = new \WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => self::META_COUNTRY,
						'value' => sanitize_title( $country ),
					),
					array(
						'key'   => self::META_STATE,
						'value' => sanitize_title( $state ),
					),
					array(
						'key'   => self::META_SUBURB,
						'value' => sanitize_title( $suburb ),
					),
					array(
						'key'   => self::META_POSTCODE,
						'value' => sanitize_text_field( $postcode ),
					),
				),
				'no_found_rows'  => true,
			)
		);

		$post = $query->posts[0] ?? null;

		return $post instanceof \WP_Post ? $post : null;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function to_rest_item( \WP_Post $post ): array {
		$location = $this->get_location( (int) $post->ID );

		return array(
			'id'        => (int) $post->ID,
			'title'     => get_the_title( $post ),
			'content'   => apply_filters( 'the_content', $post->post_content ),
			'headline'  => (string) get_post_meta( $post->ID, self::META_HEADLINE, true ),
			'body'      => (string) get_post_meta( $post->ID, self::META_BODY, true ),
			'location'  => $location,
			'url'       => $this->get_public_url( (int) $post->ID ),
			'url_pattern' => $this->settings->url_pattern(),
			'status'    => $post->post_status,
			'modified'  => $post->post_modified_gmt,
		);
	}
}
