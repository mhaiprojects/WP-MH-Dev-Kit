<?php
/**
 * Order Menu feature entry.
 *
 * @package MH\DevKit\Features\OrderMenu
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Features\OrderMenu\Presenters\OrderMenuPresenter;

final class OrderMenuFeature implements FeatureInterface {

	public static function id(): string {
		return 'order-menu';
	}

	public static function name(): string {
		return 'Order Menu';
	}

	public function register(): void {
		if ( ! $this->woocommerce_active() ) {
			return;
		}

		( new OrderMenuPresenter() )->register();
	}

	private function woocommerce_active(): bool {
		return class_exists( 'WooCommerce' );
	}
}
