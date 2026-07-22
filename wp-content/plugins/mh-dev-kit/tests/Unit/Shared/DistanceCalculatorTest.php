<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Shared;

use MH\DevKit\Shared\DistanceCalculator;
use MH\DevKit\Tests\TestCase;

final class DistanceCalculatorTest extends TestCase {

	public function test_metres_sydney_to_melbourne_is_plausible(): void {
		$metres = DistanceCalculator::metres( -33.8688, 151.2093, -37.8136, 144.9631 );

		$this->assertGreaterThan( 600000, $metres );
		$this->assertLessThan( 800000, $metres );
	}

	public function test_kilometres_matches_metres(): void {
		$metres = DistanceCalculator::metres( -27.4698, 153.0251, -31.9505, 115.8605 );
		$km     = DistanceCalculator::kilometres( -27.4698, 153.0251, -31.9505, 115.8605 );

		$this->assertEqualsWithDelta( $metres / 1000, $km, 0.001 );
	}
}
