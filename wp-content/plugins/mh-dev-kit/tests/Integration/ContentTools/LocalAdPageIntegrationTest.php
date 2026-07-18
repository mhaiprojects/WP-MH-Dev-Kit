<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Integration\ContentTools;

use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPageRestPresenter;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters\LocalAdPageSeedPresenter;
use MH\DevKit\Tests\TestCase;

final class LocalAdPageIntegrationTest extends TestCase {

	private LocalAdPageModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new LocalAdPageModel();
		delete_option( LocalAdPageModel::SEED_OPTION );
	}

	public function test_find_by_path(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => LocalAdPageModel::POST_TYPE,
				'post_status' => 'publish',
			)
		);
		update_post_meta( $post_id, LocalAdPageModel::META_COUNTRY, 'au' );
		update_post_meta( $post_id, LocalAdPageModel::META_STATE, 'vic' );
		update_post_meta( $post_id, LocalAdPageModel::META_SUBURB, 'shepparton' );
		update_post_meta( $post_id, LocalAdPageModel::META_POSTCODE, '3630' );

		$found = $this->model->find_by_path( 'au', 'vic', 'shepparton', '3630' );

		$this->assertInstanceOf( \WP_Post::class, $found );
		$this->assertSame( $post_id, (int) $found->ID );
	}

	public function test_seed_creates_shepparton_page_once(): void {
		$seed = new LocalAdPageSeedPresenter( $this->model );
		$seed->maybe_seed();
		$seed->maybe_seed();

		$found = $this->model->find_by_path( 'au', 'vic', 'shepparton', '3630' );
		$this->assertInstanceOf( \WP_Post::class, $found );
		$this->assertTrue( (bool) get_option( LocalAdPageModel::SEED_OPTION ) );
	}

	public function test_rest_permissions(): void {
		$presenter = new LocalAdPageRestPresenter( $this->model );

		wp_set_current_user( 0 );
		$this->assertFalse( $presenter->can_read() );

		$editor = $this->create_user_with_role( 'editor' );
		$this->acting_as( $editor );
		$this->assertTrue( $presenter->can_read() );
		$this->assertTrue( $presenter->can_write() );
	}
}
