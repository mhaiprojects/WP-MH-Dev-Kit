<?php
/**
 * Map distance settings page.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\MapDistance\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\MapDistance\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\SiteTools\Features\MapDistance\Models\MapDistanceSettingsModel;

final class MapDistanceSettingsPresenter {

	public function __construct(
		private MapDistanceSettingsModel $settings
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_map_distance',
			MapDistanceSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_map_distance_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Configure the fixed starting point for the distance calculator shortcode.', 'mh-dev-kit' ) . '</p>';
			},
			MapDistanceSettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'start_label',
			__( 'Start label', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_start_label' ),
			MapDistanceSettingsModel::SETTINGS_SLUG,
			'mhdk_map_distance_main'
		);

		add_settings_field(
			'start_lat',
			__( 'Start latitude', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_start_lat' ),
			MapDistanceSettingsModel::SETTINGS_SLUG,
			'mhdk_map_distance_main'
		);

		add_settings_field(
			'start_lng',
			__( 'Start longitude', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_start_lng' ),
			MapDistanceSettingsModel::SETTINGS_SLUG,
			'mhdk_map_distance_main'
		);
	}

	public function render_settings(): void {
		ViewRenderer::display(
			'Core/Views/admin/partials/settings-page.php',
			array(
				'page_slug'    => MapDistanceSettingsModel::SETTINGS_SLUG,
				'option_group' => 'mhdk_map_distance',
				'footer_note'  => sprintf(
					/* translators: %s: shortcode tag */
					__( 'Use shortcode %s on any page. Demo page slug: /map-distance-demo/', 'mh-dev-kit' ),
					'[mhdk_map_distance]'
				),
			)
		);
	}

	public function field_start_label(): void {
		$key = MapDistanceSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::text(
			$key . '[start_label]',
			(string) $this->settings->get( 'start_label', '' ),
			'regular-text',
			__( 'Displayed name for the starting point.', 'mh-dev-kit' )
		);
	}

	public function field_start_lat(): void {
		$key = MapDistanceSettingsModel::OPTION_KEY;
		printf(
			'<input type="number" step="any" class="regular-text" name="%1$s" value="%2$s" />',
			esc_attr( $key . '[start_lat]' ),
			esc_attr( (string) $this->settings->get_start_lat() )
		);
	}

	public function field_start_lng(): void {
		$key = MapDistanceSettingsModel::OPTION_KEY;
		printf(
			'<input type="number" step="any" class="regular-text" name="%1$s" value="%2$s" />',
			esc_attr( $key . '[start_lng]' ),
			esc_attr( (string) $this->settings->get_start_lng() )
		);
	}
}
