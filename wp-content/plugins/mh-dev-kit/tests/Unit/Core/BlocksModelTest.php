<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Core;

use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\BlocksModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\PostTypesModel;
use MH\DevKit\Tests\TestCase;

final class BlocksModelTest extends TestCase {

	public function test_resolve_callout_style_falls_back_to_default(): void {
		$settings = new MhdkPocSettingsModel();
		$model    = new BlocksModel( $settings, new PostTypesModel() );

		$this->assertSame( 'info', $model->resolve_callout_style( 'invalid' ) );
		$this->assertSame( 'success', $model->resolve_callout_style( 'success' ) );
	}

	public function test_get_web_links_data_normalizes_layout(): void {
		$model = new BlocksModel( new MhdkPocSettingsModel(), new PostTypesModel() );
		$data  = $model->get_web_links_data( 5, 'grid' );

		$this->assertSame( 'grid', $data['layout'] );
		$this->assertIsArray( $data['items'] );
	}
}
