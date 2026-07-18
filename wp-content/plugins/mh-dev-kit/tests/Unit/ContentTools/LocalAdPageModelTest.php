<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\ContentTools;

use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;
use MH\DevKit\Tests\TestCase;

final class LocalAdPageModelTest extends TestCase {

	private LocalAdPageModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new LocalAdPageModel();
	}

	public function test_build_path_requires_all_segments(): void {
		$this->assertSame(
			'local/au/vic/shepparton-3630',
			$this->model->build_path(
				array(
					'country'  => 'au',
					'state'    => 'vic',
					'suburb'   => 'shepparton',
					'postcode' => '3630',
				)
			)
		);

		$this->assertSame( '', $this->model->build_path( array( 'country' => 'au' ) ) );
	}

	public function test_sanitize_location(): void {
		$location = $this->model->sanitize_location(
			array(
				'country'  => 'AU',
				'state'    => 'VIC',
				'suburb'   => 'Shepparton',
				'postcode' => '3630',
			)
		);

		$this->assertSame( 'au', $location['country'] );
		$this->assertSame( '3630', $location['postcode'] );
	}

	public function test_get_public_url(): void {
		$post_id = self::factory()->post->create( array( 'post_type' => LocalAdPageModel::POST_TYPE ) );
		update_post_meta( $post_id, LocalAdPageModel::META_COUNTRY, 'au' );
		update_post_meta( $post_id, LocalAdPageModel::META_STATE, 'vic' );
		update_post_meta( $post_id, LocalAdPageModel::META_SUBURB, 'shepparton' );
		update_post_meta( $post_id, LocalAdPageModel::META_POSTCODE, '3630' );

		$this->assertSame(
			home_url( 'local/au/vic/shepparton-3630/' ),
			$this->model->get_public_url( $post_id )
		);
	}

	public function test_to_rest_item_shape(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => LocalAdPageModel::POST_TYPE,
				'post_title'  => 'Shepparton Ad',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $post_id, LocalAdPageModel::META_COUNTRY, 'au' );
		update_post_meta( $post_id, LocalAdPageModel::META_STATE, 'vic' );
		update_post_meta( $post_id, LocalAdPageModel::META_SUBURB, 'shepparton' );
		update_post_meta( $post_id, LocalAdPageModel::META_POSTCODE, '3630' );

		$post = get_post( $post_id );
		$this->assertInstanceOf( \WP_Post::class, $post );

		$item = $this->model->to_rest_item( $post );

		$this->assertSame( $post_id, $item['id'] );
		$this->assertArrayHasKey( 'url', $item );
		$this->assertArrayHasKey( 'location', $item );
	}
}
