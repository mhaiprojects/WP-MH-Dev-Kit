<?php
/**
 * Module enable/disable option storage.
 *
 * @package MH\DevKit\Core\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Models;

final class ModuleSettingsModel {

	public const OPTION_KEY = 'mhdk_module_settings';

	/**
	 * @param array<int, class-string<\MH\DevKit\Core\ModuleInterface>> $module_classes Registered modules.
	 * @return array<string, array{enabled: bool}>
	 */
	public function get_all( array $module_classes ): array {
		$stored = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$settings = array();

		foreach ( $module_classes as $class ) {
			if ( ! is_subclass_of( $class, \MH\DevKit\Core\ModuleInterface::class ) ) {
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
	 * @param array<int, class-string<\MH\DevKit\Core\ModuleInterface>> $module_classes Registered modules.
	 * @return array<string, array{enabled: bool}>
	 */
	public function sanitize( array $input, array $module_classes ): array {
		$clean = $this->get_all( $module_classes );

		foreach ( $clean as $id => $row ) {
			$clean[ $id ]['enabled'] = ! empty( $input[ $id ]['enabled'] );
		}

		return $clean;
	}
}
