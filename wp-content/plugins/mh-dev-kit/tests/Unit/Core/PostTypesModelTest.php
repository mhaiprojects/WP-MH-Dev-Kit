<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Core;

use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\PostTypesModel;
use MH\DevKit\Tests\TestCase;

final class PostTypesModelTest extends TestCase {

	private PostTypesModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new PostTypesModel();
	}

	public function test_cpt_args_include_expected_keys(): void {
		$menu_args = $this->model->get_hospitality_menu_args();
		$link_args = $this->model->get_web_link_args();

		$this->assertSame( PostTypesModel::HOSPITALITY_MENU, 'mhdk_hosp_menu' );
		$this->assertTrue( $menu_args['public'] );
		$this->assertTrue( $link_args['has_archive'] );
	}

	public function test_web_link_opens_new_tab_honors_meta_and_default(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_type' => PostTypesModel::WEB_LINK,
			)
		);

		$this->assertTrue( $this->model->web_link_opens_new_tab( $post_id, true ) );

		update_post_meta( $post_id, PostTypesModel::META_NEW_TAB, '0' );
		$this->assertFalse( $this->model->web_link_opens_new_tab( $post_id, true ) );
	}
}
