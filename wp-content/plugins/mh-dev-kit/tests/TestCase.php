<?php
/**
 * Base test case for MH Dev Kit integration tests.
 *
 * @package MH_Dev_Kit
 */

declare(strict_types=1);

namespace MH\DevKit\Tests;

abstract class TestCase extends \WP_UnitTestCase {

	protected function setUp(): void {
		parent::setUp();
	}

	protected function create_user_with_role( string $role ): int {
		return self::factory()->user->create( array( 'role' => $role ) );
	}

	protected function acting_as( int $user_id ): void {
		wp_set_current_user( $user_id );
	}
}
