<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Commerce;

use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuSettingsModel;
use MH\DevKit\Tests\TestCase;

final class OrderMenuSettingsModelTest extends TestCase {

	private OrderMenuSettingsModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new OrderMenuSettingsModel();
		delete_option( OrderMenuSettingsModel::OPTION_KEY );
	}

	public function test_sanitize_clamps_max_qty(): void {
		$result = $this->model->sanitize(
			array(
				'max_qty_per_line' => 5000,
				'default_category' => 'Shepparton Local',
			)
		);

		$this->assertSame( 999, $result['max_qty_per_line'] );
		$this->assertSame( 'shepparton-local', $result['default_category'] );
	}

	public function test_max_qty_per_line_bounds(): void {
		update_option( OrderMenuSettingsModel::OPTION_KEY, array( 'max_qty_per_line' => 0 ), false );

		$this->assertSame( 1, $this->model->max_qty_per_line() );
	}
}
