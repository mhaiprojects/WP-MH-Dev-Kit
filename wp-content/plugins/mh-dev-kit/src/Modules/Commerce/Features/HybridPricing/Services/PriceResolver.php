<?php
/**
 * Resolves effective WooCommerce prices from price list rules.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Services
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Services;

use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\PriceListModel;
use WC_Product;

final class PriceResolver {

	public function __construct(
		private HybridPricingSettingsModel $settings,
		private PriceListModel $price_lists
	) {
	}

	public function should_hide_price_html(): bool {
		return $this->settings->hide_prices_for_current_user();
	}

	public function should_hide_price_for_product( int $product_id ): bool {
		if ( HybridPricingSettingsModel::current_user_is_b2b() ) {
			return false;
		}

		if ( 'yes' === get_post_meta( $product_id, '_mhdk_hide_price_b2c', true ) ) {
			return true;
		}

		return $this->settings->hide_prices_for_current_user();
	}

	public function get_hidden_price_html(): string {
		$message = (string) $this->settings->get( 'login_for_prices_message', '' );

		if ( '' === $message ) {
			return '';
		}

		return '<span class="mhdk-price-hidden">' . esc_html( $message ) . '</span>';
	}

	/**
	 * @return float|null Resolved price or null to use WooCommerce default.
	 */
	public function resolve_price( WC_Product $product ): ?float {
		if ( ! HybridPricingSettingsModel::current_user_is_b2b() ) {
			return null;
		}

		if ( ! (bool) $this->settings->get( 'apply_custom_prices_cart', true ) ) {
			return null;
		}

		$user_id = get_current_user_id();
		$lists   = $this->price_lists->get_lists_for_user( $user_id );
		if ( array() === $lists ) {
			return null;
		}

		$base = (float) $product->get_regular_price( 'edit' );
		if ( $base <= 0 ) {
			$base = (float) $product->get_price( 'edit' );
		}
		if ( $base <= 0 ) {
			return null;
		}

		$best = null;
		foreach ( $lists as $list ) {
			$rules = is_array( $list['rules'] ?? null ) ? $list['rules'] : array();
			foreach ( $rules as $rule ) {
				if ( ! is_array( $rule ) ) {
					continue;
				}
				$candidate = $this->apply_rule( $rule, $product, $base );
				if ( null === $candidate ) {
					continue;
				}
				if ( null === $best || $candidate < $best ) {
					$best = $candidate;
				}
			}
		}

		return $best;
	}

	/**
	 * @param array<string, mixed> $rule
	 */
	private function apply_rule( array $rule, WC_Product $product, float $base_price ): ?float {
		$type = (string) ( $rule['type'] ?? '' );

		if ( 'product' === $type ) {
			$product_id = (int) ( $rule['product_id'] ?? 0 );
			if ( $product_id <= 0 || $product_id !== (int) $product->get_id() ) {
				return null;
			}

			if ( isset( $rule['fixed_price'] ) && '' !== (string) $rule['fixed_price'] ) {
				return max( 0.0, (float) $rule['fixed_price'] );
			}

			if ( isset( $rule['discount_percent'] ) ) {
				return $this->discounted_price( $base_price, (float) $rule['discount_percent'] );
			}

			return null;
		}

		if ( 'category' === $type ) {
			$term_id = (int) ( $rule['term_id'] ?? 0 );
			if ( $term_id <= 0 ) {
				return null;
			}

			$term_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
			if ( ! in_array( $term_id, array_map( 'intval', $term_ids ), true ) ) {
				return null;
			}

			if ( isset( $rule['fixed_price'] ) && '' !== (string) $rule['fixed_price'] ) {
				return max( 0.0, (float) $rule['fixed_price'] );
			}

			if ( isset( $rule['discount_percent'] ) ) {
				return $this->discounted_price( $base_price, (float) $rule['discount_percent'] );
			}
		}

		return null;
	}

	private function discounted_price( float $base, float $percent ): float {
		$percent = max( 0.0, min( 100.0, $percent ) );

		return round( $base * ( 1 - ( $percent / 100 ) ), wc_get_price_decimals(), PHP_ROUND_HALF_UP );
	}
}
