<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\SiteTools;

use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models\RestrictOriginSettingsModel;
use MH\DevKit\Tests\TestCase;

final class RestrictOriginSettingsModelTest extends TestCase {

	private RestrictOriginSettingsModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new RestrictOriginSettingsModel();
		delete_option( RestrictOriginSettingsModel::OPTION_KEY );
	}

	public function test_sanitize_parses_flags(): void {
		$result = $this->model->sanitize(
			array(
				'enabled'         => '1',
				'allow_same_host' => '',
				'allowed_hosts'   => "Example.com\r\ntest.org",
			)
		);

		$this->assertTrue( $result['enabled'] );
		$this->assertFalse( $result['allow_same_host'] );
		$this->assertSame( "Example.com\r\ntest.org", $result['allowed_hosts'] );
	}

	public function test_get_allowed_hosts_normalizes(): void {
		update_option(
			RestrictOriginSettingsModel::OPTION_KEY,
			array( 'allowed_hosts' => "Foo.COM, bar.org\n" ),
			false
		);

		$this->assertSame( array( 'foo.com', 'bar.org' ), $this->model->get_allowed_hosts() );
	}
}
