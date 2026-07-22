<?php
/**
 * Seed demo inventory levels.
 *
 * @package MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\InventoryManagement\Presenters;

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;

final class InventorySeedPresenter {

	public function __construct(
		private InventoryModel $inventory
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'seed_demo_stock' ), 30 );
	}

	public function seed_demo_stock(): void {
		$this->inventory->seed_demo_stock_if_empty();
	}
}
