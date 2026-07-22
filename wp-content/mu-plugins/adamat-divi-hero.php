<?php
/**
 * Sync adamat page hero backgrounds with the page featured image.
 *
 * @package adamat
 */

declare(strict_types=1);

add_action(
	'init',
	static function (): void {
		if ( ! is_multisite() || 2 !== get_current_blog_id() ) {
			return;
		}

		$layouts = ABSPATH . 'scripts/lib/AdamatDiviLayouts.php';
		if ( ! is_readable( $layouts ) ) {
			return;
		}

		require_once $layouts;
		AdamatDiviLayouts::register_hero_sync_hooks();
	}
);
