<?php
/**
 * Registers MHDK Settings top-level menu; modules add subpages only.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

use MH\DevKit\Core\ViewRenderer;

final class AdminMenuRegistrar {

	/** @var array<int, ModuleAdminContributorInterface> */
	private static array $contributors = array();

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'register_top_level_menu' ), 5 );
		add_action( 'admin_menu', array( self::class, 'dispatch_module_admin' ), 15 );
	}

	public static function register_contributor( ModuleAdminContributorInterface $contributor ): void {
		self::$contributors[] = $contributor;
	}

	public static function register_top_level_menu(): void {
		add_menu_page(
			__( 'MHDK Settings', AdminMenu::TEXT_DOMAIN ),
			__( 'MHDK Settings', AdminMenu::TEXT_DOMAIN ),
			'manage_options',
			AdminMenu::SETTINGS_SLUG,
			array( self::class, 'render_settings_dashboard' ),
			'dashicons-admin-tools',
			57
		);
	}

	public static function dispatch_module_admin(): void {
		$registrar = new self();

		foreach ( self::$contributors as $contributor ) {
			$contributor->register_admin( $registrar );
		}
	}

	/**
	 * @param callable(): void $render_page Page callback.
	 */
	public function add_submenu(
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

	/** @deprecated Use add_submenu() */
	public function add_settings_submenu(
		string $slug,
		string $menu_title,
		string $page_title,
		callable $render_page,
		string $capability = 'manage_options'
	): void {
		$this->add_submenu( $slug, $menu_title, $page_title, $render_page, $capability );
	}

	public static function render_settings_dashboard(): void {
		ViewRenderer::display(
			'Core/Views/admin/settings-dashboard.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}
}
