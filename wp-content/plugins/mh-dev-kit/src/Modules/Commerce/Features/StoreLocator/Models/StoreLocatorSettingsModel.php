<?php
/**
 * Store Locator settings.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Models;

final class StoreLocatorSettingsModel {

	public const OPTION_KEY = 'mhdk_store_locator_settings';

	public const SETTINGS_SLUG = 'mhdk-settings-store-locator';

	public const DEMO_PAGE_SLUG = 'store-locator';

	public const DEMO_OPTION = 'mhdk_store_locator_demo_page_id';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'max_distance_km'      => 500,
			'enable_checkout_field'  => true,
			'show_phone'             => true,
			'show_hours'             => true,
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

	public function get_max_distance_metres(): float {
		return (float) $this->get( 'max_distance_km', 500 ) * 1000;
	}

	public function checkout_enabled(): bool {
		return (bool) $this->get( 'enable_checkout_field', true );
	}

	/**
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		if ( isset( $input['max_distance_km'] ) ) {
			$clean['max_distance_km'] = max( 1, (int) $input['max_distance_km'] );
		}

		$clean['enable_checkout_field'] = ! empty( $input['enable_checkout_field'] );
		$clean['show_phone']            = ! empty( $input['show_phone'] );
		$clean['show_hours']            = ! empty( $input['show_hours'] );

		return $clean;
	}
}
