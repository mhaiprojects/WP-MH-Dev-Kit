<?php
/**
 * Module enable/disable option storage.
 *
 * @package MH\DevKit\Core\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Models;

use MH\DevKit\Core\ModuleInterface;
use MH\DevKit\Core\ModuleRequirements;

final class ModuleSettingsModel {

	public const OPTION_KEY = 'mhdk_module_settings';

	/**
	 * @param array<int, class-string<ModuleInterface>> $module_classes Registered modules.
	 * @return array<string, array{enabled: bool}>
	 */
	public function get_all( array $module_classes ): array {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings = array();

		foreach ( $module_classes as $class ) {
			if ( ! is_subclass_of( $class, ModuleInterface::class ) ) {
				continue;
			}

			$id = $class::id();

			$settings[ $id ] = array(
				'enabled' => ! isset( $stored[ $id ]['enabled'] ) || ! empty( $stored[ $id ]['enabled'] ),
			);
		}

		return $settings;
	}

	public function is_enabled( string $module_id, array $module_classes ): bool {
		$all = $this->get_all( $module_classes );

		return ! empty( $all[ $module_id ]['enabled'] );
	}

	/**
	 * @param array<string, mixed> $input Raw form input.
	 * @param array<int, class-string<ModuleInterface>> $module_classes Registered modules.
	 * @return array<string, array{enabled: bool}>
	 */
	public function sanitize( array $input, array $module_classes ): array {
		$clean = $this->get_all( $module_classes );
		$names = ModuleRequirements::module_name_map( $module_classes );

		foreach ( $clean as $id => $row ) {
			$clean[ $id ]['enabled'] = ! empty( $input[ $id ]['enabled'] );
		}

		foreach ( $clean as $id => $row ) {
			if ( empty( $row['enabled'] ) ) {
				continue;
			}

			$class = $this->find_class_by_id( $id, $module_classes );

			if ( null === $class ) {
				continue;
			}

			$req = ( new $class() )->requirements();

			foreach ( $req->required_modules() as $dep_id ) {
				if ( ! empty( $clean[ $dep_id ]['enabled'] ) ) {
					continue;
				}

				$dep_label = $names[ $dep_id ] ?? $dep_id;

				add_settings_error(
					self::OPTION_KEY,
					'mhdk_module_dep_' . $id,
					sprintf(
						/* translators: 1: module name, 2: required module name */
						__( '%1$s requires %2$s to be enabled.', 'mh-dev-kit' ),
						$names[ $id ] ?? $id,
						$dep_label
					),
					'error'
				);

				$clean[ $id ]['enabled'] = false;
			}
		}

		foreach ( $clean as $id => $row ) {
			if ( ! empty( $row['enabled'] ) ) {
				continue;
			}

			foreach ( $this->get_dependents( $id, $module_classes ) as $dependent_class ) {
				$dependent_id = $dependent_class::id();

				if ( empty( $clean[ $dependent_id ]['enabled'] ) ) {
					continue;
				}

				add_settings_error(
					self::OPTION_KEY,
					'mhdk_module_dependent_' . $dependent_id,
					sprintf(
						/* translators: 1: module name, 2: dependent module name */
						__( '%1$s cannot be disabled while %2$s is enabled.', 'mh-dev-kit' ),
						$names[ $id ] ?? $id,
						$dependent_class::name()
					),
					'error'
				);

				$clean[ $id ]['enabled'] = true;
			}
		}

		return $clean;
	}

	/**
	 * @param array<int, class-string<ModuleInterface>> $module_classes Registered modules.
	 * @return array<int, class-string<ModuleInterface>>
	 */
	public function get_dependents( string $module_id, array $module_classes ): array {
		$dependents = array();

		foreach ( $module_classes as $class ) {
			$req = ( new $class() )->requirements();

			if ( in_array( $module_id, $req->required_modules(), true ) ) {
				$dependents[] = $class;
			}
		}

		return $dependents;
	}

	/**
	 * @param array<int, class-string<ModuleInterface>> $module_classes Registered modules.
	 * @return class-string<ModuleInterface>|null
	 */
	public function find_class_by_id( string $module_id, array $module_classes ): ?string {
		foreach ( $module_classes as $class ) {
			if ( $class::id() === $module_id ) {
				return $class;
			}
		}

		return null;
	}
}
