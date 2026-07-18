<?php
/**
 * Enforces Referer/Origin checks on wp-admin requests.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Presenters;

use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models\RestrictOriginSettingsModel;

final class RestrictOriginPresenter {

	public function __construct(
		private RestrictOriginSettingsModel $settings
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'maybe_block_request' ), 1 );
	}

	public function maybe_block_request(): void {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && $this->settings->get( 'bypass_ajax', true ) ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $this->settings->get( 'bypass_rest', false ) ) {
			return;
		}

		if ( wp_doing_cron() && $this->settings->get( 'bypass_cron', true ) ) {
			return;
		}

		if ( $this->is_allowed_request() ) {
			return;
		}

		wp_die(
			esc_html__( 'This admin request was blocked because it did not originate from an allowed site.', 'mh-dev-kit' ),
			esc_html__( 'Origin check failed', 'mh-dev-kit' ),
			array( 'response' => 403 )
		);
	}

	private function is_allowed_request(): bool {
		$site_host = $this->normalize_host( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		$allowed   = $this->settings->get_allowed_hosts();

		if ( $this->settings->get( 'allow_same_host', true ) && '' !== $site_host ) {
			$allowed[] = $site_host;
		}

		$allowed = array_values( array_unique( array_filter( $allowed ) ) );

		$referer_host = $this->host_from_header( 'HTTP_REFERER' );
		$origin_host  = $this->host_from_header( 'HTTP_ORIGIN' );

		if ( '' === $referer_host && '' === $origin_host ) {
			return false;
		}

		foreach ( array( $referer_host, $origin_host ) as $host ) {
			if ( '' === $host ) {
				continue;
			}

			if ( in_array( $host, $allowed, true ) ) {
				return true;
			}
		}

		return false;
	}

	private function host_from_header( string $header ): string {
		if ( empty( $_SERVER[ $header ] ) ) {
			return '';
		}

		$raw = sanitize_text_field( wp_unslash( (string) $_SERVER[ $header ] ) );

		return $this->normalize_host( (string) wp_parse_url( $raw, PHP_URL_HOST ) );
	}

	private function normalize_host( string $host ): string {
		return strtolower( trim( $host ) );
	}
}
