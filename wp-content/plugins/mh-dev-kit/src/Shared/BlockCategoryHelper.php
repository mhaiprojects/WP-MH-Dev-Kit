<?php
/**
 * Registers the MHDK Gutenberg block category once.
 *
 * @package MH\DevKit\Shared
 */

declare(strict_types=1);

namespace MH\DevKit\Shared;

use MH\DevKit\Core\Admin\AdminMenu;

final class BlockCategoryHelper {

	private static bool $registered = false;

	public static function register(): void {
		if ( self::$registered ) {
			return;
		}

		self::$registered = true;

		add_filter( 'block_categories_all', array( self::class, 'prepend_category' ), 10, 2 );
		add_filter( 'block_categories', array( self::class, 'prepend_category_legacy' ), 10, 2 );
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	public static function prepend_category( array $categories, \WP_Block_Editor_Context $context ): array {
		unset( $context );

		return self::ensure_category( $categories );
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	public static function prepend_category_legacy( array $categories, \WP_Post $post ): array {
		unset( $post );

		return self::ensure_category( $categories );
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	private static function ensure_category( array $categories ): array {
		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && AdminMenu::BLOCK_CATEGORY === $category['slug'] ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			array(
				'slug'  => AdminMenu::BLOCK_CATEGORY,
				'title' => __( 'MHDK', AdminMenu::TEXT_DOMAIN ),
				'icon'  => null,
			)
		);

		return $categories;
	}
}
