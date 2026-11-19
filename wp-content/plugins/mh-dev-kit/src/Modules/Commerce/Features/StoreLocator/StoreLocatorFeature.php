<?php
/**
 * Store Locator feature entry.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters\StoreLocatorCheckoutPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters\StoreLocatorPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters\StoreLocatorRestPresenter;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters\StoreLocatorSeedPresenter;

final class StoreLocatorFeature implements FeatureInterface {

	public static function id(): string {
		return 'store-locator';
	}

	public static function name(): string {
		return 'Store Locator';
	}

	public function register(): void {
		$stores    = new StoreLocatorModel();
		$settings  = new StoreLocatorSettingsModel();
		$inventory = new InventoryModel( $stores );

		( new StoreLocatorSeedPresenter( $stores ) )->register();
		( new StoreLocatorPresenter( $stores, $settings, $inventory ) )->register();
		( new StoreLocatorRestPresenter( $stores, $settings, $inventory ) )->register();
		( new StoreLocatorCheckoutPresenter( $stores, $settings, $inventory ) )->register();
	}
}
