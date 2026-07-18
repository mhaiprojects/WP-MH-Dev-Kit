<?php
/**
 * Family tree data model.
 *
 * @package MH\DevKit\Modules\UserTools\Features\FamilyTree\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\UserTools\Features\FamilyTree\Models;

use MH\DevKit\Shared\UserScopedCptHelper;

final class FamilyTreeModel {

	public const POST_TYPE = 'mhdk_family_tree';

	public const META_TREE = 'mhdk_family_tree_json';

	/**
	 * @return array<string, mixed>
	 */
	public function get_post_type_args(): array {
		return UserScopedCptHelper::author_caps_args(
			__( 'Family Tree', 'mh-dev-kit' ),
			__( 'Family Trees', 'mh-dev-kit' ),
			'dashicons-groups'
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_default_tree(): array {
		return array(
			'root' => array(
				'id'       => 'root',
				'name'     => __( 'Root Person', 'mh-dev-kit' ),
				'children' => array(),
			),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_tree( int $post_id ): array {
		$raw = get_post_meta( $post_id, self::META_TREE, true );

		if ( is_string( $raw ) && '' !== $raw ) {
			$decoded = json_decode( $raw, true );

			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		if ( is_array( $raw ) ) {
			return $raw;
		}

		return $this->get_default_tree();
	}

	/**
	 * @param mixed $input Raw tree JSON string.
	 */
	public function sanitize_tree( mixed $input ): string {
		if ( ! is_string( $input ) ) {
			return wp_json_encode( $this->get_default_tree() ) ?: '{}';
		}

		$decoded = json_decode( $input, true );

		if ( ! is_array( $decoded ) ) {
			return wp_json_encode( $this->get_default_tree() ) ?: '{}';
		}

		return wp_json_encode( $decoded ) ?: '{}';
	}
}
