<?php
/**
 * Hybrid B2B/B2C pricing feature entry.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing;

use MH\DevKit\Core\FeatureInterface;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\PriceListModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters\HybridPricingAdminPresenter;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters\HybridPricingCartPresenter;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters\HybridPricingFrontendPresenter;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters\HybridPricingProductPresenter;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters\HybridPricingUserPresenter;

final class HybridPricingFeature implements FeatureInterface {

	public static function id(): string {
		return 'hybrid-pricing';
	}

	public static function name(): string {
		return 'Hybrid B2B/B2C Pricing';
	}

	public function register(): void {
		add_action( 'init', array( $this, 'register_roles' ) );
		add_action( 'init', array( $this, 'bootstrap_defaults' ), 5 );

		$settings = new HybridPricingSettingsModel();
		$price_lists = new PriceListModel();

		( new HybridPricingFrontendPresenter() )->register();
		( new HybridPricingCartPresenter() )->register();
		( new HybridPricingProductPresenter() )->register();
		( new HybridPricingUserPresenter( $price_lists ) )->register();
		( new HybridPricingAdminPresenter( $settings, $price_lists ) )->register();
	}

	public function bootstrap_defaults(): void {
		( new PriceListModel() )->ensure_defaults();
	}

	public function register_roles(): void {
		if ( get_role( HybridPricingSettingsModel::B2B_ROLE ) ) {
			return;
		}

		add_role(
			HybridPricingSettingsModel::B2B_ROLE,
			__( 'B2B Customer', 'mh-dev-kit' ),
			array(
				'read'                                      => true,
				HybridPricingSettingsModel::CAP_VIEW_WHOLESALE => true,
				HybridPricingSettingsModel::CAP_VIEW_B2B_CATALOG => true,
			)
		);
	}
}
