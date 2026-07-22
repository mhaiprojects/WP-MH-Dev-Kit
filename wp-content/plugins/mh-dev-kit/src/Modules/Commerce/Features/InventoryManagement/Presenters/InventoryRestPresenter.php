<?php
/**
 * Inventory REST API.
 *
 * @package MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters;

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;

final class InventoryRestPresenter {

	public function __construct(
		private InventoryModel $inventory
	) {}

	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			'mhdk/v1',
			'/inventory/(?P<product_id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_product_inventory' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'mhdk/v1',
			'/inventory/(?P<product_id>\d+)/stores',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stores_with_stock' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function get_product_inventory( \WP_REST_Request $request ): \WP_REST_Response {
		$product_id = (int) $request->get_param( 'product_id' );

		return new \WP_REST_Response(
			array(
				'product_id'  => $product_id,
				'levels'      => $this->inventory->get_levels_for_product( $product_id ),
				'total_stock' => $this->inventory->total_stock( $product_id ),
			),
			200
		);
	}

	public function get_stores_with_stock( \WP_REST_Request $request ): \WP_REST_Response {
		$product_id = (int) $request->get_param( 'product_id' );
		$stores     = $this->inventory->get_stores_with_stock( $product_id );

		$payload = array_map(
			static fn( array $store ): array => array(
				'id'             => (string) $store['id'],
				'name'           => (string) $store['name'],
				'city'           => (string) $store['city'],
				'state'          => (string) $store['state'],
				'stock_quantity' => (int) $store['stock_quantity'],
			),
			$stores
		);

		return new \WP_REST_Response( $payload, 200 );
	}
}
