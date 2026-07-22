<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Commerce;

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Tests\TestCase;

final class InventoryModelTest extends TestCase {

	private InventoryModel $inventory;

	protected function setUp(): void {
		parent::setUp();
		delete_option( InventoryModel::OPTION_KEY );
		$this->inventory = new InventoryModel( new StoreLocatorModel() );
	}

	public function test_set_and_get_stock_per_store(): void {
		$this->inventory->set_stock( 42, 'sydney-cbd', 7 );

		$this->assertSame( 7, $this->inventory->get_stock( 42, 'sydney-cbd' ) );
		$this->assertSame( 0, $this->inventory->get_stock( 42, 'melbourne-cbd' ) );
	}

	public function test_total_stock_sums_all_stores(): void {
		$this->inventory->save_levels_for_product(
			99,
			array(
				'sydney-cbd'    => 4,
				'melbourne-cbd' => 6,
			)
		);

		$this->assertSame( 10, $this->inventory->total_stock( 99 ) );
	}

	public function test_get_stores_with_stock_links_to_store_locator(): void {
		update_option( StoreLocatorModel::OPTION_KEY, ( new StoreLocatorModel() )->get_defaults(), false );

		$this->inventory->save_levels_for_product(
			5,
			array(
				'sydney-cbd'    => 2,
				'melbourne-cbd' => 0,
				'brisbane-cbd'  => 5,
			)
		);

		$stores = $this->inventory->get_stores_with_stock( 5 );

		$this->assertCount( 2, $stores );
		$this->assertSame( 'sydney-cbd', $stores[0]['id'] );
		$this->assertSame( 2, $stores[0]['stock_quantity'] );
	}
}
