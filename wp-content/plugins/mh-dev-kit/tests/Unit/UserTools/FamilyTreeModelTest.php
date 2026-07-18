<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\UserTools;

use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;
use MH\DevKit\Tests\TestCase;

final class FamilyTreeModelTest extends TestCase {

	private FamilyTreeModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new FamilyTreeModel();
	}

	public function test_get_tree_returns_default_when_empty(): void {
		$post_id = self::factory()->post->create( array( 'post_type' => FamilyTreeModel::POST_TYPE ) );

		$tree = $this->model->get_tree( $post_id );

		$this->assertArrayHasKey( 'root', $tree );
		$this->assertSame( 'root', $tree['root']['id'] );
	}

	public function test_sanitize_tree_persists_valid_json(): void {
		$json = wp_json_encode( array( 'root' => array( 'id' => 'root', 'name' => 'Test', 'children' => array() ) ) );
		$this->assertIsString( $json );

		$sanitized = $this->model->sanitize_tree( $json );
		$decoded   = json_decode( $sanitized, true );

		$this->assertIsArray( $decoded );
		$this->assertSame( 'Test', $decoded['root']['name'] );
	}
}
