<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Integration\Commerce;

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters\InventoryRestPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters\StoreLocatorRestPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Tests\TestCase;

final class CommerceModulesCompatibilityTest extends TestCase {

	public function test_inventory_stores_endpoint_uses_store_locator_ids(): void {
		$stores = new StoreLocatorModel();
		$stores->seed_defaults_if_empty();

		$inventory = new InventoryModel( $stores );
		$inventory->save_levels_for_product(
			101,
			array(
				'sydney-cbd'   => 3,
				'perth-cbd'    => 1,
				'melbourne-cbd'=> 0,
			)
		);

		$presenter = new InventoryRestPresenter( $inventory );
		$presenter->register_routes();

		$request  = new \WP_REST_Request( 'GET', '/mhdk/v1/inventory/101/stores' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertCount( 2, $data );
		$this->assertSame( 'sydney-cbd', $data[0]['id'] );
		$this->assertSame( 'Sydney CBD', $data[0]['name'] );
		$this->assertSame( 3, $data[0]['stock_quantity'] );
	}

	public function test_nearby_stores_and_inventory_share_store_catalog(): void {
		$stores   = new StoreLocatorModel();
		$settings = new StoreLocatorSettingsModel();
		$stores->seed_defaults_if_empty();

		$locator_presenter = new StoreLocatorRestPresenter( $stores, $settings );
		$locator_presenter->register_routes();

		$nearby_request = new \WP_REST_Request( 'GET', '/mhdk/v1/stores/nearby' );
		$nearby_request->set_param( 'lat', -37.8136 );
		$nearby_request->set_param( 'lng', 144.9631 );
		$nearby = rest_get_server()->dispatch( $nearby_request )->get_data();

		$inventory = new InventoryModel( $stores );
		$levels    = array();

		foreach ( $nearby as $index => $store ) {
			$levels[ (string) $store['id'] ] = 5 - $index;
		}

		$inventory->save_levels_for_product( 202, $levels );

		$stock_request = new \WP_REST_Request( 'GET', '/mhdk/v1/inventory/202/stores' );
		$stock_data    = rest_get_server()->dispatch( $stock_request )->get_data();

		$this->assertNotEmpty( $stock_data );
		$this->assertSame( $nearby[0]['id'], $stock_data[0]['id'] );
	}
}
