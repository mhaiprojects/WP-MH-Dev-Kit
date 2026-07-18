<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\UserTools;

use MH\DevKit\Modules\UserTools\Features\QrCode\Models\QrCodeModel;
use MH\DevKit\Tests\TestCase;

final class QrCodeModelTest extends TestCase {

	private QrCodeModel $model;

	protected function setUp(): void {
		parent::setUp();
		$this->model = new QrCodeModel();
	}

	public function test_get_size_clamps_between_128_and_512(): void {
		$post_id = self::factory()->post->create( array( 'post_type' => QrCodeModel::POST_TYPE ) );

		update_post_meta( $post_id, QrCodeModel::META_SIZE, 64 );
		$this->assertSame( 128, $this->model->get_size( $post_id ) );

		update_post_meta( $post_id, QrCodeModel::META_SIZE, 900 );
		$this->assertSame( 512, $this->model->get_size( $post_id ) );
	}

	public function test_get_for_current_user_scopes_by_author(): void {
		$user_id = $this->create_user_with_role( 'subscriber' );
		$other   = $this->create_user_with_role( 'subscriber' );

		self::factory()->post->create(
			array(
				'post_type'   => QrCodeModel::POST_TYPE,
				'post_author' => $user_id,
			)
		);
		self::factory()->post->create(
			array(
				'post_type'   => QrCodeModel::POST_TYPE,
				'post_author' => $other,
			)
		);

		$this->acting_as( $user_id );
		$this->assertCount( 1, $this->model->get_for_current_user() );
	}
}
