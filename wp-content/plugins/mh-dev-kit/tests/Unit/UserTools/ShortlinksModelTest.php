<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\UserTools;

use MH\DevKit\Modules\UserTools\Features\Shortlinks\Models\ShortlinksModel;
use MH\DevKit\Tests\TestCase;

final class ShortlinksModelTest extends TestCase {

	private ShortlinksModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new ShortlinksModel();
	}

	public function test_sanitize_links_filters_invalid_rows(): void {
		$links = $this->model->sanitize_links(
			array(
				array(
					'label' => 'Valid',
					'url'   => 'https://example.com',
				),
				array(
					'label' => '',
					'url'   => 'https://bad.com',
				),
				'not-an-array',
			)
		);

		$this->assertCount( 1, $links );
		$this->assertSame( 'Valid', $links[0]['label'] );
	}

	public function test_public_url_format(): void {
		$this->assertSame( home_url( 'l/my-slug/' ), ShortlinksModel::public_url( 'my-slug' ) );
	}

	public function test_find_by_slug(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => ShortlinksModel::POST_TYPE,
				'post_status' => 'publish',
			)
		);
		update_post_meta( $post_id, ShortlinksModel::META_SLUG, 'my-links' );

		$found = $this->model->find_by_slug( 'my-links' );

		$this->assertInstanceOf( \WP_Post::class, $found );
		$this->assertSame( $post_id, (int) $found->ID );
	}
}
