<?php
/**
 * Store Locator settings page.
 *
 * @package MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\StoreLocator\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorModel;
use MH\DevKit\Modules\Commerce\Features\StoreLocator\Models\StoreLocatorSettingsModel;

final class StoreLocatorSettingsPresenter {

	public function __construct(
		private StoreLocatorSettingsModel $settings,
		private StoreLocatorModel $stores
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_store_locator',
			StoreLocatorSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_store_locator_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Configure store locator behaviour and checkout integration.', 'mh-dev-kit' ) . '</p>';
			},
			StoreLocatorSettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'max_distance_km',
			__( 'Max search radius (km)', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_max_distance' ),
			StoreLocatorSettingsModel::SETTINGS_SLUG,
			'mhdk_store_locator_main'
		);

		add_settings_field(
			'enable_checkout_field',
			__( 'Checkout pickup store field', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_checkout' ),
			StoreLocatorSettingsModel::SETTINGS_SLUG,
			'mhdk_store_locator_main'
		);

		add_settings_field(
			'show_phone',
			__( 'Show phone on locator', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_show_phone' ),
			StoreLocatorSettingsModel::SETTINGS_SLUG,
			'mhdk_store_locator_main'
		);

		add_settings_field(
			'show_hours',
			__( 'Show hours on locator', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_show_hours' ),
			StoreLocatorSettingsModel::SETTINGS_SLUG,
			'mhdk_store_locator_main'
		);
	}

	public function render_settings(): void {
		ViewRenderer::display(
			'Modules/Commerce/Features/StoreLocator/Views/settings-page.php',
			array(
				'settings' => $this->settings,
				'stores'   => $this->stores->get_all(),
			)
		);
	}

	public function field_max_distance(): void {
		$key = StoreLocatorSettingsModel::OPTION_KEY;
		printf(
			'<input type="number" min="1" class="small-text" name="%1$s" value="%2$d" />',
			esc_attr( $key . '[max_distance_km]' ),
			(int) $this->settings->get( 'max_distance_km', 500 )
		);
		echo '<p class="description">' . esc_html__( 'Stores beyond this radius are hidden from nearby results.', 'mh-dev-kit' ) . '</p>';
	}

	public function field_checkout(): void {
		SettingsFieldRenderer::checkbox(
			StoreLocatorSettingsModel::OPTION_KEY . '[enable_checkout_field]',
			$this->settings->checkout_enabled(),
			__( 'Show pickup store selector on WooCommerce checkout with distance from customer location.', 'mh-dev-kit' )
		);
	}

	public function field_show_phone(): void {
		SettingsFieldRenderer::checkbox(
			StoreLocatorSettingsModel::OPTION_KEY . '[show_phone]',
			(bool) $this->settings->get( 'show_phone', true ),
			__( 'Display store phone numbers on the locator shortcode.', 'mh-dev-kit' )
		);
	}

	public function field_show_hours(): void {
		SettingsFieldRenderer::checkbox(
			StoreLocatorSettingsModel::OPTION_KEY . '[show_hours]',
			(bool) $this->settings->get( 'show_hours', true ),
			__( 'Display store opening hours on the locator shortcode.', 'mh-dev-kit' )
		);
	}
}
