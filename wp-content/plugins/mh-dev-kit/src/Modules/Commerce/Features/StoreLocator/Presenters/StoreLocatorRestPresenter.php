<?php
/**
 * Store Locator REST API.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters;

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;

final class StoreLocatorRestPresenter {

	public function __construct(
		private StoreLocatorModel $stores,
		private StoreLocatorSettingsModel $settings,
		private ?InventoryModel $inventory = null
	) {}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'mhdk/v1',
			'/stores',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_stores' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'mhdk/v1',
			'/stores/nearby',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'stores_nearby' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'lat' => array(
						'required'          => true,
						'type'              => 'number',
						'sanitize_callback' => static fn( $value ): float => (float) $value,
					),
					'lng' => array(
						'required'          => true,
						'type'              => 'number',
						'sanitize_callback' => static fn( $value ): float => (float) $value,
					),
					'product_id' => array(
						'required'          => false,
						'type'              => 'integer',
						'sanitize_callback' => static fn( $value ): int => (int) $value,
					),
				),
			)
		);

		register_rest_route(
			'mhdk/v1',
			'/stores/(?P<id>[a-z0-9-]+)/distance',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'store_distance' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'lat' => array(
						'required'          => true,
						'type'              => 'number',
						'sanitize_callback' => static fn( $value ): float => (float) $value,
					),
					'lng' => array(
						'required'          => true,
						'type'              => 'number',
						'sanitize_callback' => static fn( $value ): float => (float) $value,
					),
				),
			)
		);
	}

	public function list_stores(): \WP_REST_Response {
		$stores = array_map(
			fn( array $store ): array => $this->public_store( $store ),
			$this->stores->get_active()
		);

		return new \WP_REST_Response( $stores, 200 );
	}

	public function stores_nearby( \WP_REST_Request $request ): \WP_REST_Response {
		$lat        = (float) $request->get_param( 'lat' );
		$lng        = (float) $request->get_param( 'lng' );
		$product_id = (int) $request->get_param( 'product_id' );

		$max_metres = $this->settings->get_max_distance_metres();
		$sorted     = $this->stores->sort_by_distance( $lat, $lng );

		$payload = array();

		foreach ( $sorted as $store ) {
			$row = $this->public_store( $store, true );
			$row['within_radius'] = (float) $store['distance_metres'] <= $max_metres;

			if ( $product_id > 0 && null !== $this->inventory ) {
				$stock                   = $this->inventory->get_stock( $product_id, (string) $store['id'] );
				$row['stock_quantity']   = $stock;
				$row['has_stock']        = $stock > 0;
			}

			$payload[] = $row;
		}

		return new \WP_REST_Response( $payload, 200 );
	}

	public function store_distance( \WP_REST_Request $request ): \WP_REST_Response {
		$store_id = (string) $request->get_param( 'id' );
		$lat      = (float) $request->get_param( 'lat' );
		$lng      = (float) $request->get_param( 'lng' );
		$store    = $this->stores->get_by_id( $store_id );

		if ( null === $store ) {
			return new \WP_REST_Response( array( 'message' => 'Store not found.' ), 404 );
		}

		$metres = $this->stores->distance_to_store( $lat, $lng, $store_id );

		return new \WP_REST_Response(
			array(
				'store'           => $this->public_store( $store ),
				'distance_metres' => round( (float) $metres ),
				'distance_km'     => round( (float) $metres / 1000, 1 ),
			),
			200
		);
	}

	/**
	 * @param array<string, mixed> $store Store row.
	 * @return array<string, mixed>
	 */
	private function public_store( array $store, bool $include_distance = false ): array {
		$row = array(
			'id'      => (string) $store['id'],
			'name'    => (string) $store['name'],
			'address' => $this->stores->format_address( $store ),
			'city'    => (string) $store['city'],
			'state'   => (string) $store['state'],
			'lat'     => (float) $store['lat'],
			'lng'     => (float) $store['lng'],
			'phone'   => (string) $store['phone'],
			'hours'   => (string) $store['hours'],
		);

		if ( $include_distance ) {
			$row['distance_metres'] = (int) ( $store['distance_metres'] ?? 0 );
			$row['distance_km']     = (float) ( $store['distance_km'] ?? 0 );
		}

		return $row;
	}
}
