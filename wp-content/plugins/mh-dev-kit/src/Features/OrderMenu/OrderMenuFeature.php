<?php
/**
 * Order Menu feature entry.
 *
 * @package MH\DevKit\Features\OrderMenu
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu;

use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\FeatureAdminContributorInterface;
use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Features\OrderMenu\Presenters\OrderMenuAdminPresenter;
use MH\DevKit\Features\OrderMenu\Presenters\OrderMenuBlockPresenter;
use MH\DevKit\Features\OrderMenu\Presenters\OrderMenuPresenter;
use MH\DevKit\Features\OrderMenu\Presenters\OrderMetaPresenter;
use MH\DevKit\Features\OrderMenu\Presenters\SavedMenuPresenter;

final class OrderMenuFeature implements FeatureInterface, FeatureAdminContributorInterface {

	private OrderMenuAdminPresenter $admin;

	public static function id(): string {
		return 'order-menu';
	}

	public static function name(): string {
		return 'Order Menu';
	}

	public function register(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->admin = new OrderMenuAdminPresenter();

		( new OrderMenuPresenter() )->register();
		$this->admin->register();
		( new SavedMenuPresenter() )->register();
		( new OrderMenuBlockPresenter() )->register();
		( new OrderMetaPresenter() )->register();
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$this->admin->register_admin( $registrar );
	}
}
