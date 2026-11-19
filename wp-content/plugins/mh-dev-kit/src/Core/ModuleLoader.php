<?php
/**
 * Loads registered modules and their features.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\ModuleAdminContributorInterface;
use MH\DevKit\Core\Models\ModuleSettingsModel;

final class ModuleLoader {

	/** @var array<int, class-string<ModuleInterface>>|null */
	private static ?array $module_classes = null;

	/**
	 * @return array<int, class-string<ModuleInterface>>
	 */
	public static function module_classes(): array {
		if ( null !== self::$module_classes ) {
			return self::$module_classes;
		}

		$config = MH_DEV_KIT_PATH . 'config/modules.php';

		if ( ! is_readable( $config ) ) {
			self::$module_classes = array();

			return self::$module_classes;
		}

		/** @var array<int, class-string<ModuleInterface>> $modules */
		$modules = require $config;

		self::$module_classes = array_values(
			array_filter(
				$modules,
				static fn( $class ): bool => is_string( $class ) && is_subclass_of( $class, ModuleInterface::class )
			)
		);

		return self::$module_classes;
	}

	public static function boot(): void {
		$settings = new ModuleSettingsModel();

		foreach ( self::module_classes() as $class ) {
			$id = $class::id();

			if ( ! $settings->is_enabled( $id, self::module_classes() ) ) {
				continue;
			}

			$module = new $class();
			$req    = $module->requirements();

			if ( ! $req->met() ) {
				self::register_requirements_notice( $class::name(), $req->unmet_messages() );
				continue;
			}

			if ( ! $req->module_dependencies_met( $settings, self::module_classes() ) ) {
				self::register_requirements_notice( $class::name(), $req->unmet_module_messages( $settings, self::module_classes() ) );
				continue;
			}

			$module->register();

			if ( $module instanceof ModuleAdminContributorInterface ) {
				AdminMenuRegistrar::register_contributor( $module );
			}
		}
	}

	private static function register_requirements_notice( string $module_name, array $messages ): void {
		if ( array() === $messages ) {
			return;
		}

		add_action(
			'admin_notices',
			static function () use ( $module_name, $messages ): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				printf(
					'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
					esc_html(
						sprintf(
							/* translators: %s: module name */
							__( 'MH Dev Kit module "%s" is not loaded:', 'mh-dev-kit' ),
							$module_name
						)
					),
					esc_html( implode( ' ', $messages ) )
				);
			}
		);
	}
}
