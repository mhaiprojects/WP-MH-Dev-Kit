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

final class ApiMiddlewareAdminPresenter {

	public function __construct(
		private ApiMiddlewareModel $model
	) {}

	public function register(): void {
		add_action( 'admin_post_mhdk_save_api_connection', array( $this, 'handle_save' ) );
		add_action( 'admin_post_mhdk_delete_api_connection', array( $this, 'handle_delete' ) );
		add_action( 'admin_post_mhdk_run_api_connection', array( $this, 'handle_run' ) );
	}

	public function render_page(): void {
		ViewRenderer::display(
			'Modules/Integration/Features/ApiMiddleware/Views/settings-page.php',
			array(
				'connections' => $this->model->get_connections(),
				'log'         => $this->model->get_log( 10 ),
			)
		);
	}

	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'mh-dev-kit' ) );
		}

		check_admin_referer( 'mhdk_save_api_connection' );

		$this->model->save_connection(
			array(
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
			)
		);

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
			$this->model->run_connection( $id );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=mhdk-settings-api-middleware&ran=1' ) );
		exit;
	}
}
