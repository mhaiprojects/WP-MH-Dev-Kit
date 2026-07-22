<?php
/**
 * Seed default stores and demo page.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters;

use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;

final class StoreLocatorSeedPresenter {

	public function __construct(
		private StoreLocatorModel $stores
	) {}

	public function register(): void {
		add_action( 'init', array( $this, 'seed_stores' ), 5 );
		add_action( 'init', array( $this, 'maybe_create_demo_page' ), 20 );
	}

	public function seed_stores(): void {
		$this->stores->seed_defaults_if_empty();
	}

	public function maybe_create_demo_page(): void {
		$page_id = (int) get_option( StoreLocatorSettingsModel::DEMO_OPTION, 0 );

		if ( $page_id > 0 && get_post_status( $page_id ) ) {
			return;
		}

		$existing = get_page_by_path( StoreLocatorSettingsModel::DEMO_PAGE_SLUG );

		if ( $existing instanceof \WP_Post ) {
			update_option( StoreLocatorSettingsModel::DEMO_OPTION, $existing->ID, false );
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Store Locator', 'mh-dev-kit' ),
				'post_name'    => StoreLocatorSettingsModel::DEMO_PAGE_SLUG,
				'post_content' => '[mhdk_store_locator]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);

		if ( ! is_wp_error( $page_id ) && $page_id > 0 ) {
			update_option( StoreLocatorSettingsModel::DEMO_OPTION, $page_id, false );
		}
	}
}
