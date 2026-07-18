<?php
/**
 * vCard CPT registration.
 *
 * @package MH\DevKit\Modules\UserTools\Features\VCard\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\VCard\Presenters;

use MH\DevKit\Modules\UserTools\Features\VCard\Models\VCardModel;

final class VCardPostTypePresenter {

	public function __construct(
		private VCardModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_post_type(): void {
		register_post_type( VCardModel::POST_TYPE, $this->model->get_post_type_args() );
	}

	public function register_meta(): void {
		foreach ( VCardModel::FIELDS as $meta_key ) {
			register_post_meta(
				VCardModel::POST_TYPE,
				$meta_key,
				array(
					'single'        => true,
					'type'          => 'string',
					'show_in_rest'  => true,
					'auth_callback' => static fn(): bool => is_user_logged_in(),
				)
			);
		}
	}
}
