<?php
/**
 * Order Menu settings option storage.
 *
 * @package MH\DevKit\Features\OrderMenu\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu\Models;

final class OrderMenuSettingsModel {

	public const OPTION_KEY    = 'mhdk_order_menu_settings';

	public const SETTINGS_SLUG = 'mhdk-settings-order-menu';

	public const META_MENU_DESCRIPTION = '_mhdk_menu_description';

	public const META_AVAILABLE_FROM   = '_mhdk_available_from';

	public const META_AVAILABLE_UNTIL  = '_mhdk_available_until';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'enable_ajax'             => true,
			'default_category'        => 'shepparton-local-produce',
			'max_qty_per_line'        => 99,
			'show_unit_column'        => true,
			'show_stock_badge'        => true,
			'show_image'              => false,
			'preserve_qty_after_add'  => true,
			'enable_reorder_button'   => true,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_all(): array {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return array_merge( $this->get_defaults(), $stored );
	}

	/**
	 * @param mixed $default Fallback.
	 */
	public function get( string $key, mixed $default = null ): mixed {
		$all = $this->get_all();

		return $all[ $key ] ?? $default;
	}

	public function max_qty_per_line(): int {
		return max( 1, min( 999, (int) $this->get( 'max_qty_per_line', 99 ) ) );
	}

	public function ajax_enabled(): bool {
		return (bool) $this->get( 'enable_ajax', true );
	}

	/**
	 * @param array<string, mixed> $input Raw settings.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		$clean['enable_ajax']            = ! empty( $input['enable_ajax'] );
		$clean['show_unit_column']       = ! empty( $input['show_unit_column'] );
		$clean['show_stock_badge']       = ! empty( $input['show_stock_badge'] );
		$clean['show_image']             = ! empty( $input['show_image'] );
		$clean['preserve_qty_after_add'] = ! empty( $input['preserve_qty_after_add'] );
		$clean['enable_reorder_button']  = ! empty( $input['enable_reorder_button'] );

		if ( isset( $input['default_category'] ) ) {
			$clean['default_category'] = sanitize_title( (string) $input['default_category'] );
		}

		if ( isset( $input['max_qty_per_line'] ) ) {
			$clean['max_qty_per_line'] = max( 1, min( 999, (int) $input['max_qty_per_line'] ) );
		}

		return $clean;
	}
}
