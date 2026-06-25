<?php
/**
 * Allows features to register settings subpages under MHDK Settings.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

interface FeatureAdminContributorInterface {

	/**
	 * Register settings submenus and Settings API fields.
	 */
	public function register_admin( AdminMenuRegistrar $registrar ): void;
}
