<?php
/**
 * Loads registered feature modules.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

final class FeatureLoader {

	/**
	 * Boot all features listed in config/features.php.
	 */
	public static function boot(): void {
		$config = MH_DEV_KIT_PATH . 'config/features.php';

		if ( ! is_readable( $config ) ) {
			return;
		}

		/** @var array<int, class-string<FeatureInterface>> $features */
		$features = require $config;

		foreach ( $features as $class ) {
			if ( ! is_subclass_of( $class, FeatureInterface::class ) ) {
				continue;
			}

			( new $class() )->register();
		}
	}
}
