<?php
/**
 * Family Tree CPT registration.
 *
 * @package MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters;

use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;

final class FamilyTreePostTypePresenter {

	public function __construct(
		private FamilyTreeModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	public function register_post_type(): void {
		register_post_type( FamilyTreeModel::POST_TYPE, $this->model->get_post_type_args() );
	}

	public function register_meta(): void {
		register_post_meta(
			FamilyTreeModel::POST_TYPE,
			FamilyTreeModel::META_TREE,
			array(
				'single'        => true,
				'type'          => 'string',
				'show_in_rest'  => true,
				'auth_callback' => static fn(): bool => is_user_logged_in(),
			)
		);
	}
}
