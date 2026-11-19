<?php
/**
 * Inventory Management feature entry.
 *
 * @package MH\DevKit\Modules\Commerce\Features\InventoryManagement
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\InventoryManagement;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters\InventoryProductPresenter;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters\InventoryRestPresenter;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters\InventorySeedPresenter;

final class InventoryManagementFeature implements FeatureInterface {

	public static function id(): string {
		return 'inventory-management';
	}

	public static function name(): string {
		return 'Inventory Management';
	}

	public function register(): void {
		$inventory = new InventoryModel();
		$settings  = new InventorySettingsModel();

		( new InventorySeedPresenter( $inventory ) )->register();
		( new InventoryProductPresenter( $inventory, $settings ) )->register();
		( new InventoryRestPresenter( $inventory ) )->register();
	}
}
