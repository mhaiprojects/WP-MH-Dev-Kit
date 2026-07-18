<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Integration\Core;

use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\PostTypesModel;
use MH\DevKit\Tests\TestCase;

final class MhdkPocIntegrationTest extends TestCase {

	public function test_hospitality_menu_cpt_is_registered(): void {
		$this->assertTrue( post_type_exists( PostTypesModel::HOSPITALITY_MENU ) );
		$this->assertTrue( post_type_exists( PostTypesModel::WEB_LINK ) );
	}

	public function test_mhdk_blocks_are_registered(): void {
		$registry = \WP_Block_Type_Registry::get_instance();

		$this->assertTrue(
			$registry->is_registered( 'mh-dev-kit/callout' )
			|| $registry->is_registered( 'mhdk/callout' )
			|| $registry->is_registered( 'mh-dev-kit/hospitality-menu' )
		);
	}
}
