<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Integration\Commerce;

use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuModel;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\SavedMenuModel;
use MH\DevKit\Tests\TestCase;

final class OrderMenuIntegrationTest extends TestCase {

	public function test_saved_menu_cpt_exists_when_commerce_booted(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded.' );
		}

		$this->assertTrue( post_type_exists( SavedMenuModel::POST_TYPE ) );
	}

	public function test_is_seasonally_available_respects_meta_dates(): void {
		if ( ! class_exists( 'WC_Product_Simple' ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded.' );
		}

		$product = new \WC_Product_Simple();
		$product->set_name( 'Seasonal Item' );
		$product_id = $product->save();

		$model = new OrderMenuModel();
		$this->assertTrue( $model->is_seasonally_available( $product_id ) );

		update_post_meta( $product_id, '_mhdk_available_from', '2099-01-01' );
		$this->assertFalse( $model->is_seasonally_available( $product_id ) );
	}
}
