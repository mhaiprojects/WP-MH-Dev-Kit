<?php
/**
 * Backend Restrict Origin settings storage.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models;

final class RestrictOriginSettingsModel {

	public const OPTION_KEY = 'mhdk_restrict_origin_settings';

	public const SETTINGS_SLUG = 'mhdk-settings-restrict-origin';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'enabled'           => false,
			'allow_same_host'   => true,
			'allowed_hosts'     => '',
			'bypass_ajax'       => true,
			'bypass_cron'       => true,
			'bypass_rest'       => false,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_all(): array {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return array_merge( $this->get_defaults(), $stored );
	}

	public function get( string $key, mixed $default = null ): mixed {
		$all = $this->get_all();

		return $all[ $key ] ?? $default;
	}

	public function is_enabled(): bool {
		return (bool) $this->get( 'enabled', false );
	}

	/**
	 * @return string[]
	 */
	public function get_allowed_hosts(): array {
		$raw   = (string) $this->get( 'allowed_hosts', '' );
		$lines = preg_split( '/[\r\n,]+/', $raw ) ?: array();
		$hosts = array();

		foreach ( $lines as $line ) {
			$host = strtolower( trim( $line ) );

			if ( '' !== $host ) {
				$hosts[] = $host;
			}
		}

		return array_values( array_unique( $hosts ) );
	}

	/**
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		$clean['enabled']         = ! empty( $input['enabled'] );
		$clean['allow_same_host'] = ! empty( $input['allow_same_host'] );
		$clean['bypass_ajax']     = ! empty( $input['bypass_ajax'] );
		$clean['bypass_cron']     = ! empty( $input['bypass_cron'] );
		$clean['bypass_rest']     = ! empty( $input['bypass_rest'] );

		if ( isset( $input['allowed_hosts'] ) ) {
			$clean['allowed_hosts'] = sanitize_textarea_field( (string) $input['allowed_hosts'] );
		}

		return $clean;
	}
}
