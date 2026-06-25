<?php
/**
 * Plugin bootstrap orchestrator.
 *
 * @package MH\DevKit\Core
 */

declare(strict_types=1);

namespace MH\DevKit\Core;

use MH\DevKit\Core\Admin\AdminMenuRegistrar;

final class Plugin {

	public static function boot(): void {
		AdminMenuRegistrar::init();
		FeatureLoader::boot();
	}
}
