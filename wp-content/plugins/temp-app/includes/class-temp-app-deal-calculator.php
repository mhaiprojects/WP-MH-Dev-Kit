<?php
/**
 * Pure mix-and-match deal calculator (no WordPress / WooCommerce dependencies).
 *
 * @package TempApp
 */

declare(strict_types=1);

final class Temp_App_Deal_Calculator {

	/**
	 * Convert a decimal price to integer cents.
	 */
	public static function to_cents( float|string $amount ): int {
		return (int) round( (float) $amount * 100 );
	}

	/**
	 * @param list<array{key:string,product_id:int,unit_price_cents:int,qty:int,tags:list<string>}> $lines    Cart lines in order.
	 * @param list<array{tag:string,qty:int,price_cents:int,label:string}>                         $bundles  Deal configs in order.
	 * @return array{deals:list<array<string,mixed>>,unassigned_keys:list<string>}
	 */
	public static function calculate( array $lines, array $bundles ): array {
		if ( array() === $lines && array() === $bundles ) {
			return array(
				'deals'            => array(),
				'unassigned_keys'  => array(),
			);
		}

		/** @var list<array{key:string,product_id:int,unit_price_cents:int,qty:int,tags:list<string>,line_index:int}> $eligible */
		$eligible = array();
		foreach ( $lines as $line_index => $line ) {
			$qty = isset( $line['qty'] ) ? (int) $line['qty'] : 0;
			$unit = isset( $line['unit_price_cents'] ) ? (int) $line['unit_price_cents'] : 0;
			if ( $qty < 1 || $unit <= 0 ) {
				continue;
			}
			$key = isset( $line['key'] ) ? (string) $line['key'] : '';
			if ( '' === $key ) {
				continue;
			}
			$eligible[] = array(
				'key'                => $key,
				'product_id'         => isset( $line['product_id'] ) ? (int) $line['product_id'] : 0,
				'unit_price_cents'   => $unit,
				'qty'                => $qty,
				'tags'               => isset( $line['tags'] ) && is_array( $line['tags'] ) ? array_values( $line['tags'] ) : array(),
				'line_index'         => $line_index,
			);
		}

		/** @var array<string,string> $line_bundle tag assigned per cart line key */
		$line_bundle = array();
		foreach ( $eligible as $row ) {
			foreach ( $bundles as $bundle ) {
				$tag = isset( $bundle['tag'] ) ? (string) $bundle['tag'] : '';
				if ( '' === $tag ) {
					continue;
				}
				if ( in_array( $tag, $row['tags'], true ) ) {
					$line_bundle[ $row['key'] ] = $tag;
					break;
				}
			}
		}

		$unassigned_keys = array();
		foreach ( $eligible as $row ) {
			if ( ! isset( $line_bundle[ $row['key'] ] ) ) {
				$unassigned_keys[] = $row['key'];
			}
		}

		$deals = array();
		foreach ( $bundles as $bundle ) {
			$tag         = isset( $bundle['tag'] ) ? (string) $bundle['tag'] : '';
			$n           = isset( $bundle['qty'] ) ? (int) $bundle['qty'] : 0;
			$price_cents = isset( $bundle['price_cents'] ) ? (int) $bundle['price_cents'] : 0;
			$label       = isset( $bundle['label'] ) ? (string) $bundle['label'] : '';
			if ( '' === $tag || $n < 2 || $price_cents <= 0 ) {
				continue;
			}

			/** @var list<array{key:string,product_id:int,unit_price_cents:int,line_index:int}> $units */
			$units = array();
			foreach ( $eligible as $row ) {
				if ( ( $line_bundle[ $row['key'] ] ?? '' ) !== $tag ) {
					continue;
				}
				for ( $u = 0; $u < $row['qty']; $u++ ) {
					$units[] = array(
						'key'              => $row['key'],
						'product_id'       => $row['product_id'],
						'unit_price_cents' => $row['unit_price_cents'],
						'line_index'       => $row['line_index'],
					);
				}
			}

			if ( array() === $units ) {
				continue;
			}

			usort(
				$units,
				static function ( array $a, array $b ): int {
					if ( $a['unit_price_cents'] !== $b['unit_price_cents'] ) {
						return $b['unit_price_cents'] <=> $a['unit_price_cents'];
					}
					return $a['line_index'] <=> $b['line_index'];
				}
			);

			$set_count      = intdiv( count( $units ), $n );
			$sets           = array();
			$deal_saving    = 0;
			for ( $s = 0; $s < $set_count; $s++ ) {
				$slice          = array_slice( $units, $s * $n, $n );
				$regular_cents  = self::sum_unit_prices( $slice );
				$saving_cents   = max( 0, $regular_cents - $price_cents );
				$deal_saving   += $saving_cents;
				$sets[]         = array(
					'index'         => $s + 1,
					'items'         => self::aggregate_consecutive_units( $slice ),
					'regular_cents' => $regular_cents,
					'saving_cents'  => $saving_cents,
				);
			}

			$leftover_units = array_slice( $units, $set_count * $n );
			$leftover_qty   = count( $leftover_units );
			$need           = $leftover_qty > 0 ? $n - $leftover_qty : 0;

			$deals[] = array(
				'tag'           => $tag,
				'label'         => $label,
				'qty'           => $n,
				'price_cents'   => $price_cents,
				'sets'          => $sets,
				'leftover'      => self::aggregate_consecutive_units( $leftover_units ),
				'leftover_qty'  => $leftover_qty,
				'need'          => $need,
				'saving_cents'  => $deal_saving,
			);
		}

		if ( array() === $eligible && array() === $bundles ) {
			return array(
				'deals'           => array(),
				'unassigned_keys' => array(),
			);
		}

		return array(
			'deals'           => $deals,
			'unassigned_keys' => $unassigned_keys,
		);
	}

	/**
	 * @param list<array{unit_price_cents:int}> $units Units.
	 */
	private static function sum_unit_prices( array $units ): int {
		$sum = 0;
		foreach ( $units as $unit ) {
			$sum += (int) $unit['unit_price_cents'];
		}
		return $sum;
	}

	/**
	 * @param list<array{key:string,product_id:int,unit_price_cents:int}> $units Units in set order.
	 * @return list<array{key:string,product_id:int,qty:int,unit_price_cents:int}>
	 */
	private static function aggregate_consecutive_units( array $units ): array {
		if ( array() === $units ) {
			return array();
		}

		$items = array();
		foreach ( $units as $unit ) {
			$last_index = count( $items ) - 1;
			if ( $last_index >= 0
				&& $items[ $last_index ]['key'] === $unit['key']
				&& $items[ $last_index ]['unit_price_cents'] === $unit['unit_price_cents']
			) {
				$items[ $last_index ]['qty']++;
				continue;
			}
			$items[] = array(
				'key'              => $unit['key'],
				'product_id'       => $unit['product_id'],
				'qty'              => 1,
				'unit_price_cents' => $unit['unit_price_cents'],
			);
		}

		return $items;
	}
}