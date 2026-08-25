<?php
/**
 * B2B-only product catalog visibility.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Services
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Services;

use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;

final class CatalogVisibilityService {

	public function __construct(
		private HybridPricingSettingsModel $settings
	) {
	}

	public function product_is_b2b_only( int $product_id ): bool {
		return 'yes' === get_post_meta( $product_id, '_mhdk_b2b_only', true );
	}

	public function should_hide_from_current_user( int $product_id ): bool {
		if ( ! (bool) $this->settings->get( 'hide_b2b_only_from_b2c', true ) ) {
			return false;
		}

		if ( ! $this->product_is_b2b_only( $product_id ) ) {
			return false;
		}

		return ! HybridPricingSettingsModel::current_user_is_b2b();
	}

	/**
	 * @param list<int> $post_ids
	 * @return list<int>
	 */
	public function filter_visible_product_ids( array $post_ids ): array {
		return array_values(
			array_filter(
				$post_ids,
				fn( int $id ): bool => ! $this->should_hide_from_current_user( $id )
			)
		);
	}
}
