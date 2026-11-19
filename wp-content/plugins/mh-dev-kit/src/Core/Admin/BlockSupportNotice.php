<?php
/**
 * Admin notice when block content exists but Content Tools is disabled.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

use MH\DevKit\Core\Models\ModuleSettingsModel;
use MH\DevKit\Core\ModuleLoader;
use MH\DevKit\Modules\ContentTools\Features\MarkdownEditor\Models\MarkdownModel;

final class BlockSupportNotice {

	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'maybe_render' ) );
	}

	public static function maybe_render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = new ModuleSettingsModel();

		if ( $settings->is_enabled( 'content-tools', ModuleLoader::module_classes() ) ) {
			return;
		}

		if ( ! self::site_has_unsupported_blocks() ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			wp_kses_post(
				sprintf(
					/* translators: %s: Modules settings URL */
					__( 'MH Dev Kit block content (including <strong>MH Markdown</strong>) is present but the <strong>Content Tools</strong> module is disabled. Enable it under <a href="%s">MHDK Settings → Modules</a>.', 'mh-dev-kit' ),
					esc_url( admin_url( 'admin.php?page=mhdk-settings-modules' ) )
				)
			)
		);
	}

	private static function site_has_unsupported_blocks(): bool {
		global $wpdb;

		$like = '%' . $wpdb->esc_like( '<!-- wp:' . MarkdownModel::BLOCK_NAME ) . '%';

		$post_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_status IN ('publish','draft','pending','future','private') LIMIT 1",
				$like
			)
		);

		return $post_id > 0;
	}
}
