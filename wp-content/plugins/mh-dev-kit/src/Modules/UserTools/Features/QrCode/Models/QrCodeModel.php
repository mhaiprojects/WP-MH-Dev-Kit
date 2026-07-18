<?php
/**
 * QR Code data model.
 *
 * @package MH\DevKit\Modules\UserTools\Features\QrCode\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\QrCode\Models;

use MH\DevKit\Shared\UserScopedCptHelper;

final class QrCodeModel {

	public const POST_TYPE = 'mhdk_qr_code';

	public const META_TARGET = 'mhdk_qr_target';

	public const META_SIZE = 'mhdk_qr_size';

	/**
	 * @return array<string, mixed>
	 */
	public function get_post_type_args(): array {
		$args = UserScopedCptHelper::author_caps_args(
			__( 'QR Code', 'mh-dev-kit' ),
			__( 'QR Codes', 'mh-dev-kit' ),
			'dashicons-screenoptions'
		);

		$args['labels']['all_items'] = __( 'My QR Codes', 'mh-dev-kit' );

		return $args;
	}

	public function get_target( int $post_id ): string {
		return (string) get_post_meta( $post_id, self::META_TARGET, true );
	}

	public function get_size( int $post_id ): int {
		$size = (int) get_post_meta( $post_id, self::META_SIZE, true );

		return max( 128, min( 512, $size ?: 256 ) );
	}

	/**
	 * @return \WP_Post[]
	 */
	public function get_for_current_user(): array {
		if ( ! is_user_logged_in() ) {
			return array();
		}

		$query = new \WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'author'         => get_current_user_id(),
				'posts_per_page' => 100,
				'post_status'    => array( 'publish', 'draft' ),
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		return $query->posts;
	}
}
