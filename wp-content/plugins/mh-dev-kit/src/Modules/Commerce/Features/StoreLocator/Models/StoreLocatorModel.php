<?php
/**
 * Store records and distance sorting for the Store Locator.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Models;

use MH\DevKit\Shared\DistanceCalculator;

final class StoreLocatorModel {

	public const OPTION_KEY = 'mhdk_store_locator_stores';

	public const SELECTED_STORE_OPTION = 'mhdk_store_locator_selected';

	public const SESSION_KEY = 'mhdk_selected_store_id';

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_defaults(): array {
		return array(
			array(
				'id'       => 'sydney-cbd',
				'name'     => 'Sydney CBD',
				'address'  => '100 George Street',
				'city'     => 'Sydney',
				'state'    => 'NSW',
				'postcode' => '2000',
				'lat'      => -33.8688,
				'lng'      => 151.2093,
				'phone'    => '02 9000 1001',
				'hours'    => 'Mon–Fri 9:00–17:30, Sat 10:00–14:00',
				'active'   => true,
			),
			array(
				'id'       => 'melbourne-cbd',
				'name'     => 'Melbourne CBD',
				'address'  => '250 Collins Street',
				'city'     => 'Melbourne',
				'state'    => 'VIC',
				'postcode' => '3000',
				'lat'      => -37.8136,
				'lng'      => 144.9631,
				'phone'    => '03 9000 2002',
				'hours'    => 'Mon–Fri 9:00–17:30, Sat 10:00–14:00',
				'active'   => true,
			),
			array(
				'id'       => 'brisbane-cbd',
				'name'     => 'Brisbane CBD',
				'address'  => '75 Queen Street',
				'city'     => 'Brisbane',
				'state'    => 'QLD',
				'postcode' => '4000',
				'lat'      => -27.4698,
				'lng'      => 153.0251,
				'phone'    => '07 9000 3003',
				'hours'    => 'Mon–Fri 9:00–17:30, Sat 10:00–14:00',
				'active'   => true,
			),
			array(
				'id'       => 'perth-cbd',
				'name'     => 'Perth CBD',
				'address'  => '88 St Georges Terrace',
				'city'     => 'Perth',
				'state'    => 'WA',
				'postcode' => '6000',
				'lat'      => -31.9505,
				'lng'      => 115.8605,
				'phone'    => '08 9000 4004',
				'hours'    => 'Mon–Fri 9:00–17:30, Sat 10:00–14:00',
				'active'   => true,
			),
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_all(): array {
		$stored = get_option( self::OPTION_KEY, null );

		if ( ! is_array( $stored ) || array() === $stored ) {
			return $this->get_defaults();
		}

		return $this->normalize_stores( $stored );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_active(): array {
		return array_values(
			array_filter(
				$this->get_all(),
				static fn( array $store ): bool => ! empty( $store['active'] )
			)
		);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_by_id( string $store_id ): ?array {
		foreach ( $this->get_all() as $store ) {
			if ( $store['id'] === $store_id ) {
				return $store;
			}
		}

		return null;
	}

	/**
	 * @return array<int, array<string, mixed>> Stores with distance_metres and distance_km.
	 */
	public function sort_by_distance( float $lat, float $lng, bool $active_only = true ): array {
		$stores = $active_only ? $this->get_active() : $this->get_all();

		$with_distance = array_map(
			static function ( array $store ) use ( $lat, $lng ): array {
				$metres              = DistanceCalculator::metres(
					$lat,
					$lng,
					(float) $store['lat'],
					(float) $store['lng']
				);
				$store['distance_metres'] = round( $metres );
				$store['distance_km']     = round( $metres / 1000, 1 );

				return $store;
			},
			$stores
		);

		usort(
			$with_distance,
			static fn( array $a, array $b ): int => $a['distance_metres'] <=> $b['distance_metres']
		);

		return $with_distance;
	}

	public function distance_to_store( float $lat, float $lng, string $store_id ): ?float {
		$store = $this->get_by_id( $store_id );

		if ( null === $store ) {
			return null;
		}

		return DistanceCalculator::metres( $lat, $lng, (float) $store['lat'], (float) $store['lng'] );
	}

	public function seed_defaults_if_empty(): void {
		$stored = get_option( self::OPTION_KEY, null );

		if ( is_array( $stored ) && array() !== $stored ) {
			return;
		}

		update_option( self::OPTION_KEY, $this->get_defaults(), false );
	}

	/**
	 * @param array<int, array<string, mixed>> $stores Raw store rows.
	 * @return array<int, array<string, mixed>>
	 */
	public function normalize_stores( array $stores ): array {
		$clean = array();

		foreach ( $stores as $store ) {
			if ( ! is_array( $store ) || empty( $store['id'] ) ) {
				continue;
			}

			$clean[] = array(
				'id'       => sanitize_key( (string) $store['id'] ),
				'name'     => sanitize_text_field( (string) ( $store['name'] ?? '' ) ),
				'address'  => sanitize_text_field( (string) ( $store['address'] ?? '' ) ),
				'city'     => sanitize_text_field( (string) ( $store['city'] ?? '' ) ),
				'state'    => sanitize_text_field( (string) ( $store['state'] ?? '' ) ),
				'postcode' => sanitize_text_field( (string) ( $store['postcode'] ?? '' ) ),
				'lat'      => (float) ( $store['lat'] ?? 0 ),
				'lng'      => (float) ( $store['lng'] ?? 0 ),
				'phone'    => sanitize_text_field( (string) ( $store['phone'] ?? '' ) ),
				'hours'    => sanitize_text_field( (string) ( $store['hours'] ?? '' ) ),
				'active'   => ! empty( $store['active'] ),
			);
		}

		return $clean;
	}

	public function format_address( array $store ): string {
		return trim(
			sprintf(
				'%s, %s %s %s',
				(string) ( $store['address'] ?? '' ),
				(string) ( $store['city'] ?? '' ),
				(string) ( $store['state'] ?? '' ),
				(string) ( $store['postcode'] ?? '' )
			)
		);
	}
}
