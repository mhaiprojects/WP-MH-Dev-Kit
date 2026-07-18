<?php
/**
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests\Unit\Shared;

use MH\DevKit\Shared\UserScopedCptHelper;
use MH\DevKit\Tests\TestCase;

final class UserScopedCptHelperTest extends TestCase {

	public function test_author_caps_args_structure(): void {
		$args = UserScopedCptHelper::author_caps_args( 'QR Code', 'QR Codes', 'dashicons-screenoptions' );

		$this->assertFalse( $args['public'] );
		$this->assertTrue( $args['show_in_rest'] );
		$this->assertSame( 'post', $args['capability_type'] );
		$this->assertSame( array( 'title', 'author' ), $args['supports'] );
		$this->assertSame( 'QR Code', $args['labels']['singular_name'] );
	}

	public function test_current_user_owns_post(): void {
		$user_id = $this->create_user_with_role( 'subscriber' );
		$other   = $this->create_user_with_role( 'subscriber' );

		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'post',
				'post_author' => $user_id,
			)
		);

		$this->acting_as( $user_id );
		$this->assertTrue( UserScopedCptHelper::current_user_owns_post( $post_id ) );

		$this->acting_as( $other );
		$this->assertFalse( UserScopedCptHelper::current_user_owns_post( $post_id ) );
	}

	public function test_can_manage_own_requires_login(): void {
		wp_set_current_user( 0 );
		$this->assertFalse( UserScopedCptHelper::can_manage_own() );

		$user_id = $this->create_user_with_role( 'subscriber' );
		$this->acting_as( $user_id );
		$this->assertTrue( UserScopedCptHelper::can_manage_own() );
	}
}
