<?php
/**
 * Haversine distance calculations shared across modules.
 *
 * @package MH\DevKit\Shared
 */

declare(strict_types=1);

namespace MH\DevKit\Shared;

final class DistanceCalculator {

	/**
	 * Great-circle distance in metres between two WGS84 coordinates.
	 */
	public static function metres( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
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

	public static function kilometres( float $lat1, float $lng1, float $lat2, float $lng2 ): float {
		return self::metres( $lat1, $lng1, $lat2, $lng2 ) / 1000;
	}
}
