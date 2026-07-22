<?php
/**
 * API Middleware REST triggers.
 *
 * @package MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters;

use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareModel;

final class ApiMiddlewareRestPresenter {

	public function __construct(
		private ApiMiddlewareModel $model
	) {}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'mhdk/v1',
			'/api-connections',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_connections' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);

		register_rest_route(
			'mhdk/v1',
			'/api-connections/(?P<id>[a-z0-9-]+)/run',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'run_connection' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);
	}

	public function can_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	public function list_connections(): \WP_REST_Response {
		$connections = array_map(
			static fn( array $connection ): array => array(
				'id'          => (string) $connection['id'],
				'name'        => (string) $connection['name'],
				'schedule'    => (string) $connection['schedule'],
				'enabled'     => (bool) $connection['enabled'],
				'last_run'    => (int) $connection['last_run'],
				'last_status' => (string) $connection['last_status'],
			),
			$this->model->get_connections()
		);

		return new \WP_REST_Response( $connections, 200 );
	}

	public function run_connection( \WP_REST_Request $request ): \WP_REST_Response {
		$result = $this->model->run_connection( (string) $request->get_param( 'id' ) );

		return new \WP_REST_Response( $result, ! empty( $result['success'] ) ? 200 : 500 );
	}
}
