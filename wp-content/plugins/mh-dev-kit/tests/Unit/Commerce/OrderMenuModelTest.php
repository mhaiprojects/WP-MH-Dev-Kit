<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Commerce;

use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuModel;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuSettingsModel;
use MH\DevKit\Tests\TestCase;

final class OrderMenuModelTest extends TestCase {

	private OrderMenuModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new OrderMenuModel();
	}

	public function test_normalize_atts_sanitizes_values(): void {
		$atts = $this->model->normalize_atts(
			array(
				'category' => 'Shepparton Local',
				'order'    => 'desc',
				'menu'     => 'Weekly Menu',
			)
		);

		$this->assertSame( 'shepparton-local', $atts['category'] );
		$this->assertSame( 'DESC', $atts['order'] );
		$this->assertSame( 'weekly-menu', $atts['menu'] );
	}

	public function test_resolve_filter_atts_uses_default_category(): void {
		$settings = new OrderMenuSettingsModel();
		update_option(
			OrderMenuSettingsModel::OPTION_KEY,
			array( 'default_category' => 'local-produce' ),
			false
		);

		$model    = new OrderMenuModel( $settings );
		$resolved = $model->resolve_filter_atts( array() );

		$this->assertSame( 'local-produce', $resolved['category'] );
	}

	public function test_menu_token_round_trip(): void {
		$atts = array(
			'category'   => 'fruit',
			'ids'        => '',
			'slugs'      => '',
			'menu'       => '',
			'orderby'    => 'title',
			'order'      => 'ASC',
			'show_image' => '',
		);

		$token    = $this->model->create_menu_token( $atts, array( 12, 5, 12 ) );
		$verified = $this->model->verify_menu_token( $token );

		$this->assertIsArray( $verified );
		$this->assertSame( array( 5, 12, 12 ), $verified['product_ids'] );
		$this->assertSame( 'fruit', $verified['atts']['category'] );
	}

	public function test_verify_menu_token_rejects_tampered_signature(): void {
		$token = $this->model->create_menu_token( array( 'category' => 'x' ), array( 1 ) );
		$parts = explode( '.', $token );
		$bad   = $parts[0] . '.deadbeef';

		$this->assertNull( $this->model->verify_menu_token( $bad ) );
	}

	public function test_sanitize_quantities(): void {
		$clean = $this->model->sanitize_quantities(
			array(
				'10'  => 3,
				'0'   => 5,
				'abc' => 2,
				'11'  => 9999,
			)
		);

		$this->assertSame( array( 10 => 3, 11 => 99 ), $clean );
	}

	public function test_parse_unit_from_product_name(): void {
		if ( ! class_exists( 'WC_Product_Simple' ) ) {
			$this->markTestSkipped( 'WooCommerce not loaded.' );
		}

		$product = new \WC_Product_Simple();
		$product->set_name( 'Apples (kg)' );

		$this->assertSame( 'kg', $this->model->parse_unit( $product ) );
	}
}
