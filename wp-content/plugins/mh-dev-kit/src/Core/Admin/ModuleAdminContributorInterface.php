<?php
/**
 * Allows modules to register subpages under MHDK Settings.
 *
 * @package MH\DevKit\Core\Admin
 */

declare(strict_types=1);

namespace MH\DevKit\Core\Admin;

interface ModuleAdminContributorInterface {

	public function register_admin( AdminMenuRegistrar $registrar ): void;
}
