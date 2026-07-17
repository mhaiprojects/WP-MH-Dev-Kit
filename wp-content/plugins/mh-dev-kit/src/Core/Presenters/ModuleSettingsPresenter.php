<?php
/**
 * Plugin-owned Modules enable/disable settings page.
 *
 * @package MH\DevKit\Core\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Models\ModuleSettingsModel;
use MH\DevKit\Core\ModuleLoader;
use MH\DevKit\Core\ViewRenderer;

final class ModuleSettingsPresenter {

	public const PAGE_SLUG = 'mhdk-settings-modules';

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_submenu' ), 10 );
	}

	public function register_submenu(): void {
		add_submenu_page(
			AdminMenu::SETTINGS_SLUG,
			__( 'Modules', AdminMenu::TEXT_DOMAIN ),
			__( 'Modules', AdminMenu::TEXT_DOMAIN ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	public function register_settings(): void {
		$model = new ModuleSettingsModel();

		register_setting(
			'mhdk_modules',
			ModuleSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => static function ( $input ) use ( $model ) {
					return $model->sanitize( is_array( $input ) ? $input : array(), ModuleLoader::module_classes() );
				},
				'default'           => $model->get_all( ModuleLoader::module_classes() ),
			)
		);
	}

	public function render_page(): void {
		$model   = new ModuleSettingsModel();
		$classes = ModuleLoader::module_classes();
		$modules = array();

		foreach ( $classes as $class ) {
			$instance = new $class();
			$req      = $instance->requirements();

			$modules[] = array(
				'id'          => $class::id(),
				'name'        => $class::name(),
				'description' => $class::description(),
				'requirements_met' => $req->met(),
				'requirements_messages' => $req->unmet_messages(),
			);
		}

		ViewRenderer::display(
			'Core/Views/admin/modules-page.php',
			array(
				'option_key' => ModuleSettingsModel::OPTION_KEY,
				'page_slug'  => self::PAGE_SLUG,
				'settings'   => $model->get_all( $classes ),
				'modules'    => $modules,
				'version'    => MH_DEV_KIT_VERSION,
			)
		);
	}
}
