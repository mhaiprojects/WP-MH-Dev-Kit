<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\ContentTools;

use MH\DevKit\Modules\ContentTools\Features\ChildrenPages\Models\ChildrenPagesModel;
use MH\DevKit\Tests\TestCase;

final class ChildrenPagesModelTest extends TestCase {

	private ChildrenPagesModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new ChildrenPagesModel();
	}

	public function test_resolve_parent_id_from_attributes(): void {
		$this->assertSame( 42, $this->model->resolve_parent_id( array( 'parent_id' => 42 ) ) );
		$this->assertSame( 7, $this->model->resolve_parent_id( array(), 7 ) );
	}

	public function test_get_children_returns_child_pages(): void {
		$parent = self::factory()->post->create(
			array(
				'post_type' => 'page',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_parent' => $parent,
				'post_status' => 'publish',
			)
		);

		$children = $this->model->get_children( array( 'parent_id' => $parent ) );

		$this->assertCount( 1, $children );
	}
}
