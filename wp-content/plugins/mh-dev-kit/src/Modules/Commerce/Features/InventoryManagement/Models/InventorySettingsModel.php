<?php
/**
 * Inventory Management settings.
 *
 * @package MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models;

final class InventorySettingsModel {

	public const OPTION_KEY = 'mhdk_inventory_settings';

	public const SETTINGS_SLUG = 'mhdk-settings-inventory';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'show_store_availability_on_product' => true,
			'low_stock_threshold'                => 3,
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

	public function show_on_product(): bool {
		return (bool) $this->get( 'show_store_availability_on_product', true );
	}

	public function low_stock_threshold(): int {
		return max( 0, (int) $this->get( 'low_stock_threshold', 3 ) );
	}

	/**
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		$clean['show_store_availability_on_product'] = ! empty( $input['show_store_availability_on_product'] );
		$clean['low_stock_threshold']                = max( 0, (int) ( $input['low_stock_threshold'] ?? 3 ) );

		return $clean;
	}
}
