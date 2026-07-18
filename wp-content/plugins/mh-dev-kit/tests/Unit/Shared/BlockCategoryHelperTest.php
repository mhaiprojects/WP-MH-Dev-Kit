<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Shared;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Shared\BlockCategoryHelper;
use MH\DevKit\Tests\TestCase;

final class BlockCategoryHelperTest extends TestCase {

	public function test_prepend_category_adds_mhdk_when_missing(): void {
		$categories = array(
			array(
				'slug'  => 'text',
				'title' => 'Text',
			),
		);

		$result = BlockCategoryHelper::prepend_category(
			$categories,
			new \WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) )
		);

		$this->assertSame( AdminMenu::BLOCK_CATEGORY, $result[0]['slug'] );
		$this->assertSame( 'MHDK', $result[0]['title'] );
	}

	public function test_prepend_category_does_not_duplicate(): void {
		$categories = array(
			array(
				'slug'  => AdminMenu::BLOCK_CATEGORY,
				'title' => 'MHDK',
			),
			array(
				'slug'  => 'text',
				'title' => 'Text',
			),
		);

		$result = BlockCategoryHelper::prepend_category_legacy(
			$categories,
			self::factory()->post->create_and_get()
		);

		$this->assertCount( 2, $result );
	}

	public function test_register_is_idempotent(): void {
		BlockCategoryHelper::register();
		BlockCategoryHelper::register();

		$this->assertTrue( has_filter( 'block_categories_all', array( BlockCategoryHelper::class, 'prepend_category' ) ) !== false );
	}
}
