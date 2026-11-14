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
		/** @var array<string,list<string>> $line_tags */
		$line_tags = array();
		foreach ( $eligible as $row ) {
			$line_tags[ $row['key'] ] = $row['tags'];
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

		/** @var array<string,array{tag:string,label:string,n:int,price_cents:int,sets:list<array<string,mixed>>,leftovers:list<array<string,mixed>>}> $deal_state */
		$deal_state = array();

		foreach ( $bundles as $bundle ) {
			$tag         = isset( $bundle['tag'] ) ? (string) $bundle['tag'] : '';
			$n           = isset( $bundle['qty'] ) ? (int) $bundle['qty'] : 0;
			$price_cents = isset( $bundle['price_cents'] ) ? (int) $bundle['price_cents'] : 0;
			$label       = isset( $bundle['label'] ) ? (string) $bundle['label'] : '';
			if ( '' === $tag || $n < 2 || $price_cents <= 0 ) {
				continue;
			}

			/** @var list<array{key:string,product_id:int,unit_price_cents:int,line_index:int,primary_tag:string}> $units */
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
						'primary_tag'      => $tag,
					);
				}
			}

			// Deals with no primary units are still kept so leftovers of other deals can be reassigned into them.
			usort( $units, array( __CLASS__, 'compare_units' ) );

			$set_count = intdiv( count( $units ), $n );
			$sets      = array();
			for ( $s = 0; $s < $set_count; $s++ ) {
				$slice = array_slice( $units, $s * $n, $n );
				$sets[] = self::build_set( $slice, $price_cents, false );
			}

			$leftovers = array_slice( $units, $set_count * $n );

			$deal_state[ $tag ] = array(
				'tag'         => $tag,
				'label'       => $label,
				'n'           => $n,
				'price_cents' => $price_cents,
				'sets'        => $sets,
				'leftovers'   => $leftovers,
			);
		}

		$bundle_order = array();
		foreach ( $bundles as $bundle ) {
			$tag = isset( $bundle['tag'] ) ? (string) $bundle['tag'] : '';
			if ( '' !== $tag && isset( $deal_state[ $tag ] ) ) {
				$bundle_order[] = $tag;
			}
		}

		$labels_by_tag = array();
		foreach ( $deal_state as $tag => $state ) {
			$labels_by_tag[ $tag ] = $state['label'];
		}

		$changed = true;
		while ( $changed ) {
			$changed = false;
			foreach ( $bundle_order as $target_tag ) {
				if ( ! isset( $deal_state[ $target_tag ] ) ) {
					continue;
				}
				$target = &$deal_state[ $target_tag ];
				$n      = $target['n'];

				while ( true ) {
					/** @var list<array{unit:array<string,mixed>,source_tag:string,pool_index:int}> $pool_entries */
					$pool_entries = array();

					foreach ( $target['leftovers'] as $unit ) {
						$pool_entries[] = array(
							'unit'       => $unit,
							'source_tag' => $target_tag,
							'pool_index' => count( $pool_entries ),
						);
					}

					foreach ( $deal_state as $source_tag => $source_state ) {
						if ( $source_tag === $target_tag ) {
							continue;
						}
						foreach ( $source_state['leftovers'] as $unit ) {
							$key  = $unit['key'];
							$tags = $line_tags[ $key ] ?? array();
							if ( ! in_array( $target_tag, $tags, true ) ) {
								continue;
							}
							$pool_entries[] = array(
								'unit'       => $unit,
								'source_tag' => $source_tag,
								'pool_index' => count( $pool_entries ),
							);
						}
					}

					if ( count( $pool_entries ) < $n ) {
						break;
					}

					usort(
						$pool_entries,
						static function ( array $a, array $b ): int {
							return self::compare_units( $a['unit'], $b['unit'] );
						}
					);

					$slice_entries = array_slice( $pool_entries, 0, $n );
					$has_foreign   = false;
					$reassigned_keys = array();
					foreach ( $slice_entries as $entry ) {
						if ( $entry['source_tag'] !== $target_tag ) {
							$has_foreign = true;
							$reassigned_keys[] = $entry['unit']['key'];
						} elseif ( $entry['unit']['primary_tag'] !== $target_tag ) {
							$has_foreign = true;
							$reassigned_keys[] = $entry['unit']['key'];
						}
					}

					if ( ! $has_foreign ) {
						break;
					}

					$reassigned_keys = array_values( array_unique( $reassigned_keys ) );

					$from_labels = array();
					foreach ( $slice_entries as $entry ) {
						if ( $entry['source_tag'] === $target_tag ) {
							continue;
						}
						$primary = isset( $entry['unit']['primary_tag'] ) ? (string) $entry['unit']['primary_tag'] : $entry['source_tag'];
						if ( isset( $labels_by_tag[ $primary ] ) ) {
							$from_labels[ $primary ] = $labels_by_tag[ $primary ];
						}
					}

					$slice_units = array_map(
						static fn( array $entry ): array => $entry['unit'],
						$slice_entries
					);
					$new_set = self::build_set(
						$slice_units,
						$target['price_cents'],
						true,
						$reassigned_keys,
						array_values( $from_labels )
					);
					$target['sets'][] = $new_set;

					foreach ( $slice_entries as $entry ) {
						self::remove_one_leftover_unit( $deal_state[ $entry['source_tag'] ]['leftovers'], $entry['unit'] );
					}

					$changed = true;
				}
				unset( $target );
			}
		}

		$deals = array();
		foreach ( $bundles as $bundle ) {
			$tag = isset( $bundle['tag'] ) ? (string) $bundle['tag'] : '';
			if ( '' === $tag || ! isset( $deal_state[ $tag ] ) ) {
				continue;
			}

			$state = $deal_state[ $tag ];
			if ( array() === $state['sets'] && array() === $state['leftovers'] ) {
				continue;
			}
			$n     = $state['n'];
			$sets  = array();
			$deal_saving = 0;
			$set_index   = 0;
			foreach ( $state['sets'] as $set ) {
				++$set_index;
				$deal_saving += (int) $set['saving_cents'];
				$sets[] = array_merge(
					$set,
					array( 'index' => $set_index )
				);
			}

			$leftover_qty = count( $state['leftovers'] );
			$need         = $leftover_qty > 0 ? $n - $leftover_qty : 0;

			$deals[] = array(
				'tag'           => $tag,
				'label'         => $state['label'],
				'qty'           => $n,
				'price_cents'   => $state['price_cents'],
				'sets'          => $sets,
				'leftover'      => self::aggregate_consecutive_units( $state['leftovers'] ),
				'leftover_qty'  => $leftover_qty,
				'need'          => $need,
				'saving_cents'  => $deal_saving,
			);
		}

		if ( array() === $eligible && array() === $bundles ) {
			return array(
				'deals'           => array(),
				'unassigned_keys' => $unassigned_keys,
			);
		}

		return array(
			'deals'           => $deals,
			'unassigned_keys' => $unassigned_keys,
		);
	}

	/**
	 * @param array{unit_price_cents:int,line_index:int} $a First unit.
	 * @param array{unit_price_cents:int,line_index:int} $b Second unit.
	 */
	private static function compare_units( array $a, array $b ): int {
		if ( $a['unit_price_cents'] !== $b['unit_price_cents'] ) {
			return $b['unit_price_cents'] <=> $a['unit_price_cents'];
		}
		return $a['line_index'] <=> $b['line_index'];
	}

	/**
	 * @param list<array{key:string,product_id:int,unit_price_cents:int,primary_tag?:string}> $slice Units in set.
	 * @param list<string> $reassigned_keys Cart keys of foreign units.
	 * @param list<string> $reassigned_from_labels Primary deal labels for foreign units.
	 * @return array{index:int,items:list<array<string,mixed>>,regular_cents:int,saving_cents:int,reassigned_keys:list<string>,reassigned_from_label:string}
	 */
	private static function build_set(
		array $slice,
		int $price_cents,
		bool $is_reassigned,
		array $reassigned_keys = array(),
		array $reassigned_from_labels = array()
	): array {
		$regular_cents = self::sum_unit_prices( $slice );
		$saving_cents  = max( 0, $regular_cents - $price_cents );
		$from_label    = '';
		if ( $is_reassigned && array() !== $reassigned_from_labels ) {
			$from_label = implode( ', ', array_values( array_unique( $reassigned_from_labels ) ) );
		}

		return array(
			'index'                   => 0,
			'items'                   => self::aggregate_consecutive_units( $slice ),
			'regular_cents'           => $regular_cents,
			'saving_cents'            => $saving_cents,
			'reassigned_keys'         => $is_reassigned ? $reassigned_keys : array(),
			'reassigned_from_label'   => $from_label,
		);
	}

	/**
	 * @param list<array{key:string,product_id:int,unit_price_cents:int,line_index:int,primary_tag:string}> $leftovers Leftover pool.
	 * @param array{key:string,product_id:int,unit_price_cents:int,line_index:int}                           $unit      Unit to remove.
	 */
	private static function remove_one_leftover_unit( array &$leftovers, array $unit ): void {
		foreach ( $leftovers as $i => $candidate ) {
			if ( $candidate['key'] !== $unit['key'] ) {
				continue;
			}
			if ( $candidate['unit_price_cents'] !== $unit['unit_price_cents'] ) {
				continue;
			}
			if ( $candidate['line_index'] !== $unit['line_index'] ) {
				continue;
			}
			array_splice( $leftovers, $i, 1 );
			return;
		}
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