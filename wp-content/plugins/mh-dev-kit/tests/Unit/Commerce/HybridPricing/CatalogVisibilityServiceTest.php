<?php
/**
 * @package MH\DevKit\Tests\Unit\Commerce\HybridPricing
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Commerce\HybridPricing;

use MH\DevKit\Modules\Commerce\Features\HybridPricing\HybridPricingFeature;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Services\CatalogVisibilityService;
use PHPUnit\Framework\TestCase;

final class CatalogVisibilityServiceTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		( new HybridPricingFeature() )->register_roles();
	}

	public function test_hides_b2b_only_product_from_retail_user(): void {
		update_post_meta( 9001, '_mhdk_b2b_only', 'yes' );
		wp_set_current_user( 0 );

		$service = new CatalogVisibilityService( new HybridPricingSettingsModel() );
		$this->assertTrue( $service->should_hide_from_current_user( 9001 ) );

		delete_post_meta( 9001, '_mhdk_b2b_only' );
	}

	public function test_shows_b2b_only_product_to_b2b_user(): void {
		update_post_meta( 9002, '_mhdk_b2b_only', 'yes' );

		$user_id = wp_insert_user(
			array(
				'user_login' => 'mhdk_b2b_vis_' . wp_generate_password( 6, false ),
				'user_pass'  => 'test',
				'role'       => HybridPricingSettingsModel::B2B_ROLE,
			)
		);
		wp_set_current_user( (int) $user_id );

		$service = new CatalogVisibilityService( new HybridPricingSettingsModel() );
		$this->assertFalse( $service->should_hide_from_current_user( 9002 ) );

		delete_post_meta( 9002, '_mhdk_b2b_only' );
		wp_delete_user( (int) $user_id );
	}
}
