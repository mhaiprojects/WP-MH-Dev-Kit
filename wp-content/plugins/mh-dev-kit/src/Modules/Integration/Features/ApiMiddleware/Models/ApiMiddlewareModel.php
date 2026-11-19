<?php
/**
 * Third-party API connection records and execution log.
 *
 * @package MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models;

final class ApiMiddlewareModel {

	public const CONNECTIONS_OPTION = 'mhdk_api_middleware_connections';

	public const LOG_OPTION = 'mhdk_api_middleware_log';

	public const CRON_HOOK = 'mhdk_api_middleware_run_scheduled';

	public const LAST_RUN_TRANSIENT = 'mhdk_api_last_run_notice';

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_connections(): array {
		$stored = get_option( self::CONNECTIONS_OPTION, array() );

		return is_array( $stored ) ? $this->normalize_connections( $stored ) : array();
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_connection( string $id ): ?array {
		foreach ( $this->get_connections() as $connection ) {
			if ( $connection['id'] === $id ) {
				return $connection;
			}
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $connection Connection payload.
	 */
	public function save_connection( array $connection ): void {
		$connections = $this->get_connections();
		$normalized  = $this->normalize_connection( $connection );
		$found       = false;

		foreach ( $connections as $index => $existing ) {
			if ( $existing['id'] === $normalized['id'] ) {
				if ( '' === $normalized['auth_value'] ) {
					$normalized['auth_value'] = (string) $existing['auth_value'];
				}

				$connections[ $index ] = $normalized;
				$found                   = true;
				break;
			}
		}

		if ( ! $found ) {
			$connections[] = $normalized;
		}

		update_option( self::CONNECTIONS_OPTION, $connections, false );
	}

	public function delete_connection( string $id ): void {
		$connections = array_values(
			array_filter(
				$this->get_connections(),
				static fn( array $connection ): bool => $connection['id'] !== $id
			)
		);

		update_option( self::CONNECTIONS_OPTION, $connections, false );
	}

	/**
	 * @return array<string, mixed>
	 */
	public function run_connection( string $id ): array {
		$connection = $this->get_connection( $id );

		if ( null === $connection ) {
			return array(
				'success' => false,
				'message' => 'Connection not found.',
			);
		}

		return $this->execute_request( $connection, true );
	}

	/**
	 * Test a connection without persisting results.
	 *
	 * @param array<string, mixed> $connection Connection payload.
	 * @return array<string, mixed>
	 */
	public function test_connection( array $connection ): array {
		$existing = null;

		if ( ! empty( $connection['id'] ) ) {
			$existing = $this->get_connection( (string) $connection['id'] );
		}

		$normalized = $this->normalize_connection( $connection );

		if ( null !== $existing && '' === $normalized['auth_value'] ) {
			$normalized['auth_value'] = (string) $existing['auth_value'];
		}

		if ( '' === $normalized['base_url'] ) {
			return array(
				'success' => false,
				'message' => 'Base URL is required.',
				'status'  => 0,
			);
		}

		return $this->execute_request( $normalized, false );
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_stored_preview( array $connection, int $max_chars = 500 ): ?array {
		$key = (string) ( $connection['target_key'] ?? '' );

		if ( '' === $key ) {
			return null;
		}

		$body = 'transient' === ( $connection['target_type'] ?? 'option' )
			? get_transient( $key )
			: get_option( $key, '' );

		if ( ! is_string( $body ) || '' === $body ) {
			return null;
		}

		$truncated = strlen( $body ) > $max_chars
			? substr( $body, 0, $max_chars ) . '…'
			: $body;

		return array(
			'key'     => $key,
			'preview' => $truncated,
		);
	}

	public function get_last_run_notice(): ?array {
		$stored = get_transient( self::LAST_RUN_TRANSIENT );

		return is_array( $stored ) ? $stored : null;
	}

	public function set_last_run_notice( array $notice ): void {
		set_transient( self::LAST_RUN_TRANSIENT, $notice, MINUTE_IN_SECONDS );
	}

	/**
	 * @param array<string, mixed> $connection Connection row.
	 * @return array<string, mixed>
	 */
	private function execute_request( array $connection, bool $persist ): array {
		$url     = $this->build_url( $connection );
		$headers = $this->build_headers( $connection );
		$method  = strtoupper( (string) $connection['method'] );

		$args = array(
			'method'  => $method,
			'headers' => $headers,
			'timeout' => 20,
		);

		$response = 'POST' === $method
			? wp_remote_post( $url, $args )
			: wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			$result = array(
				'success' => false,
				'message' => $response->get_error_message(),
				'status'  => 0,
			);

			if ( $persist ) {
				$this->persist_result( $connection, $result, '' );
			}

			return $result;
		}

		$status = (int) wp_remote_retrieve_response_code( $response );
		$body   = (string) wp_remote_retrieve_body( $response );
		$ok     = $status >= 200 && $status < 300;

		$result = array(
			'success'       => $ok,
			'message'       => $ok ? 'OK' : 'HTTP ' . $status,
			'status'        => $status,
			'body_preview'  => strlen( $body ) > 500 ? substr( $body, 0, 500 ) . '…' : $body,
			'target_key'    => (string) ( $connection['target_key'] ?? '' ),
		);

		if ( $persist ) {
			$this->persist_result( $connection, $result, $body );
		}

		return $result;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_log( int $limit = 20 ): array {
		$stored = get_option( self::LOG_OPTION, array() );

		if ( ! is_array( $stored ) ) {
			return array();
		}

		return array_slice( $stored, 0, max( 1, $limit ) );
	}

	public function seed_demo_connection_if_empty(): void {
		if ( ! empty( $this->get_connections() ) ) {
			return;
		}

		$this->save_connection(
			array(
				'id'           => 'jsonplaceholder-posts',
				'name'         => 'JSONPlaceholder Posts (POC)',
				'base_url'     => 'https://jsonplaceholder.typicode.com',
				'endpoint'     => '/posts/1',
				'method'       => 'GET',
				'auth_type'    => 'none',
				'auth_header'  => 'Authorization',
				'auth_value'   => '',
				'schedule'     => 'daily',
				'target_type'  => 'option',
				'target_key'   => 'mhdk_api_demo_posts',
				'enabled'      => true,
			)
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $connections Raw connections.
	 * @return array<int, array<string, mixed>>
	 */
	private function normalize_connections( array $connections ): array {
		$clean = array();

		foreach ( $connections as $connection ) {
			if ( ! is_array( $connection ) ) {
				continue;
			}

			$clean[] = $this->normalize_connection( $connection );
		}

		return $clean;
	}

	/**
	 * @param array<string, mixed> $connection Raw connection.
	 * @return array<string, mixed>
	 */
	private function normalize_connection( array $connection ): array {
		$id     = sanitize_key( (string) ( $connection['id'] ?? uniqid( 'api-', true ) ) );
		$method = strtoupper( (string) ( $connection['method'] ?? 'GET' ) );
		if ( ! in_array( $method, array( 'GET', 'POST' ), true ) ) {
			$method = 'GET';
		}

		$auth_type = (string) ( $connection['auth_type'] ?? 'none' );
		if ( ! in_array( $auth_type, array( 'none', 'bearer', 'basic', 'api_key' ), true ) ) {
			$auth_type = 'none';
		}

		$schedule = (string) ( $connection['schedule'] ?? 'manual' );
		if ( ! in_array( $schedule, array( 'manual', 'hourly', 'daily' ), true ) ) {
			$schedule = 'manual';
		}

		$target_type = (string) ( $connection['target_type'] ?? 'option' );
		if ( ! in_array( $target_type, array( 'option', 'transient' ), true ) ) {
			$target_type = 'option';
		}

		return array(
			'id'          => $id,
			'name'        => sanitize_text_field( (string) ( $connection['name'] ?? $id ) ),
			'base_url'    => esc_url_raw( (string) ( $connection['base_url'] ?? '' ) ),
			'endpoint'    => sanitize_text_field( (string) ( $connection['endpoint'] ?? '/' ) ),
			'method'      => $method,
			'auth_type'   => $auth_type,
			'auth_header' => sanitize_text_field( (string) ( $connection['auth_header'] ?? 'Authorization' ) ),
			'auth_value'  => sanitize_text_field( (string) ( $connection['auth_value'] ?? '' ) ),
			'schedule'    => $schedule,
			'target_type' => $target_type,
			'target_key'  => sanitize_key( (string) ( $connection['target_key'] ?? 'mhdk_api_data_' . $id ) ),
			'enabled'     => ! empty( $connection['enabled'] ),
			'last_run'    => (int) ( $connection['last_run'] ?? 0 ),
			'last_status' => sanitize_text_field( (string) ( $connection['last_status'] ?? '' ) ),
		);
	}

	/**
	 * @param array<string, mixed> $connection Connection row.
	 */
	private function build_url( array $connection ): string {
		return rtrim( (string) $connection['base_url'], '/' ) . '/' . ltrim( (string) $connection['endpoint'], '/' );
	}

	/**
	 * @param array<string, mixed> $connection Connection row.
	 * @return array<string, string>
	 */
	private function build_headers( array $connection ): array {
		$headers = array(
			'Accept' => 'application/json',
		);

		$value = (string) $connection['auth_value'];
		$type  = (string) $connection['auth_type'];
		$header = (string) $connection['auth_header'];

		if ( '' === $value || 'none' === $type ) {
			return $headers;
		}

		if ( 'bearer' === $type ) {
			$headers[ $header ?: 'Authorization' ] = 'Bearer ' . $value;
		} elseif ( 'basic' === $type ) {
			$headers[ $header ?: 'Authorization' ] = 'Basic ' . base64_encode( $value );
		} else {
			$headers[ $header ?: 'X-API-Key' ] = $value;
		}

		return $headers;
	}

	/**
	 * @param array<string, mixed> $connection Connection row.
	 * @param array<string, mixed> $result Run result.
	 */
	private function persist_result( array $connection, array $result, string $body ): void {
		$connection['last_run']    = time();
		$connection['last_status'] = ! empty( $result['success'] ) ? 'success' : 'error';
		$this->save_connection( $connection );

		if ( ! empty( $result['success'] ) && '' !== $body ) {
			$key = (string) $connection['target_key'];

			if ( 'transient' === $connection['target_type'] ) {
				set_transient( $key, $body, DAY_IN_SECONDS );
			} else {
				update_option( $key, $body, false );
			}
		}

		$log = $this->get_log( 50 );
		array_unshift(
			$log,
			array(
				'connection_id' => (string) $connection['id'],
				'timestamp'     => time(),
				'success'       => ! empty( $result['success'] ),
				'message'       => (string) ( $result['message'] ?? '' ),
				'status'        => (int) ( $result['status'] ?? 0 ),
			)
		);

		update_option( self::LOG_OPTION, array_slice( $log, 0, 50 ), false );
	}
}
