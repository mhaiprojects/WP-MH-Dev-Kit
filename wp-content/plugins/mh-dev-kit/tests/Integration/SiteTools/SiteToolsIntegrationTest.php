<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Integration\SiteTools;

use MH\DevKit\Tests\TestCase;

final class SiteToolsIntegrationTest extends TestCase {

	public function test_site_tools_settings_models_load(): void {
		$restrict = new \MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models\RestrictOriginSettingsModel();
		$map      = new \MH\DevKit\Modules\SiteTools\Features\MapDistance\Models\MapDistanceSettingsModel();

		$this->assertFalse( $restrict->is_enabled() );
		$this->assertSame( 'Shepparton CBD', $map->get( 'start_label' ) );
	}
}
