<?php
/**
 * Order Menu feature entry.
 *
 * @package MH\DevKit\Modules\Commerce\Features\OrderMenu
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\OrderMenu;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters\OrderMenuBlockPresenter;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters\OrderMenuPresenter;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters\OrderMetaPresenter;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters\SavedMenuPresenter;

final class OrderMenuFeature implements FeatureInterface {

	public static function id(): string {
		return 'order-menu';
	}

	public static function name(): string {
		return 'Order Menu';
	}

	public function register(): void {
		( new OrderMenuPresenter() )->register();
		( new SavedMenuPresenter() )->register();
		( new OrderMenuBlockPresenter() )->register();
		( new OrderMetaPresenter() )->register();
	}
}
