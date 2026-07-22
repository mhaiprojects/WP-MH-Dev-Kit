<?php
/**
 * Store Locator shortcode and frontend assets.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class StoreLocatorPresenter {

	public function __construct(
		private StoreLocatorModel $stores,
		private StoreLocatorSettingsModel $settings
	) {}

	public function register(): void {
		add_shortcode( 'mhdk_store_locator', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		unset( $atts );

		$this->enqueue_shortcode_assets();

		return ViewRenderer::render(
			'Modules/Commerce/Features/StoreLocator/Views/shortcode.php',
			array(
				'stores'      => $this->stores->get_active(),
				'show_phone'  => (bool) $this->settings->get( 'show_phone', true ),
				'show_hours'  => (bool) $this->settings->get( 'show_hours', true ),
				'max_distance_km' => (int) $this->settings->get( 'max_distance_km', 500 ),
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
			$this->enqueue_shortcode_assets();
		}
	}

	private function enqueue_shortcode_assets(): void {
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
				'i18n'          => array(
					'prompt'      => __( 'Allow location access to find the nearest store.', 'mh-dev-kit' ),
					'waiting'     => __( 'Finding stores near you…', 'mh-dev-kit' ),
					'error'       => __( 'Could not read your location.', 'mh-dev-kit' ),
					'unsupported' => __( 'Geolocation is not supported in this browser.', 'mh-dev-kit' ),
					'noStores'    => __( 'No stores found within range.', 'mh-dev-kit' ),
					'distance'    => __( '%1$s km away', 'mh-dev-kit' ),
					'selected'    => __( 'Selected store', 'mh-dev-kit' ),
					'select'      => __( 'Select this store', 'mh-dev-kit' ),
				),
			)
		);
	}
}
