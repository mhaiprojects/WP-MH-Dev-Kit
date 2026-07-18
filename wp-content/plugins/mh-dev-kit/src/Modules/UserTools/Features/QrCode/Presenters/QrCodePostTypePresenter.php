<?php
/**
 * QR Code CPT registration.
 *
 * @package MH\DevKit\Modules\UserTools\Features\QrCode\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\QrCode\Presenters;

use MH\DevKit\Modules\UserTools\Features\QrCode\Models\QrCodeModel;

final class QrCodePostTypePresenter {

	public function __construct(
		private QrCodeModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	public function register_post_type(): void {
		register_post_type( QrCodeModel::POST_TYPE, $this->model->get_post_type_args() );
	}

	public function register_meta(): void {
		foreach ( array( QrCodeModel::META_TARGET, QrCodeModel::META_SIZE ) as $meta_key ) {
			register_post_meta(
				QrCodeModel::POST_TYPE,
				$meta_key,
				array(
					'single'            => true,
					'type'              => 'string',
					'show_in_rest'      => true,
					'auth_callback'     => array( $this, 'can_edit_meta' ),
				)
			);
		}
	}

	public function can_edit_meta(): bool {
		return is_user_logged_in();
	}

	/**
	 * Restrict editing to post author or admins.
	 *
	 * @param string[] $caps    Required capabilities.
	 * @param string   $cap     Capability being checked.
	 * @param int      $user_id User ID.
	 * @param array    $args    Extra args.
	 * @return string[]
	 */
	public function map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
		if ( ! in_array( $cap, array( 'edit_post', 'delete_post', 'read_post' ), true ) ) {
			return $caps;
		}

		if ( empty( $args[0] ) ) {
			return $caps;
		}

		$post = get_post( (int) $args[0] );

		if ( ! $post instanceof \WP_Post || QrCodeModel::POST_TYPE !== $post->post_type ) {
			return $caps;
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return array( 'manage_options' );
		}

		if ( (int) $post->post_author !== $user_id ) {
			return array( 'do_not_allow' );
		}

		return array( 'read' );
	}
}
