<?php
/**
 * Registers MHDK Settings and MHDK Content menus; collects feature contributions.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

use MH\DevKit\Core\ViewRenderer;

final class AdminMenuRegistrar {

	/** @var array<int, FeatureAdminContributorInterface> */
	private static array $contributors = array();

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'register_top_level_menus' ), 5 );
		add_action( 'admin_menu', array( self::class, 'dispatch_feature_admin' ), 15 );
	}

	public static function register_contributor( FeatureAdminContributorInterface $contributor ): void {
		self::$contributors[] = $contributor;
	}

	public static function register_top_level_menus(): void {
		add_menu_page(
			__( 'MHDK Settings', AdminMenu::TEXT_DOMAIN ),
			__( 'MHDK Settings', AdminMenu::TEXT_DOMAIN ),
			'manage_options',
			AdminMenu::SETTINGS_SLUG,
			array( self::class, 'render_settings_dashboard' ),
			'dashicons-admin-tools',
			57
		);

		add_menu_page(
			__( 'MHDK Content', AdminMenu::TEXT_DOMAIN ),
			__( 'MHDK Content', AdminMenu::TEXT_DOMAIN ),
			'edit_posts',
			AdminMenu::CONTENT_SLUG,
			array( self::class, 'render_content_hub' ),
			'dashicons-archive',
			58
		);
	}

	public static function dispatch_feature_admin(): void {
		$registrar = new self();

		foreach ( self::$contributors as $contributor ) {
			$contributor->register_admin( $registrar );
		}
	}

	/**
	 * @param callable(): void $render_page Page callback.
	 */
	public function add_settings_submenu(
		string $slug,
		string $menu_title,
		string $page_title,
		callable $render_page,
		string $capability = 'manage_options'
	): void {
		add_submenu_page(
			AdminMenu::SETTINGS_SLUG,
			$page_title,
			$menu_title,
			$capability,
			$slug,
			$render_page
		);
	}

	public static function render_settings_dashboard(): void {
		ViewRenderer::display(
			'Core/Views/admin/settings-dashboard.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}

	public static function render_content_hub(): void {
		/**
		 * Fires when rendering the MHDK Content hub; features may output links.
		 *
		 * @param AdminMenuRegistrar $registrar Registry instance.
		 */
		do_action( 'mhdk_render_content_hub', new self() );

		ViewRenderer::display(
			'Core/Views/admin/content-hub.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}
}
