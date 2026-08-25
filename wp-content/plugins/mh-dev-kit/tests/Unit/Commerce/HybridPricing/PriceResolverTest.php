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

	public function test_category_discount_picks_lowest_price(): void {
		if ( ! class_exists( WC_Product_Simple::class ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded.' );
		}

		$term = wp_insert_term( 'MHDK Test Cat ' . wp_generate_password( 4, false ), 'product_cat' );
		if ( is_wp_error( $term ) ) {
			$this->markTestSkipped( $term->get_error_message() );
		}
		$term_id = (int) $term['term_id'];

		$settings = new HybridPricingSettingsModel();
		$lists    = new PriceListModel();
		$lists->save_all_lists(
			array(
				array(
					'id'      => 'wholesale-default',
					'enabled' => true,
					'rules'   => array(
						array(
							'type'             => 'category',
							'term_id'          => $term_id,
							'discount_percent' => 10,
						),
						array(
							'type'             => 'category',
							'term_id'          => $term_id,
							'discount_percent' => 25,
						),
					),
				),
			)
		);

		$user_id = wp_insert_user(
			array(
				'user_login' => 'mhdk_b2b_cat_' . wp_generate_password( 6, false ),
				'user_pass'  => 'test',
				'role'       => HybridPricingSettingsModel::B2B_ROLE,
			)
		);
		wp_set_current_user( (int) $user_id );

		$product = new WC_Product_Simple();
		$product->set_name( 'Category rule product' );
		$product->set_regular_price( '100' );
		$product->set_category_ids( array( $term_id ) );
		$product_id = $product->save();

		$product->set_id( $product_id );

		$resolver = new PriceResolver( $settings, $lists );
		$this->assertSame( 75.0, $resolver->resolve_price( $product ) );

		wp_delete_user( (int) $user_id );
		wp_delete_term( $term_id, 'product_cat' );
		wp_delete_post( $product_id, true );
	}

	public function test_per_product_hide_price_for_b2c(): void {
		update_post_meta( 8800, '_mhdk_hide_price_b2c', 'yes' );
		wp_set_current_user( 0 );

		$resolver = new PriceResolver( new HybridPricingSettingsModel(), new PriceListModel() );
		$this->assertTrue( $resolver->should_hide_price_for_product( 8800 ) );

		delete_post_meta( 8800, '_mhdk_hide_price_b2c' );
	}
}
