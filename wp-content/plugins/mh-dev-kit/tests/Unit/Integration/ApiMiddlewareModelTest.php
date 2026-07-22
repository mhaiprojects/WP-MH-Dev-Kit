<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Integration;

use MH\DevKit\Modules\Integration\Features\ApiMiddleware\Models\ApiMiddlewareModel;
use MH\DevKit\Tests\TestCase;

final class ApiMiddlewareModelTest extends TestCase {

	private ApiMiddlewareModel $model;

	protected function setUp(): void {
		parent::setUp();
		delete_option( ApiMiddlewareModel::CONNECTIONS_OPTION );
		delete_option( ApiMiddlewareModel::LOG_OPTION );
		$this->model = new ApiMiddlewareModel();
	}

	public function test_save_and_get_connection(): void {
		$this->model->save_connection(
			array(
				'id'       => 'demo-api',
				'name'     => 'Demo',
				'base_url' => 'https://example.com',
				'endpoint' => '/v1/test',
				'method'   => 'GET',
			)
		);

		$connection = $this->model->get_connection( 'demo-api' );

		$this->assertNotNull( $connection );
		$this->assertSame( 'Demo', $connection['name'] );
		$this->assertSame( 'GET', $connection['method'] );
	}

	public function test_run_connection_stores_response_in_option(): void {
		add_filter(
			'pre_http_request',
			static function ( $pre, $args, $url ) {
				unset( $args, $url );
				return array(
					'response' => array( 'code' => 200 ),
					'body'     => '{"ok":true}',
					'headers'  => array(),
				);
			},
			10,
			3
		);

		$this->model->save_connection(
			array(
				'id'          => 'mock-api',
				'name'        => 'Mock',
				'base_url'    => 'https://example.com',
				'endpoint'    => '/data',
				'target_type' => 'option',
				'target_key'  => 'mhdk_api_mock_data',
				'enabled'     => true,
			)
		);

		$result = $this->model->run_connection( 'mock-api' );

		$this->assertTrue( $result['success'] );
		$this->assertSame( '{"ok":true}', get_option( 'mhdk_api_mock_data' ) );
	}
}
