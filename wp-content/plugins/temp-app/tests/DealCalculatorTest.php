<?php
/**
 * @package TempApp
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DealCalculatorTest extends TestCase {

	private const ANY_3 = array(
		'tag'         => 'any-3',
		'qty'         => 3,
		'price_cents' => 700,
		'label'       => 'Any 3 for $7',
	);

	private const ANY_4 = array(
		'tag'         => 'any-4',
		'qty'         => 4,
		'price_cents' => 500,
		'label'       => 'Any 4 for $5',
	);

	/**
	 * @return array{deals:list<array<string,mixed>>,unassigned_keys:list<string>}
	 */
	private function calc( array $lines, array $bundles ): array {
		return Temp_App_Deal_Calculator::calculate( $lines, $bundles );
	}

	private function deal_savings( array $result, string $tag ): array {
		foreach ( $result['deals'] as $deal ) {
			if ( $deal['tag'] === $tag ) {
				return array_map(
					static fn( array $set ): int => (int) $set['saving_cents'],
					$deal['sets']
				);
			}
		}
		return array();
	}

	private function deal_total_saving( array $result, string $tag ): int {
		foreach ( $result['deals'] as $deal ) {
			if ( $deal['tag'] === $tag ) {
				return (int) $deal['saving_cents'];
			}
		}
		return 0;
	}

	public function test_exact_three_units_one_set(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 3,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertCount( 1, $r['deals'][0]['sets'] );
		$this->assertSame( 200, $r['deals'][0]['sets'][0]['saving_cents'] );
		$this->assertSame( 0, $r['deals'][0]['leftover_qty'] );
	}

	public function test_exact_six_units_two_sets(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 6,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertCount( 2, $r['deals'][0]['sets'] );
		$this->assertSame( 400, $r['deals'][0]['saving_cents'] );
	}

	public function test_four_units_one_set_one_leftover(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 4,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertCount( 1, $r['deals'][0]['sets'] );
		$this->assertSame( 1, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 2, $r['deals'][0]['need'] );
		$this->assertSame( 1, $r['deals'][0]['leftover'][0]['qty'] );
	}

	public function test_five_units_one_set_two_leftovers(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 1,
				'unit_price_cents'   => 250,
				'qty'                => 5,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertCount( 1, $r['deals'][0]['sets'] );
		$this->assertSame( 2, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 1, $r['deals'][0]['need'] );
	}

	public function test_mixed_products_one_deal(): void {
		$lines = array(
			array( 'key' => 'p', 'product_id' => 1, 'unit_price_cents' => 220, 'qty' => 1, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'a', 'product_id' => 2, 'unit_price_cents' => 300, 'qty' => 1, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'o', 'product_id' => 3, 'unit_price_cents' => 300, 'qty' => 1, 'tags' => array( 'any-3' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertSame( 820, $r['deals'][0]['sets'][0]['regular_cents'] );
		$this->assertSame( 120, $r['deals'][0]['sets'][0]['saving_cents'] );
	}

	public function test_same_product_multiple_qty(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 2,
				'unit_price_cents'   => 300,
				'qty'                => 3,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertSame( 200, $r['deals'][0]['sets'][0]['saving_cents'] );
		$this->assertCount( 1, $r['deals'][0]['sets'][0]['items'] );
		$this->assertSame( 3, $r['deals'][0]['sets'][0]['items'][0]['qty'] );
	}

	public function test_dearest_first_selection(): void {
		$lines = array(
			array( 'key' => 'd', 'product_id' => 4, 'unit_price_cents' => 220, 'qty' => 1, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'c', 'product_id' => 3, 'unit_price_cents' => 350, 'qty' => 1, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'b', 'product_id' => 2, 'unit_price_cents' => 300, 'qty' => 1, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'a', 'product_id' => 1, 'unit_price_cents' => 300, 'qty' => 1, 'tags' => array( 'any-3' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertSame( 250, $r['deals'][0]['sets'][0]['saving_cents'] );
		$this->assertSame( 350, $r['deals'][0]['sets'][0]['items'][0]['unit_price_cents'] );
		$this->assertSame( 1, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 220, $r['deals'][0]['leftover'][0]['unit_price_cents'] );
	}

	public function test_set_already_below_deal_price(): void {
		$lines = array(
			array(
				'key'                => 'p',
				'product_id'         => 1,
				'unit_price_cents'   => 220,
				'qty'                => 3,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertSame( 0, $r['deals'][0]['sets'][0]['saving_cents'] );
		$this->assertSame( 0, $r['deals'][0]['saving_cents'] );
	}

	public function test_multiple_deal_types_same_cart(): void {
		$lines = array(
			array( 'key' => 't3', 'product_id' => 1, 'unit_price_cents' => 300, 'qty' => 3, 'tags' => array( 'any-3' ) ),
			array( 'key' => 't4a', 'product_id' => 2, 'unit_price_cents' => 200, 'qty' => 2, 'tags' => array( 'any-4' ) ),
			array( 'key' => 't4b', 'product_id' => 3, 'unit_price_cents' => 150, 'qty' => 2, 'tags' => array( 'any-4' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );
		$this->assertCount( 2, $r['deals'] );
		$this->assertSame( 'any-3', $r['deals'][0]['tag'] );
		$this->assertSame( 'any-4', $r['deals'][1]['tag'] );
		$this->assertCount( 1, $r['deals'][1]['sets'] );
	}

	public function test_all_items_leftover_two_units_any3(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 2,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertCount( 0, $r['deals'][0]['sets'] );
		$this->assertSame( 2, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 0, $r['deals'][0]['saving_cents'] );
	}

	public function test_empty_cart_no_deals(): void {
		$r = $this->calc( array(), array( self::ANY_3 ) );
		$this->assertSame( array(), $r['deals'] );
	}

	public function test_double_tagged_product_first_deal_only(): void {
		$lines = array(
			array(
				'key'                => 'x',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 3,
				'tags'               => array( 'any-3', 'any-4' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );
		$this->assertCount( 1, $r['deals'][0]['sets'] );
		$this->assertCount( 1, $r['deals'] );
	}

	public function test_cents_rounding_and_to_cents(): void {
		$lines = array(
			array(
				'key'                => 'n',
				'product_id'         => 1,
				'unit_price_cents'   => 333,
				'qty'                => 3,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertSame( 299, $r['deals'][0]['sets'][0]['saving_cents'] );
		$this->assertSame( 333, Temp_App_Deal_Calculator::to_cents( '3.33' ) );
		$this->assertSame( 220, Temp_App_Deal_Calculator::to_cents( 2.2 ) );
		$this->assertSame( 10430, Temp_App_Deal_Calculator::to_cents( '104.30' ) );
	}

	public function test_stable_tie_break_by_cart_order(): void {
		$lines = array(
			array( 'key' => 'first', 'product_id' => 1, 'unit_price_cents' => 300, 'qty' => 1, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'second', 'product_id' => 2, 'unit_price_cents' => 300, 'qty' => 1, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'cheap', 'product_id' => 3, 'unit_price_cents' => 100, 'qty' => 1, 'tags' => array( 'any-3' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$item_keys = array_column( $r['deals'][0]['sets'][0]['items'], 'key' );
		$this->assertSame( array( 'first', 'second', 'cheap' ), $item_keys );
	}

	public function test_full_reproduction_cart(): void {
		$lines = array(
			array( 'key' => 'pasta', 'product_id' => 1, 'unit_price_cents' => 220, 'qty' => 6, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'apple', 'product_id' => 2, 'unit_price_cents' => 300, 'qty' => 5, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'orange', 'product_id' => 3, 'unit_price_cents' => 300, 'qty' => 5, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'fruit', 'product_id' => 4, 'unit_price_cents' => 350, 'qty' => 4, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'corn', 'product_id' => 5, 'unit_price_cents' => 150, 'qty' => 2, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'chick', 'product_id' => 6, 'unit_price_cents' => 140, 'qty' => 2, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'spag', 'product_id' => 7, 'unit_price_cents' => 180, 'qty' => 1, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'tom', 'product_id' => 8, 'unit_price_cents' => 220, 'qty' => 1, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'other', 'product_id' => 9, 'unit_price_cents' => 3730, 'qty' => 1, 'tags' => array() ),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );

		$this->assertSame(
			array( 350, 250, 200, 200, 120, 0 ),
			$this->deal_savings( $r, 'any-3' )
		);
		$this->assertSame( 1120, $this->deal_total_saving( $r, 'any-3' ) );
		$any3 = null;
		foreach ( $r['deals'] as $deal ) {
			if ( 'any-3' === $deal['tag'] ) {
				$any3 = $deal;
				break;
			}
		}
		$this->assertNotNull( $any3 );
		$this->assertSame( 2, $any3['leftover_qty'] );
		$this->assertSame( 2, $any3['leftover'][0]['qty'] );
		$this->assertSame( 'pasta', $any3['leftover'][0]['key'] );

		$this->assertSame( array( 200 ), $this->deal_savings( $r, 'any-4' ) );
		$this->assertSame( 200, $this->deal_total_saving( $r, 'any-4' ) );
		$any4 = null;
		foreach ( $r['deals'] as $deal ) {
			if ( 'any-4' === $deal['tag'] ) {
				$any4 = $deal;
				break;
			}
		}
		$this->assertNotNull( $any4 );
		$this->assertSame( 2, $any4['leftover_qty'] );
		$this->assertSame( 'chick', $any4['leftover'][0]['key'] );

		$assigned_keys = array();
		foreach ( $r['deals'] as $deal ) {
			foreach ( $deal['sets'] as $set ) {
				foreach ( $set['items'] as $item ) {
					$assigned_keys[ $item['key'] ] = true;
				}
			}
			foreach ( $deal['leftover'] as $item ) {
				$assigned_keys[ $item['key'] ] = true;
			}
		}

		$other_cents = 0;
		foreach ( $lines as $line ) {
			if ( isset( $assigned_keys[ $line['key'] ] ) ) {
				continue;
			}
			$other_cents += $line['unit_price_cents'] * $line['qty'];
		}
		$this->assertSame( 3730, $other_cents );
		$this->assertContains( 'other', $r['unassigned_keys'] );

		$total_regular = 0;
		foreach ( $lines as $line ) {
			$total_regular += $line['unit_price_cents'] * $line['qty'];
		}
		$this->assertSame( 10430, $total_regular );
		$this->assertSame( 9110, $total_regular - 1120 - 200 );
	}

	public function test_multiple_deal_types_exact_savings(): void {
		$lines = array(
			array( 'key' => 't3', 'product_id' => 1, 'unit_price_cents' => 300, 'qty' => 3, 'tags' => array( 'any-3' ) ),
			array( 'key' => 't4a', 'product_id' => 2, 'unit_price_cents' => 200, 'qty' => 2, 'tags' => array( 'any-4' ) ),
			array( 'key' => 't4b', 'product_id' => 3, 'unit_price_cents' => 150, 'qty' => 2, 'tags' => array( 'any-4' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );
		$this->assertSame( array( 200 ), $this->deal_savings( $r, 'any-3' ) );
		$this->assertSame( array( 200 ), $this->deal_savings( $r, 'any-4' ) ); // 700 - 500.
		$this->assertSame( 0, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 0, $r['deals'][1]['leftover_qty'] );
	}

	public function test_double_tagged_units_not_counted_in_second_deal(): void {
		$lines = array(
			array( 'key' => 'x', 'product_id' => 1, 'unit_price_cents' => 300, 'qty' => 3, 'tags' => array( 'any-3', 'any-4' ) ),
			array( 'key' => 'y', 'product_id' => 2, 'unit_price_cents' => 200, 'qty' => 1, 'tags' => array( 'any-4' ) ),
		);
		$r   = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );
		$this->assertSame( array( 200 ), $this->deal_savings( $r, 'any-3' ) );
		// If x were double counted, any-4 would have 4 units and a set; it must have only y.
		$this->assertSame( array(), $this->deal_savings( $r, 'any-4' ) );
		$this->assertSame( 1, $r['deals'][1]['leftover_qty'] );
		$this->assertSame( 'y', $r['deals'][1]['leftover'][0]['key'] );
		$this->assertSame( 3, $r['deals'][1]['need'] );
	}

	public function test_units_conserved_and_leftovers_are_cheapest(): void {
		$lines = array(
			array( 'key' => 'a', 'product_id' => 1, 'unit_price_cents' => 120, 'qty' => 4, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'b', 'product_id' => 2, 'unit_price_cents' => 410, 'qty' => 2, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'c', 'product_id' => 3, 'unit_price_cents' => 275, 'qty' => 1, 'tags' => array( 'any-3' ) ),
		);
		$r    = $this->calc( $lines, array( self::ANY_3 ) );
		$deal = $r['deals'][0];
		$units = $deal['leftover_qty'];
		$max_leftover = 0;
		foreach ( $deal['leftover'] as $it ) {
			$max_leftover = max( $max_leftover, $it['unit_price_cents'] );
		}
		$min_in_sets = PHP_INT_MAX;
		foreach ( $deal['sets'] as $set ) {
			foreach ( $set['items'] as $it ) {
				$units      += $it['qty'];
				$min_in_sets = min( $min_in_sets, $it['unit_price_cents'] );
			}
		}
		$this->assertSame( 7, $units );
		$this->assertCount( 2, $deal['sets'] );
		$this->assertLessThanOrEqual( $min_in_sets, $max_leftover );
		// Set 1: 410+410+275 = 1095 -> 395; set 2: 120*3 = 360 -> 0.
		$this->assertSame( array( 395, 0 ), $this->deal_savings( $r, 'any-3' ) );
		$this->assertSame( 395, $deal['saving_cents'] );
	}

	public function test_full_reproduction_cart_set_members(): void {
		$lines = array(
			array( 'key' => 'pasta', 'product_id' => 1, 'unit_price_cents' => 220, 'qty' => 6, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'apple', 'product_id' => 2, 'unit_price_cents' => 300, 'qty' => 5, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'orange', 'product_id' => 3, 'unit_price_cents' => 300, 'qty' => 5, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'fruit', 'product_id' => 4, 'unit_price_cents' => 350, 'qty' => 4, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'corn', 'product_id' => 5, 'unit_price_cents' => 150, 'qty' => 2, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'chick', 'product_id' => 6, 'unit_price_cents' => 140, 'qty' => 2, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'spag', 'product_id' => 7, 'unit_price_cents' => 180, 'qty' => 1, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'tom', 'product_id' => 8, 'unit_price_cents' => 220, 'qty' => 1, 'tags' => array( 'any-4' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );
		$members = static function ( array $set ): array {
			return array_map( static fn( array $i ): string => $i['key'] . 'x' . $i['qty'], $set['items'] );
		};
		$any3 = $r['deals'][0]['sets'];
		$this->assertSame( array( 'fruitx3' ), $members( $any3[0] ) );
		$this->assertSame( array( 'fruitx1', 'applex2' ), $members( $any3[1] ) );
		$this->assertSame( array( 'applex3' ), $members( $any3[2] ) );
		$this->assertSame( array( 'orangex3' ), $members( $any3[3] ) );
		$this->assertSame( array( 'orangex2', 'pastax1' ), $members( $any3[4] ) );
		$this->assertSame( array( 'pastax3' ), $members( $any3[5] ) );
		$this->assertSame( array( 1050, 950, 900, 900, 820, 660 ), array_column( $any3, 'regular_cents' ) );
		$any4 = $r['deals'][1]['sets'];
		$this->assertSame( array( 'tomx1', 'spagx1', 'cornx2' ), $members( $any4[0] ) );
		$this->assertSame( 700, $any4[0]['regular_cents'] );
	}

	public function test_leftover_reassigned_to_second_deal_completes_set(): void {
		$lines = array(
			array(
				'key'                => 'dual',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 4,
				'tags'               => array( 'any-3', 'any-4' ),
			),
			array(
				'key'                => 'b-only',
				'product_id'         => 2,
				'unit_price_cents'   => 250,
				'qty'                => 3,
				'tags'               => array( 'any-4' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );

		$this->assertSame( array( 200 ), $this->deal_savings( $r, 'any-3' ) );
		$this->assertSame( array( 550 ), $this->deal_savings( $r, 'any-4' ) ); // 300+250+250+250=1050 - 500.

		$any3 = $r['deals'][0];
		$this->assertSame( 0, $any3['leftover_qty'] );

		$any4 = $r['deals'][1];
		$this->assertCount( 1, $any4['sets'] );
		$this->assertSame( array( 'dual' ), $any4['sets'][0]['reassigned_keys'] );
		$this->assertSame( 4, array_sum( array_column( $any4['sets'][0]['items'], 'qty' ) ) );

		$this->assert_units_conserved( $lines, $r );
	}

	public function test_leftover_eligible_for_b_but_pool_under_n_stays_with_primary(): void {
		$lines = array(
			array(
				'key'                => 'x',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 4,
				'tags'               => array( 'any-3', 'any-4' ),
			),
			array(
				'key'                => 'y',
				'product_id'         => 2,
				'unit_price_cents'   => 200,
				'qty'                => 1,
				'tags'               => array( 'any-4' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );

		$this->assertSame( 1, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 'x', $r['deals'][0]['leftover'][0]['key'] );
		$this->assertSame( array(), $this->deal_savings( $r, 'any-4' ) );
		$this->assertSame( 1, $r['deals'][1]['leftover_qty'] );
	}

	public function test_single_deal_no_reassignment_four_units(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 1,
				'unit_price_cents'   => 300,
				'qty'                => 4,
				'tags'               => array( 'any-3' ),
			),
		);
		$r = $this->calc( $lines, array( self::ANY_3 ) );
		$this->assertCount( 1, $r['deals'][0]['sets'] );
		$this->assertSame( 1, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( array(), $r['deals'][0]['sets'][0]['reassigned_keys'] );
	}

	public function test_single_deal_no_reassignment_six_units_any_four(): void {
		$lines = array(
			array(
				'key'                => 'a',
				'product_id'         => 1,
				'unit_price_cents'   => 200,
				'qty'                => 6,
				'tags'               => array( 'any-4' ),
			),
		);
		$b = array(
			'tag'         => 'any-4',
			'qty'         => 4,
			'price_cents' => 500,
			'label'       => 'Any 4 for $5',
		);
		$r = $this->calc( $lines, array( $b ) );
		$this->assertCount( 1, $r['deals'][0]['sets'] );
		$this->assertSame( 2, $r['deals'][0]['leftover_qty'] );
	}

	public function test_every_set_item_qty_sums_to_bundle_n(): void {
		$lines = array(
			array( 'key' => 'pasta', 'product_id' => 1, 'unit_price_cents' => 220, 'qty' => 6, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'dual', 'product_id' => 2, 'unit_price_cents' => 300, 'qty' => 4, 'tags' => array( 'any-3', 'any-4' ) ),
			array( 'key' => 'b4', 'product_id' => 3, 'unit_price_cents' => 250, 'qty' => 3, 'tags' => array( 'any-4' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );
		foreach ( $r['deals'] as $deal ) {
			$n = (int) $deal['qty'];
			foreach ( $deal['sets'] as $set ) {
				$sum = 0;
				foreach ( $set['items'] as $item ) {
					$sum += (int) $item['qty'];
				}
				$this->assertSame( $n, $sum, $deal['tag'] );
			}
		}
	}

	public function test_leftover_reassigned_into_deal_with_no_primary_units(): void {
		$lines = array(
			array( 'key' => 'dual', 'product_id' => 1, 'unit_price_cents' => 300, 'qty' => 7, 'tags' => array( 'any-4', 'any-3' ) ),
		);
		// Config order: Any 4 first, so all 7 units are primary to Any 4 (1 set + 3 leftover); the 3 leftovers complete an Any 3 set.
		$r = $this->calc( $lines, array( self::ANY_4, self::ANY_3 ) );
		$this->assertSame( array( 700 ), $this->deal_savings( $r, 'any-4' ) ); // 1200 - 500.
		$this->assertSame( array( 200 ), $this->deal_savings( $r, 'any-3' ) ); // 900 - 700.
		$this->assertSame( 0, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 'any-3', $r['deals'][1]['tag'] );
		$this->assertSame( array( 'dual' ), $r['deals'][1]['sets'][0]['reassigned_keys'] );
		$this->assert_units_conserved( $lines, $r );
	}

	public function test_current_store_cart_unchanged_by_reassignment(): void {
		// Mirrors the on-screen 20-product cart: no product is tagged for more than one deal.
		$lines = array(
			array( 'key' => 'fruit', 'product_id' => 10, 'unit_price_cents' => 350, 'qty' => 4, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'apple', 'product_id' => 16, 'unit_price_cents' => 300, 'qty' => 5, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'orange', 'product_id' => 17, 'unit_price_cents' => 300, 'qty' => 5, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'pasta', 'product_id' => 14, 'unit_price_cents' => 220, 'qty' => 6, 'tags' => array( 'any-3' ) ),
			array( 'key' => 'tom', 'product_id' => 19, 'unit_price_cents' => 220, 'qty' => 1, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'spag', 'product_id' => 12, 'unit_price_cents' => 180, 'qty' => 1, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'corn', 'product_id' => 41, 'unit_price_cents' => 150, 'qty' => 2, 'tags' => array( 'any-4' ) ),
			array( 'key' => 'chick', 'product_id' => 42, 'unit_price_cents' => 140, 'qty' => 2, 'tags' => array( 'any-4' ) ),
		);
		$r = $this->calc( $lines, array( self::ANY_3, self::ANY_4 ) );
		$this->assertSame( 1120, $this->deal_total_saving( $r, 'any-3' ) );
		$this->assertSame( 200, $this->deal_total_saving( $r, 'any-4' ) );
		$this->assertSame( 2, $r['deals'][0]['leftover_qty'] );
		$this->assertSame( 2, $r['deals'][1]['leftover_qty'] );
		foreach ( $r['deals'] as $deal ) {
			foreach ( $deal['sets'] as $set ) {
				$this->assertSame( array(), $set['reassigned_keys'] );
			}
		}
		$this->assert_units_conserved( $lines, $r );
	}

	/**
	 * @param list<array{key:string,qty:int,unit_price_cents:int}> $lines Lines.
	 * @param array{deals:list<array<string,mixed>>}               $result Calculator output.
	 */
	private function assert_units_conserved( array $lines, array $result ): void {
		$expected = array();
		foreach ( $lines as $line ) {
			$expected[ $line['key'] ] = (int) $line['qty'];
		}

		$found = array();
		foreach ( $result['deals'] as $deal ) {
			foreach ( $deal['sets'] as $set ) {
				foreach ( $set['items'] as $item ) {
					$key = $item['key'];
					$found[ $key ] = ( $found[ $key ] ?? 0 ) + (int) $item['qty'];
				}
			}
			foreach ( $deal['leftover'] as $item ) {
				$key = $item['key'];
				$found[ $key ] = ( $found[ $key ] ?? 0 ) + (int) $item['qty'];
			}
		}

		foreach ( $expected as $key => $qty ) {
			$this->assertSame( $qty, $found[ $key ] ?? 0, 'key ' . $key );
		}
	}
}

