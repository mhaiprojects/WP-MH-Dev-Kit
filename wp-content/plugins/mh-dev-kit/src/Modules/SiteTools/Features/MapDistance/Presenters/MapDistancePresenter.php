<?php
/**
 * Map distance shortcode and demo page.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\MapDistance\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\MapDistance\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\Models\MapDistanceSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class MapDistancePresenter {

	public function __construct(
		private MapDistanceSettingsModel $settings
	) {}

	public function register(): void {
		add_shortcode( 'mhdk_map_distance', array( $this, 'render_shortcode' ) );
		add_action( 'init', array( $this, 'maybe_create_demo_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode attributes.
	 */
	public function render_shortcode( $atts = array() ): string {
		unset( $atts );

		$this->enqueue_shortcode_assets();

		return ViewRenderer::render(
			'Modules/SiteTools/Features/MapDistance/Views/shortcode.php',
			array(
				'start_label' => (string) $this->settings->get( 'start_label', '' ),
				'start_lat'   => $this->settings->get_start_lat(),
				'start_lng'   => $this->settings->get_start_lng(),
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

		if ( has_shortcode( $post->post_content, 'mhdk_map_distance' ) ) {
			$this->enqueue_shortcode_assets();
		}
	}

	public function maybe_create_demo_page(): void {
		$page_id = (int) get_option( MapDistanceSettingsModel::DEMO_OPTION, 0 );

		if ( $page_id > 0 && get_post_status( $page_id ) ) {
			return;
		}

		$existing = get_page_by_path( MapDistanceSettingsModel::DEMO_PAGE_SLUG );

		if ( $existing instanceof \WP_Post ) {
			update_option( MapDistanceSettingsModel::DEMO_OPTION, $existing->ID, false );
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Map Distance Demo', 'mh-dev-kit' ),
				'post_name'    => MapDistanceSettingsModel::DEMO_PAGE_SLUG,
				'post_content' => '[mhdk_map_distance]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);

		if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
			update_option( MapDistanceSettingsModel::DEMO_OPTION, $page_id, false );
		}
	}

	private function enqueue_shortcode_assets(): void {
		AssetRegistrar::enqueue_style(
			'mhdk-map-distance',
			'Modules/SiteTools/Features/MapDistance',
			'css/map-distance.css'
		);

		AssetRegistrar::enqueue_script(
			'mhdk-map-distance',
			'Modules/SiteTools/Features/MapDistance',
			'js/map-distance.js',
			array(),
			true
		);

		wp_localize_script(
			'mhdk-map-distance',
			'mhdkMapDistance',
			array(
				'startLabel' => (string) $this->settings->get( 'start_label', '' ),
				'startLat'   => $this->settings->get_start_lat(),
				'startLng'   => $this->settings->get_start_lng(),
				'i18n'       => array(
					'prompt'    => __( 'Allow location access to calculate distance from the start point.', 'mh-dev-kit' ),
					'error'     => __( 'Could not read your location.', 'mh-dev-kit' ),
					'result'    => __( 'Distance from %1$s: %2$s km (%3$s m)', 'mh-dev-kit' ),
					'waiting'   => __( 'Detecting your location…', 'mh-dev-kit' ),
					'unsupported' => __( 'Geolocation is not supported in this browser.', 'mh-dev-kit' ),
				),
			)
		);
	}
}
