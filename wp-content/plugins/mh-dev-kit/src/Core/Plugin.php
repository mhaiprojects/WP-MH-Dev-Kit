<?php
/**
 * Plugin bootstrap orchestrator.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

final class Plugin {

	public static function boot(): void {
		FeatureLoader::boot();
	}
}
