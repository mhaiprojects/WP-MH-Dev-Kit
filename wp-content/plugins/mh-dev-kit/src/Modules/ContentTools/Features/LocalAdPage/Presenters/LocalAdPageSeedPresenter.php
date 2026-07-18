<?php
/**
 * Seeds Shepparton VIC example local ad page.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters;

use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;

final class LocalAdPageSeedPresenter {

	public function __construct(
		private LocalAdPageModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'maybe_seed' ), 20 );
	}

	public function maybe_seed(): void {
		if ( get_option( LocalAdPageModel::SEED_OPTION ) ) {
			return;
		}

		$existing = $this->model->find_by_path( 'au', 'vic', 'shepparton', '3630' );

		if ( $existing instanceof \WP_Post ) {
			update_option( LocalAdPageModel::SEED_OPTION, 1, false );
			return;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => LocalAdPageModel::POST_TYPE,
				'post_title'   => __( 'Shepparton Local Produce', 'mh-dev-kit' ),
				'post_content' => __( 'Welcome to the Goulburn Valley — fresh produce, local vendors, and community markets in Shepparton VIC 3630.', 'mh-dev-kit' ),
				'post_status'  => 'publish',
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return;
		}

		update_post_meta( $post_id, LocalAdPageModel::META_COUNTRY, 'au' );
		update_post_meta( $post_id, LocalAdPageModel::META_STATE, 'vic' );
		update_post_meta( $post_id, LocalAdPageModel::META_SUBURB, 'shepparton' );
		update_post_meta( $post_id, LocalAdPageModel::META_POSTCODE, '3630' );
		update_post_meta( $post_id, LocalAdPageModel::META_HEADLINE, __( 'Fresh from the Goulburn Valley', 'mh-dev-kit' ) );
		update_post_meta(
			$post_id,
			LocalAdPageModel::META_BODY,
			__( 'Discover apples, pears, tomatoes, and artisan goods from Shepparton and surrounding farms. Visit our weekend market or order online from local growers.', 'mh-dev-kit' )
		);

		update_option( LocalAdPageModel::SEED_OPTION, 1, false );
	}
}
