<?php
/**
 * Shortlinks CPT registration.
 *
 * @package MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\Shortlinks\Presenters;

use MH\DevKit\Modules\UserTools\Features\Shortlinks\Models\ShortlinksModel;

final class ShortlinksPostTypePresenter {

	public function __construct(
		private ShortlinksModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_post_type(): void {
		register_post_type( ShortlinksModel::POST_TYPE, $this->model->get_post_type_args() );
	}

	public function register_meta(): void {
		foreach ( array( ShortlinksModel::META_SLUG, ShortlinksModel::META_BIO ) as $meta_key ) {
			register_post_meta(
				ShortlinksModel::POST_TYPE,
				$meta_key,
				array(
					'single'        => true,
					'type'          => 'string',
					'show_in_rest'  => true,
					'auth_callback' => static fn(): bool => is_user_logged_in(),
				)
			);
		}

		register_post_meta(
			ShortlinksModel::POST_TYPE,
			ShortlinksModel::META_LINKS,
			array(
				'single'        => true,
				'type'          => 'array',
				'show_in_rest'  => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'label' => array( 'type' => 'string' ),
								'url'   => array( 'type' => 'string' ),
							),
						),
					),
				),
				'auth_callback' => static fn(): bool => is_user_logged_in(),
			)
		);
	}
}
