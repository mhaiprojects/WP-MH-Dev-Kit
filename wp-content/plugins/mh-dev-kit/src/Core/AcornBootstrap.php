<?php
/**
 * Acorn application bootstrap for MH Dev Kit.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

use Roots\Acorn\Application;

final class AcornBootstrap {

	private static bool $booted = false;

	public static function boot(): void {
		if ( self::$booted || ! class_exists( Application::class ) ) {
			return;
		}

		Application::configure( rtrim( MH_DEV_KIT_PATH, '/\\' ) )
			->withProviders(
				array(
					\MH\DevKit\Providers\PluginServiceProvider::class,
				)
			)
			->boot();

		self::$booted = true;
	}

	public static function is_booted(): bool {
		return self::$booted && function_exists( 'app' );
	}

	/**
	 * @template T
	 * @param class-string<T> $abstract
	 * @return T|null
	 */
	public static function make( string $abstract ): mixed {
		if ( ! self::is_booted() ) {
			return null;
		}

		return app( $abstract );
	}
}
