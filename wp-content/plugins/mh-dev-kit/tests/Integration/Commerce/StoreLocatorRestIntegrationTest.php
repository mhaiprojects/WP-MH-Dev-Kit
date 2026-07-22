<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Integration\Commerce;

use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters\StoreLocatorRestPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Tests\TestCase;

final class StoreLocatorRestIntegrationTest extends TestCase {

	public function test_nearby_rest_route_returns_sorted_stores(): void {
		$stores   = new StoreLocatorModel();
		$settings = new StoreLocatorSettingsModel();
		$stores->seed_defaults_if_empty();

		$presenter = new StoreLocatorRestPresenter( $stores, $settings );
		$presenter->register_routes();

		$request = new \WP_REST_Request( 'GET', '/mhdk/v1/stores/nearby' );
		$request->set_param( 'lat', -33.8688 );
		$request->set_param( 'lng', 151.2093 );

		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data );
		$this->assertSame( 'sydney-cbd', $data[0]['id'] );
	}
}
