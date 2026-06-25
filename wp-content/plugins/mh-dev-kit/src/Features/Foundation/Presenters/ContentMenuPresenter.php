<?php
/**
 * MH Dev Kit Content top-level admin menu shell.
 *
 * @package MH\DevKit\Features\Foundation\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\Foundation\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Features\Foundation\Models\FoundationSettingsModel;

final class ContentMenuPresenter {

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 9 );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'MH Dev Kit Content', 'mh-dev-kit' ),
			__( 'MH Dev Kit Content', 'mh-dev-kit' ),
			'edit_posts',
			FoundationSettingsModel::CONTENT_MENU_SLUG,
			array( $this, 'render_page' ),
			'dashicons-archive',
			58
		);
	}

	public function render_page(): void {
		ViewRenderer::display(
			'Foundation/Views/content-menu-page.php',
			array(
				'menu_slug' => FoundationSettingsModel::CONTENT_MENU_SLUG,
			)
		);
	}
}
