<?php
/**
 * Seed demo API connection.
 *
 * @package MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters;

use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareModel;

final class ApiMiddlewareSeedPresenter {

	public function __construct(
		private ApiMiddlewareModel $model
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'seed_demo' ), 20 );
	}

	public function seed_demo(): void {
		$this->model->seed_demo_connection_if_empty();
	}
}
