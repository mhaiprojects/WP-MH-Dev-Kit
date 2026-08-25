<?php
/**
 * WooCommerce frontend filters for hybrid pricing.
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

final class HybridPricingFrontendPresenter {

	private ?PriceResolver $resolver = null;

	private ?CatalogVisibilityService $visibility = null;

	public function register(): void {
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_price_html' ), 99, 2 );
		add_filter( 'woocommerce_product_get_price', array( $this, 'filter_product_price' ), 99, 2 );
		add_filter( 'woocommerce_product_get_regular_price', array( $this, 'filter_product_price' ), 99, 2 );
		add_filter( 'woocommerce_product_is_visible', array( $this, 'filter_product_visible' ), 99, 2 );
		add_action( 'woocommerce_product_query', array( $this, 'exclude_b2b_only_from_query' ) );
	}

	public function filter_price_html( string $price, WC_Product $product ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $price;
		}

		if ( $this->visibility()->should_hide_from_current_user( (int) $product->get_id() ) ) {
			return '';
		}

		if ( $this->resolver()->should_hide_price_for_product( (int) $product->get_id() ) ) {
			return $this->resolver()->get_hidden_price_html();
		}

		$resolved = $this->resolver()->resolve_price( $product );
		if ( null === $resolved ) {
			return $price;
		}

		return wc_price( $resolved );
	}

	public function filter_product_price( mixed $price, WC_Product $product ): mixed {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $price;
		}

		$resolved = $this->resolver()->resolve_price( $product );

		return null !== $resolved ? (string) $resolved : $price;
	}

	public function filter_product_visible( bool $visible, int $product_id ): bool {
		if ( ! $visible ) {
			return false;
		}

		if ( $this->visibility()->should_hide_from_current_user( $product_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param \WP_Query $query Product query.
	 */
	public function exclude_b2b_only_from_query( $query ): void {
		if ( HybridPricingSettingsModel::current_user_is_b2b() ) {
			return;
		}

		if ( ! (bool) ( new HybridPricingSettingsModel() )->get( 'hide_b2b_only_from_b2c', true ) ) {
			return;
		}

		$meta_query   = (array) $query->get( 'meta_query' );
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => '_mhdk_b2b_only',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_mhdk_b2b_only',
				'value'   => 'yes',
				'compare' => '!=',
			),
		);
		$query->set( 'meta_query', $meta_query );
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
