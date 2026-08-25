<?php
/**
 * Product-level hybrid pricing flags.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters;

final class HybridPricingProductPresenter {

	public function register(): void {
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'render_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_fields' ) );
	}

	public function render_fields(): void {
		echo '<div class="options_group mhdk-hybrid-pricing-fields">';
		woocommerce_wp_checkbox(
			array(
				'id'          => '_mhdk_b2b_only',
				'label'       => __( 'B2B only', 'mh-dev-kit' ),
				'description' => __( 'Hide this product from B2C catalog and search.', 'mh-dev-kit' ),
			)
		);
		woocommerce_wp_checkbox(
			array(
				'id'          => '_mhdk_hide_price_b2c',
				'label'       => __( 'Hide price from B2C', 'mh-dev-kit' ),
				'description' => __( 'Override global setting for this product.', 'mh-dev-kit' ),
			)
		);
		echo '</div>';
	}

	public function save_fields( int $product_id ): void {
		$b2b_only = isset( $_POST['_mhdk_b2b_only'] ) ? 'yes' : 'no';
		update_post_meta( $product_id, '_mhdk_b2b_only', $b2b_only );

		$hide_price = isset( $_POST['_mhdk_hide_price_b2c'] ) ? 'yes' : 'no';
		update_post_meta( $product_id, '_mhdk_hide_price_b2c', $hide_price );
	}
}
