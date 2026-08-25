<?php
/**
 * Cart, checkout, and purchase guards for hybrid pricing.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters;

use MH\DevKit\Core\AcornBootstrap;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\PriceListModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Services\CatalogVisibilityService;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Services\PriceResolver;
use WC_Product;

final class HybridPricingCartPresenter {

	private ?PriceResolver $resolver = null;

	private ?CatalogVisibilityService $visibility = null;

	public function register(): void {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_cart_prices' ), 20 );
		add_filter( 'woocommerce_is_purchasable', array( $this, 'filter_is_purchasable' ), 99, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 99, 3 );
		add_filter( 'woocommerce_rest_prepare_product_object', array( $this, 'filter_rest_product' ), 99, 3 );
	}

	public function apply_cart_prices( \WC_Cart $cart ): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		if ( ! (bool) ( new HybridPricingSettingsModel() )->get( 'apply_custom_prices_cart', true ) ) {
			return;
		}

		if ( ! HybridPricingSettingsModel::current_user_is_b2b() ) {
			return;
		}

		foreach ( $cart->get_cart() as $item ) {
			$product = $item['data'] ?? null;
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$resolved = $this->resolver()->resolve_price( $product );
			if ( null === $resolved ) {
				continue;
			}

			$product->set_price( (string) $resolved );
		}
	}

	public function filter_is_purchasable( bool $purchasable, WC_Product $product ): bool {
		if ( ! $purchasable ) {
			return false;
		}

		if ( $this->visibility()->should_hide_from_current_user( (int) $product->get_id() ) ) {
			return false;
		}

		if ( $this->resolver()->should_hide_price_for_product( (int) $product->get_id() ) ) {
			return false;
		}

		return true;
	}

	public function validate_add_to_cart( bool $passed, int $product_id, int $quantity ): bool {
		if ( ! $passed ) {
			return false;
		}

		if ( $this->visibility()->should_hide_from_current_user( $product_id ) ) {
			wc_add_notice(
				__( 'This product is available to trade accounts only.', 'mh-dev-kit' ),
				'error'
			);

			return false;
		}

		if ( $this->resolver()->should_hide_price_for_product( $product_id ) ) {
			wc_add_notice(
				__( 'Please log in with a trade account to purchase this product.', 'mh-dev-kit' ),
				'error'
			);

			return false;
		}

		return true;
	}

	/**
	 * @param \WP_REST_Response $response REST response.
	 * @param \WC_Product       $product  Product.
	 * @param \WP_REST_Request  $request  Request.
	 */
	public function filter_rest_product( $response, $product, $request ) {
		if ( ! $response instanceof \WP_REST_Response || ! $product instanceof WC_Product ) {
			return $response;
		}

		$product_id = (int) $product->get_id();
		$data       = $response->get_data();

		if ( $this->visibility()->should_hide_from_current_user( $product_id ) ) {
			return new \WP_Error(
				'mhdk_b2b_only',
				__( 'Product not available.', 'mh-dev-kit' ),
				array( 'status' => 404 )
			);
		}

		if ( $this->resolver()->should_hide_price_for_product( $product_id ) ) {
			unset( $data['price'], $data['regular_price'], $data['sale_price'], $data['price_html'] );
			$response->set_data( $data );

			return $response;
		}

		$resolved = $this->resolver()->resolve_price( $product );
		if ( null !== $resolved ) {
			$data['price']         = (string) $resolved;
			$data['regular_price'] = (string) $resolved;
			$data['price_html']    = wc_price( $resolved );
			$response->set_data( $data );
		}

		return $response;
	}

	private function resolver(): PriceResolver {
		if ( null === $this->resolver ) {
			$this->resolver = AcornBootstrap::make( PriceResolver::class )
				?? new PriceResolver( new HybridPricingSettingsModel(), new PriceListModel() );
		}

		return $this->resolver;
	}

	private function visibility(): CatalogVisibilityService {
		if ( null === $this->visibility ) {
			$this->visibility = AcornBootstrap::make( CatalogVisibilityService::class )
				?? new CatalogVisibilityService( new HybridPricingSettingsModel() );
		}

		return $this->visibility;
	}
}
