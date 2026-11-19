<?php
/**
 * API Middleware admin UI.
 *
 * @package MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Integration\Features\ApiMiddleware\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareModel;
use MH\DevKit\Shared\AssetRegistrar;

final class ApiMiddlewareAdminPresenter {

	public function __construct(
		private ApiMiddlewareModel $model
	) {}

	public function register(): void {
		add_action( 'admin_post_mhdk_save_api_connection', array( $this, 'handle_save' ) );
		add_action( 'admin_post_mhdk_delete_api_connection', array( $this, 'handle_delete' ) );
		add_action( 'admin_post_mhdk_run_api_connection', array( $this, 'handle_run' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, 'mhdk-settings-api-middleware' ) ) {
			return;
		}

		AssetRegistrar::enqueue_script(
			'mhdk-api-middleware-admin',
			'Modules/Integration/Features/ApiMiddleware',
			'js/api-middleware-admin.js',
			array( 'jquery' ),
			true
		);

		wp_localize_script(
			'mhdk-api-middleware-admin',
			'mhdkApiMiddleware',
			array(
				'restUrl' => rest_url( 'mhdk/v1/api-connections/' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'confirmDelete' => __( 'Delete this connection?', 'mh-dev-kit' ),
					'testing'       => __( 'Testing connection…', 'mh-dev-kit' ),
					'testOk'        => __( 'Connection successful.', 'mh-dev-kit' ),
					'testFail'      => __( 'Connection failed.', 'mh-dev-kit' ),
					'editMode'      => __( 'Editing connection — submit to update.', 'mh-dev-kit' ),
					'createMode'    => __( 'Creating new connection.', 'mh-dev-kit' ),
				),
			)
		);
	}

	public function render_page(): void {
		$connections = $this->model->get_connections();

		foreach ( $connections as $index => $connection ) {
			$preview = $this->model->get_stored_preview( $connection );
			if ( null !== $preview ) {
				$connections[ $index ]['stored_preview'] = $preview;
			}
		}

		ViewRenderer::display(
			'Modules/Integration/Features/ApiMiddleware/Views/settings-page.php',
			array(
				'connections'  => $connections,
				'log'          => $this->model->get_log( 10 ),
				'last_run'     => $this->model->get_last_run_notice(),
				'demo_example' => array(
					'id'          => 'jsonplaceholder-posts',
					'name'        => 'JSONPlaceholder Posts (POC)',
					'base_url'    => 'https://jsonplaceholder.typicode.com',
					'endpoint'    => '/posts/1',
					'method'      => 'GET',
					'auth_type'   => 'none',
					'schedule'    => 'daily',
					'target_type' => 'option',
					'target_key'  => 'mhdk_api_demo_posts',
				),
			)
		);
	}

	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'mh-dev-kit' ) );
		}

		check_admin_referer( 'mhdk_save_api_connection' );

		$this->model->save_connection( $this->connection_from_post() );

		wp_safe_redirect( admin_url( 'admin.php?page=mhdk-settings-api-middleware&updated=1' ) );
		exit;
	}

	public function handle_delete(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'mh-dev-kit' ) );
		}

		check_admin_referer( 'mhdk_delete_api_connection' );

		$id = sanitize_key( wp_unslash( (string) ( $_GET['id'] ?? '' ) ) );

		if ( '' !== $id ) {
			$this->model->delete_connection( $id );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=mhdk-settings-api-middleware&deleted=1' ) );
		exit;
	}

	public function handle_run(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'mh-dev-kit' ) );
		}

		check_admin_referer( 'mhdk_run_api_connection' );

		$id = sanitize_key( wp_unslash( (string) ( $_GET['id'] ?? '' ) ) );

		if ( '' !== $id ) {
			$result = $this->model->run_connection( $id );
			$this->model->set_last_run_notice(
				array_merge(
					$result,
					array(
						'connection_id' => $id,
						'timestamp'     => time(),
					)
				)
			);
		}

		wp_safe_redirect( admin_url( 'admin.php?page=mhdk-settings-api-middleware&ran=1' ) );
		exit;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function connection_from_post(): array {
		return array(
			'id'          => sanitize_key( wp_unslash( (string) ( $_POST['id'] ?? '' ) ) ),
			'name'        => sanitize_text_field( wp_unslash( (string) ( $_POST['name'] ?? '' ) ) ),
			'base_url'    => esc_url_raw( wp_unslash( (string) ( $_POST['base_url'] ?? '' ) ) ),
			'endpoint'    => sanitize_text_field( wp_unslash( (string) ( $_POST['endpoint'] ?? '/' ) ) ),
			'method'      => sanitize_text_field( wp_unslash( (string) ( $_POST['method'] ?? 'GET' ) ) ),
			'auth_type'   => sanitize_text_field( wp_unslash( (string) ( $_POST['auth_type'] ?? 'none' ) ) ),
			'auth_header' => sanitize_text_field( wp_unslash( (string) ( $_POST['auth_header'] ?? 'Authorization' ) ) ),
			'auth_value'  => sanitize_text_field( wp_unslash( (string) ( $_POST['auth_value'] ?? '' ) ) ),
			'schedule'    => sanitize_text_field( wp_unslash( (string) ( $_POST['schedule'] ?? 'manual' ) ) ),
			'target_type' => sanitize_text_field( wp_unslash( (string) ( $_POST['target_type'] ?? 'option' ) ) ),
			'target_key'  => sanitize_key( wp_unslash( (string) ( $_POST['target_key'] ?? '' ) ) ),
			'enabled'     => ! empty( $_POST['enabled'] ),
		);
	}
}
