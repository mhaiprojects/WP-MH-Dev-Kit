<?php
/**
 * Price list storage and rule definitions.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Models;

final class PriceListModel {

	public const OPTION_KEY = 'mhdk_price_lists';

	/**
	 * @return list<array<string, mixed>>
	 */
	public function get_all_lists(): array {
		$stored = get_option( self::OPTION_KEY, array() );

		return is_array( $stored ) ? array_values( $stored ) : array();
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_list( string $list_id ): ?array {
		foreach ( $this->get_all_lists() as $list ) {
			if ( isset( $list['id'] ) && (string) $list['id'] === $list_id ) {
				return $list;
			}
		}

		return null;
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	public function get_lists_for_user( int $user_id ): array {
		if ( $user_id <= 0 ) {
			return array();
		}

		$assigned = get_user_meta( $user_id, 'mhdk_price_list_ids', true );
		if ( ! is_array( $assigned ) || array() === $assigned ) {
			$settings = new HybridPricingSettingsModel();
			$default  = (string) $settings->get( 'default_b2b_list_id', 'wholesale-default' );

			return array_filter(
				array( $this->get_list( $default ) ),
				static fn( $list ) => is_array( $list ) && ! empty( $list['enabled'] )
			);
		}

		$lists = array();
		foreach ( $assigned as $list_id ) {
			$list = $this->get_list( (string) $list_id );
			if ( is_array( $list ) && ! empty( $list['enabled'] ) ) {
				$lists[] = $list;
			}
		}

		return $lists;
	}

	/**
	 * @param list<array<string, mixed>> $lists
	 */
	public function save_all_lists( array $lists ): void {
		update_option( self::OPTION_KEY, array_values( $lists ), false );
	}

	/**
	 * @return list<array<string, mixed>>
	 */
	public function get_default_seed_lists(): array {
		return array(
			array(
				'id'      => 'wholesale-default',
				'name'    => __( 'Wholesale Default', 'mh-dev-kit' ),
				'enabled' => true,
				'rules'   => array(),
			),
		);
	}

	public function ensure_defaults(): void {
		if ( array() !== $this->get_all_lists() ) {
			return;
		}

		$this->save_all_lists( $this->get_default_seed_lists() );
	}

	/**
	 * @param array<string, mixed> $list
	 */
	public function upsert_list( array $list ): void {
		$lists = $this->get_all_lists();
		$id    = (string) ( $list['id'] ?? '' );
		if ( '' === $id ) {
			return;
		}

		$found = false;
		foreach ( $lists as $index => $existing ) {
			if ( (string) ( $existing['id'] ?? '' ) === $id ) {
				$lists[ $index ] = $list;
				$found           = true;
				break;
			}
		}

		if ( ! $found ) {
			$lists[] = $list;
		}

		$this->save_all_lists( $lists );
	}
}
