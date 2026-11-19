<?php
/**
 * Declarative module dependency checks.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

use MH\DevKit\Core\Models\ModuleSettingsModel;

final class ModuleRequirements {

	/** @var array<int, string> */
	private array $plugins = array();

	/** @var array<int, string> */
	private array $modules = array();

	private ?string $php = null;

	/**
	 * @param array<int, string> $plugins Plugin basename paths, e.g. woocommerce/woocommerce.php.
	 */
	public static function needs_plugins( array $plugins ): self {
		$req = new self();
		$req->plugins = $plugins;

		return $req;
	}

	public static function none(): self {
		return new self();
	}

	/**
	 * @param array<int, string> $module_ids Other MH Dev Kit module IDs that must be enabled.
	 */
	public function requires_modules( array $module_ids ): self {
		$this->modules = array_values(
			array_filter(
				array_map( 'strval', $module_ids ),
				static fn( string $id ): bool => '' !== $id
			)
		);

		return $this;
	}

	public function min_php( string $version ): self {
		$this->php = $version;

		return $this;
	}

	/**
	 * @return array<int, string>
	 */
	public function required_modules(): array {
		return $this->modules;
	}

	/**
	 * @return array<int, string>
	 */
	public function required_plugins(): array {
		return $this->plugins;
	}

	public function met(): bool {
		if ( null !== $this->php && version_compare( PHP_VERSION, $this->php, '<' ) ) {
			return false;
		}

		foreach ( $this->plugins as $plugin ) {
			if ( ! is_string( $plugin ) || '' === $plugin ) {
				continue;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! is_plugin_active( $plugin ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array<int, class-string<ModuleInterface>> $module_classes Registered modules.
	 */
	public function module_dependencies_met( ModuleSettingsModel $settings, array $module_classes ): bool {
		foreach ( $this->modules as $module_id ) {
			if ( ! $settings->is_enabled( $module_id, $module_classes ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array<int, string>
	 */
	public function unmet_messages(): array {
		$messages = array();

		if ( null !== $this->php && version_compare( PHP_VERSION, $this->php, '<' ) ) {
			$messages[] = sprintf(
				/* translators: %s: required PHP version */
				__( 'PHP %s or newer is required.', 'mh-dev-kit' ),
				$this->php
			);
		}

		foreach ( $this->plugins as $plugin ) {
			if ( ! is_string( $plugin ) || '' === $plugin ) {
				continue;
			}

			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( ! is_plugin_active( $plugin ) ) {
				$messages[] = sprintf(
					/* translators: %s: plugin basename */
					__( 'Required plugin is not active: %s', 'mh-dev-kit' ),
					$plugin
				);
			}
		}

		return $messages;
	}

	/**
	 * @param array<int, class-string<ModuleInterface>> $module_classes Registered modules.
	 * @return array<int, string>
	 */
	public function unmet_module_messages( ModuleSettingsModel $settings, array $module_classes ): array {
		$messages = array();
		$names    = self::module_name_map( $module_classes );

		foreach ( $this->modules as $module_id ) {
			if ( $settings->is_enabled( $module_id, $module_classes ) ) {
				continue;
			}

			$label = $names[ $module_id ] ?? $module_id;

			$messages[] = sprintf(
				/* translators: %s: module name */
				__( 'Required module is disabled: %s', 'mh-dev-kit' ),
				$label
			);
		}

		return $messages;
	}

	/**
	 * @param array<int, class-string<ModuleInterface>> $module_classes Registered modules.
	 * @return array<string, string>
	 */
	public static function module_name_map( array $module_classes ): array {
		$map = array();

		foreach ( $module_classes as $class ) {
			$map[ $class::id() ] = $class::name();
		}

		return $map;
	}
}
