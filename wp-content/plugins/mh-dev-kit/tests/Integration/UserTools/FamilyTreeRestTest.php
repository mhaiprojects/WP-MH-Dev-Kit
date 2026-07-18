<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Integration\UserTools;

use MH\DevKit\Modules\UserTools\Features\FamilyTree\Models\FamilyTreeModel;
use MH\DevKit\Modules\UserTools\Features\FamilyTree\Presenters\FamilyTreeRestPresenter;
use MH\DevKit\Modules\UserTools\Features\QrCode\Models\QrCodeModel;
use MH\DevKit\Tests\TestCase;

final class FamilyTreeRestTest extends TestCase {

	public function test_save_tree_requires_owner(): void {
		$owner = $this->create_user_with_role( 'subscriber' );
		$other = $this->create_user_with_role( 'subscriber' );

		$post_id = self::factory()->post->create(
			array(
				'post_type'   => FamilyTreeModel::POST_TYPE,
				'post_author' => $owner,
			)
		);

		$presenter = new FamilyTreeRestPresenter( new FamilyTreeModel() );

		$this->acting_as( $other );
		$request = new \WP_REST_Request( 'POST', '/mhdk/v1/family-tree/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_body( wp_json_encode( array( 'tree' => array( 'root' => array( 'id' => 'root' ) ) ) ) );

		$this->assertFalse( $presenter->can_save( $request ) );

		$this->acting_as( $owner );
		$this->assertTrue( $presenter->can_save( $request ) );
	}

	public function test_user_tools_cpts_registered(): void {
		$this->assertTrue( post_type_exists( QrCodeModel::POST_TYPE ) );
		$this->assertTrue( post_type_exists( FamilyTreeModel::POST_TYPE ) );
	}
}
