<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Commerce;

use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Tests\TestCase;

final class StoreLocatorModelTest extends TestCase {

	private StoreLocatorModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new StoreLocatorModel();
		delete_option( StoreLocatorModel::OPTION_KEY );
	}

	public function test_defaults_include_four_australian_stores(): void {
		$stores = $this->model->get_defaults();

		$this->assertCount( 4, $stores );
		$this->assertSame( 'sydney-cbd', $stores[0]['id'] );
		$this->assertSame( 'NSW', $stores[0]['state'] );
	}

	public function test_sort_by_distance_orders_nearest_first(): void {
		update_option( StoreLocatorModel::OPTION_KEY, $this->model->get_defaults(), false );

		$sorted = $this->model->sort_by_distance( -33.8688, 151.2093 );

		$this->assertSame( 'sydney-cbd', $sorted[0]['id'] );
		$this->assertArrayHasKey( 'distance_metres', $sorted[0] );
		$this->assertLessThan( 5000, $sorted[0]['distance_metres'] );
	}

	public function test_get_by_id_returns_store(): void {
		$store = $this->model->get_by_id( 'perth-cbd' );

		$this->assertNotNull( $store );
		$this->assertSame( 'Perth CBD', $store['name'] );
	}

	public function test_seed_defaults_if_empty_writes_option(): void {
		$this->model->seed_defaults_if_empty();

		$stored = get_option( StoreLocatorModel::OPTION_KEY );
		$this->assertIsArray( $stored );
		$this->assertCount( 4, $stored );
	}
}
