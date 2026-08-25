<?php
/**
 * @package MH\DevKit\Tests\Unit\Commerce\HybridPricing
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Commerce\HybridPricing;

use MH\DevKit\Modules\Commerce\Features\HybridPricing\HybridPricingFeature;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\PriceListModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Services\PriceResolver;
use PHPUnit\Framework\TestCase;
use WC_Product_Simple;

final class PriceResolverTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		( new HybridPricingFeature() )->register_roles();
	}

	public function test_picks_lowest_fixed_price_for_product_rules(): void {
		if ( ! class_exists( WC_Product_Simple::class ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded.' );
		}

		$settings = new HybridPricingSettingsModel();
		$lists    = new PriceListModel();
		$lists->save_all_lists(
			array(
				array(
					'id'      => 'test',
					'enabled' => true,
					'rules'   => array(
						array(
							'type'        => 'product',
							'product_id'  => 42,
							'fixed_price' => '80',
						),
						array(
							'type'        => 'product',
							'product_id'  => 42,
							'fixed_price' => '70',
						),
					),
				),
			)
		);

		$user_id = wp_insert_user(
			array(
				'user_login' => 'mhdk_b2b_test_' . wp_generate_password( 6, false ),
				'user_pass'  => 'test',
				'role'       => HybridPricingSettingsModel::B2B_ROLE,
			)
		);
		wp_set_current_user( (int) $user_id );

		$product = new WC_Product_Simple();
		$product->set_id( 42 );
		$product->set_regular_price( '100' );

		$resolver = new PriceResolver( $settings, $lists );
		$this->assertSame( 70.0, $resolver->resolve_price( $product ) );

		wp_delete_user( (int) $user_id );
	}
}
