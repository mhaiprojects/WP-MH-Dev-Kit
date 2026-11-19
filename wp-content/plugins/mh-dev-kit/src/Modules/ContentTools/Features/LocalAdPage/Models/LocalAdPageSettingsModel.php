<?php
/**
 * Local Ad Page settings option storage.
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models;

final class LocalAdPageSettingsModel {

	public const OPTION_KEY = 'mhdk_local_ad_settings';

	public const SETTINGS_SLUG = 'mhdk-settings-local-ad';

	public const URL_PATTERN_DASH  = 'dash';

	public const URL_PATTERN_SLASH = 'slash';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'url_pattern' => self::URL_PATTERN_DASH,
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

	public function url_pattern(): string {
		$pattern = (string) $this->get_all()['url_pattern'];

		return self::URL_PATTERN_SLASH === $pattern ? self::URL_PATTERN_SLASH : self::URL_PATTERN_DASH;
	}

	/**
	 * @param array<string, mixed> $input Raw settings.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		$pattern = (string) ( $input['url_pattern'] ?? self::URL_PATTERN_DASH );

		$clean['url_pattern'] = self::URL_PATTERN_SLASH === $pattern ? self::URL_PATTERN_SLASH : self::URL_PATTERN_DASH;

		return $clean;
	}
}
