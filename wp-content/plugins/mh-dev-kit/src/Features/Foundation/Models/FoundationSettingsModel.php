<?php
/**
 * Foundation settings option storage.
 *
 * @package MH\DevKit\Features\Foundation\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Features\Foundation\Models;

final class FoundationSettingsModel {

	public const OPTION_KEY = 'mhdk_foundation_settings';

	public const MENU_SLUG = 'mh-dev-kit';

	public const SETTINGS_SLUG = 'mh-dev-kit-foundation';

	public const CONTENT_MENU_SLUG = 'mh-dev-kit-content';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'default_callout_style' => 'info',
			'web_links_new_tab'     => true,
			'menu_currency_symbol'  => '$',
			'enable_frontend_css'   => true,
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

	/**
	 * @param mixed $default Fallback value.
	 */
	public function get( string $key, mixed $default = null ): mixed {
		$all = $this->get_all();

		return $all[ $key ] ?? $default;
	}

	/**
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean   = $this->get_defaults();
		$allowed = array( 'info', 'warning', 'success' );

		if ( isset( $input['default_callout_style'] ) && in_array( $input['default_callout_style'], $allowed, true ) ) {
			$clean['default_callout_style'] = $input['default_callout_style'];
		}

		$clean['web_links_new_tab']   = ! empty( $input['web_links_new_tab'] );
		$clean['enable_frontend_css'] = ! empty( $input['enable_frontend_css'] );

		if ( isset( $input['menu_currency_symbol'] ) ) {
			$clean['menu_currency_symbol'] = sanitize_text_field( (string) $input['menu_currency_symbol'] );
		}

		return $clean;
	}
}
