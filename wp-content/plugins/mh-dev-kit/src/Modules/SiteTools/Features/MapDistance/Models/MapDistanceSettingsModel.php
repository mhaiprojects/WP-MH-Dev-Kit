<?php
/**
 * Map distance settings and haversine math.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\MapDistance\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\MapDistance\Models;

final class MapDistanceSettingsModel {

	public const OPTION_KEY = 'mhdk_map_distance_settings';

	public const SETTINGS_SLUG = 'mhdk-settings-map-distance';

	public const DEMO_PAGE_SLUG = 'map-distance-demo';

	public const DEMO_OPTION = 'mhdk_map_distance_demo_page_id';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'start_label' => 'Shepparton CBD',
			'start_lat'   => -36.3809,
			'start_lng'   => 145.3985,
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

	public function get_start_lat(): float {
		return (float) $this->get( 'start_lat', -36.3809 );
	}

	public function get_start_lng(): float {
		return (float) $this->get( 'start_lng', 145.3985 );
	}

	/**
	 * Haversine distance in metres.
	 */
	public function distance_metres( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
		$earth_radius = 6371000.0;
		$lat1_rad     = deg2rad( $lat1 );
		$lat2_rad     = deg2rad( $lat2 );
		$delta_lat    = deg2rad( $lat2 - $lat1 );
		$delta_lng    = deg2rad( $lng2 - $lng1 );

		$a = sin( $delta_lat / 2 ) ** 2
			+ cos( $lat1_rad ) * cos( $lat2_rad ) * sin( $delta_lng / 2 ) ** 2;

		$c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );

		return $earth_radius * $c;
	}

	/**
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		if ( isset( $input['start_label'] ) ) {
			$clean['start_label'] = sanitize_text_field( (string) $input['start_label'] );
		}

		if ( isset( $input['start_lat'] ) ) {
			$clean['start_lat'] = (float) $input['start_lat'];
		}

		if ( isset( $input['start_lng'] ) ) {
			$clean['start_lng'] = (float) $input['start_lng'];
		}

		return $clean;
	}
}
