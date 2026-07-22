<?php
/**
 * Per-store product stock levels.
 *
 * @package MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models;

use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;

final class InventoryModel {

	public const OPTION_KEY = 'mhdk_inventory_levels';

	public function __construct(
		private ?StoreLocatorModel $stores = null
	) {
		$this->stores = $stores ?? new StoreLocatorModel();
	}

	/**
	 * @return array<string, array<string, int>>
	 */
	public function get_all(): array {
		$stored = get_option( self::OPTION_KEY, array() );

		return is_array( $stored ) ? $stored : array();
	}

	public function get_stock( int $product_id, string $store_id ): int {
		$all = $this->get_all();
		$key = $this->product_key( $product_id );

		return (int) ( $all[ $key ][ $store_id ] ?? 0 );
	}

	public function set_stock( int $product_id, string $store_id, int $quantity ): void {
		$all = $this->get_all();
		$key = $this->product_key( $product_id );

		if ( ! isset( $all[ $key ] ) || ! is_array( $all[ $key ] ) ) {
			$all[ $key ] = array();
		}

		$all[ $key ][ sanitize_key( $store_id ) ] = max( 0, $quantity );

		update_option( self::OPTION_KEY, $all, false );
	}

	/**
	 * @return array<string, int> store_id => quantity
	 */
	public function get_levels_for_product( int $product_id ): array {
		$all = $this->get_all();
		$key = $this->product_key( $product_id );
		$row = $all[ $key ] ?? array();

		if ( ! is_array( $row ) ) {
			return array();
		}

		$clean = array();

		foreach ( $row as $store_id => $qty ) {
			$clean[ (string) $store_id ] = max( 0, (int) $qty );
		}

		return $clean;
	}

	public function total_stock( int $product_id ): int {
		return array_sum( $this->get_levels_for_product( $product_id ) );
	}

	/**
	 * @return array<int, array<string, mixed>> Active stores that have stock for the product.
	 */
	public function get_stores_with_stock( int $product_id, int $minimum = 1 ): array {
		$levels = $this->get_levels_for_product( $product_id );
		$result = array();

		foreach ( $this->stores->get_active() as $store ) {
			$store_id = (string) $store['id'];
			$qty      = (int) ( $levels[ $store_id ] ?? 0 );

			if ( $qty >= $minimum ) {
				$store['stock_quantity'] = $qty;
				$result[]                = $store;
			}
		}

		return $result;
	}

	/**
	 * @param array<string, int> $levels store_id => quantity
	 */
	public function save_levels_for_product( int $product_id, array $levels ): void {
		$all = $this->get_all();
		$key = $this->product_key( $product_id );
		$clean = array();

		foreach ( $levels as $store_id => $qty ) {
			if ( null === $this->stores->get_by_id( (string) $store_id ) ) {
				continue;
			}

			$clean[ sanitize_key( (string) $store_id ) ] = max( 0, (int) $qty );
		}

		$all[ $key ] = $clean;
		update_option( self::OPTION_KEY, $all, false );
	}

	/**
	 * Seed demo stock for the first WooCommerce product across all stores.
	 */
	public function seed_demo_stock_if_empty(): void {
		if ( ! empty( $this->get_all() ) || ! function_exists( 'wc_get_products' ) ) {
			return;
		}

		$products = wc_get_products(
			array(
				'limit'   => 1,
				'status'  => 'publish',
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		if ( array() === $products ) {
			return;
		}

		$product = $products[0];
		$levels  = array();

		foreach ( $this->stores->get_active() as $index => $store ) {
			$levels[ (string) $store['id'] ] = 10 - ( $index * 2 );
		}

		$this->save_levels_for_product( (int) $product->get_id(), $levels );
	}

	private function product_key( int $product_id ): string {
		return 'product_' . max( 0, $product_id );
	}
}
