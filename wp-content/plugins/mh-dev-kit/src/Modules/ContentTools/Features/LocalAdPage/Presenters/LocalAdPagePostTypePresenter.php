<?php
/**
 * Local Ad Page CPT and meta registration.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters;

use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;

final class LocalAdPagePostTypePresenter {

	public function __construct(
		private LocalAdPageModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_post_type(): void {
		register_post_type( LocalAdPageModel::POST_TYPE, $this->model->get_post_type_args() );
	}

	public function register_meta(): void {
		$meta_keys = array(
			LocalAdPageModel::META_COUNTRY,
			LocalAdPageModel::META_STATE,
			LocalAdPageModel::META_SUBURB,
			LocalAdPageModel::META_POSTCODE,
			LocalAdPageModel::META_HEADLINE,
			LocalAdPageModel::META_BODY,
		);

		foreach ( $meta_keys as $meta_key ) {
			register_post_meta(
				LocalAdPageModel::POST_TYPE,
				$meta_key,
				array(
					'single'            => true,
					'type'              => 'string',
					'show_in_rest'      => true,
					'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
}
