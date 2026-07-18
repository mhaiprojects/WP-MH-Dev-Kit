<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\SiteTools;

use MH\DevKit\Modules\SiteTools\Features\MapDistance\Models\MapDistanceSettingsModel;
use MH\DevKit\Tests\TestCase;

final class MapDistanceSettingsModelTest extends TestCase {

	private MapDistanceSettingsModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new MapDistanceSettingsModel();
		delete_option( MapDistanceSettingsModel::OPTION_KEY );
	}

	public function test_distance_metres_shepparton_to_nearby(): void {
		$start_lat = -36.3809;
		$start_lng = 145.3985;
		$end_lat   = -36.3850;
		$end_lng   = 145.4000;

		$distance = $this->model->distance_metres( $start_lat, $start_lng, $end_lat, $end_lng );

		$this->assertGreaterThan( 400, $distance );
		$this->assertLessThan( 800, $distance );
	}

	public function test_sanitize_updates_coordinates(): void {
		$result = $this->model->sanitize(
			array(
				'start_label' => 'Test Point',
				'start_lat'   => '-36.5',
				'start_lng'   => '145.5',
			)
		);

		$this->assertSame( 'Test Point', $result['start_label'] );
		$this->assertSame( -36.5, $result['start_lat'] );
	}
}
