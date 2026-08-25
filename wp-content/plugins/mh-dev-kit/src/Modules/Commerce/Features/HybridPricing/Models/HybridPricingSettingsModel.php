<?php
/**
 * Hybrid B2B/B2C pricing settings.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Models;

final class HybridPricingSettingsModel {

	public const OPTION_KEY = 'mhdk_hybrid_pricing_settings';

	public const SETTINGS_SLUG = 'mhdk-settings-hybrid-pricing';

	public const B2B_ROLE = 'mhdk_b2b_customer';

	public const CAP_VIEW_WHOLESALE = 'mhdk_view_wholesale_prices';

	public const CAP_VIEW_B2B_CATALOG = 'mhdk_view_b2b_catalog';

	/**
	 * @return array<string, mixed>
	 */
	public function get_defaults(): array {
		return array(
			'hide_prices_guest'        => true,
			'hide_prices_b2c'          => true,
			'login_for_prices_message' => __( 'Log in or register for a trade account to view pricing.', 'mh-dev-kit' ),
			'hide_b2b_only_from_b2c'   => true,
			'apply_custom_prices_cart' => true,
			'default_b2b_list_id'      => 'wholesale-default',
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

	public function get( string $key, mixed $default = null ): mixed {
		$all = $this->get_all();

		return $all[ $key ] ?? $default;
	}

	public function hide_prices_for_current_user(): bool {
		if ( self::current_user_is_b2b() ) {
			return false;
		}

		if ( ! is_user_logged_in() ) {
			return (bool) $this->get( 'hide_prices_guest', true );
		}

		return (bool) $this->get( 'hide_prices_b2c', true );
	}

	public static function current_user_is_b2b(): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();
		if ( ! $user instanceof \WP_User ) {
			return false;
		}

		if ( user_can( $user, self::CAP_VIEW_WHOLESALE ) || user_can( $user, 'manage_woocommerce' ) ) {
			return true;
		}

		return in_array( self::B2B_ROLE, (array) $user->roles, true );
	}

	/**
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = $this->get_defaults();

		$clean['hide_prices_guest']        = ! empty( $input['hide_prices_guest'] );
		$clean['hide_prices_b2c']          = ! empty( $input['hide_prices_b2c'] );
		$clean['hide_b2b_only_from_b2c']   = ! empty( $input['hide_b2b_only_from_b2c'] );
		$clean['apply_custom_prices_cart'] = ! empty( $input['apply_custom_prices_cart'] );

		if ( isset( $input['login_for_prices_message'] ) ) {
			$clean['login_for_prices_message'] = sanitize_text_field( (string) $input['login_for_prices_message'] );
		}

		if ( isset( $input['default_b2b_list_id'] ) ) {
			$clean['default_b2b_list_id'] = sanitize_key( (string) $input['default_b2b_list_id'] );
		}

		return $clean;
	}
}
