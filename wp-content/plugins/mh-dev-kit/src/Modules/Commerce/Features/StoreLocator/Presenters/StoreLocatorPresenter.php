<?php
/**
 * Store Locator shortcode and frontend assets.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class StoreLocatorPresenter {

	public function __construct(
		private StoreLocatorModel $stores,
		private StoreLocatorSettingsModel $settings,
		private ?InventoryModel $inventory = null
	) {}

	public function register(): void {
		add_shortcode( 'mhdk_store_locator', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'product_id' => '0',
			),
			is_array( $atts ) ? $atts : array(),
			'mhdk_store_locator'
		);

		$product_id = (int) $atts['product_id'];

		if ( $product_id <= 0 && isset( $_GET['product_id'] ) ) {
			$product_id = (int) $_GET['product_id'];
		}

		$stores     = $this->stores->get_active();

		if ( $product_id > 0 && null !== $this->inventory ) {
			foreach ( $stores as $index => $store ) {
				$qty = $this->inventory->get_stock( $product_id, (string) $store['id'] );
				$stores[ $index ]['stock_quantity'] = $qty;
				$stores[ $index ]['has_stock']      = $qty > 0;
			}

			usort(
				$stores,
				static function ( array $a, array $b ): int {
					$a_stock = (int) ( $a['stock_quantity'] ?? 0 ) > 0 ? 1 : 0;
					$b_stock = (int) ( $b['stock_quantity'] ?? 0 ) > 0 ? 1 : 0;

					return $b_stock <=> $a_stock;
				}
			);
		}

		$this->enqueue_shortcode_assets( $product_id );

		return ViewRenderer::render(
			'Modules/Commerce/Features/StoreLocator/Views/shortcode.php',
			array(
				'stores'          => $stores,
				'show_phone'      => (bool) $this->settings->get( 'show_phone', true ),
				'show_hours'      => (bool) $this->settings->get( 'show_hours', true ),
				'max_distance_km' => (int) $this->settings->get( 'max_distance_km', 500 ),
				'product_id'      => $product_id,
				'stores_model'    => $this->stores,
			)
		);
	}

	public function enqueue_assets(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'mhdk_store_locator' ) ) {
			$this->enqueue_shortcode_assets( 0 );
		}
	}

	private function enqueue_shortcode_assets( int $product_id = 0 ): void {
		AssetRegistrar::enqueue_style(
			'mhdk-store-locator',
			'Modules/Commerce/Features/StoreLocator',
			'css/store-locator.css'
		);

		AssetRegistrar::enqueue_script(
			'mhdk-store-locator',
			'Modules/Commerce/Features/StoreLocator',
			'js/store-locator.js',
			array(),
			true
		);

		wp_localize_script(
			'mhdk-store-locator',
			'mhdkStoreLocator',
			array(
				'restUrl'       => rest_url( 'mhdk/v1/stores/nearby' ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
				'maxDistanceKm' => (int) $this->settings->get( 'max_distance_km', 500 ),
				'productId'     => $product_id,
				'i18n'          => array(
					'prompt'        => __( 'Allow location access to find the nearest store.', 'mh-dev-kit' ),
					'waiting'       => __( 'Finding stores near you…', 'mh-dev-kit' ),
					'error'         => __( 'Could not read your location.', 'mh-dev-kit' ),
					'unsupported'   => __( 'Geolocation is not supported in this browser. Use the filters below.', 'mh-dev-kit' ),
					'denied'        => __( 'Location access denied. Use state or postcode filters below.', 'mh-dev-kit' ),
					'noStores'      => __( 'No stores found within range.', 'mh-dev-kit' ),
					'distance'      => __( 'About %1$s km from you', 'mh-dev-kit' ),
					'outsideRadius' => __( 'Outside delivery area (%1$s km)', 'mh-dev-kit' ),
					'selected'      => __( 'Selected store', 'mh-dev-kit' ),
					'select'        => __( 'Select this store', 'mh-dev-kit' ),
					'filtered'      => __( 'Showing stores matching your filters.', 'mh-dev-kit' ),
					'inStock'       => __( '%1$s in stock', 'mh-dev-kit' ),
					'outOfStock'    => __( 'Out of stock', 'mh-dev-kit' ),
				),
			)
		);
	}
}
