<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Core;

use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Tests\TestCase;

final class MhdkPocSettingsModelTest extends TestCase {

	private MhdkPocSettingsModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new MhdkPocSettingsModel();
		delete_option( MhdkPocSettingsModel::OPTION_KEY );
	}

	public function test_sanitize_accepts_allowed_callout_styles(): void {
		$result = $this->model->sanitize(
			array(
				'default_callout_style' => 'warning',
				'web_links_new_tab'     => '1',
				'menu_currency_symbol'  => '€',
				'enable_frontend_css'   => '',
			)
		);

		$this->assertSame( 'warning', $result['default_callout_style'] );
		$this->assertTrue( $result['web_links_new_tab'] );
		$this->assertFalse( $result['enable_frontend_css'] );
		$this->assertSame( '€', $result['menu_currency_symbol'] );
	}

	public function test_sanitize_rejects_invalid_callout_style(): void {
		$result = $this->model->sanitize(
			array(
				'default_callout_style' => 'danger',
			)
		);

		$this->assertSame( 'info', $result['default_callout_style'] );
	}

	public function test_get_all_merges_defaults(): void {
		update_option( MhdkPocSettingsModel::OPTION_KEY, array( 'menu_currency_symbol' => '£' ), false );

		$all = $this->model->get_all();

		$this->assertSame( '£', $all['menu_currency_symbol'] );
		$this->assertSame( 'info', $all['default_callout_style'] );
	}
}
