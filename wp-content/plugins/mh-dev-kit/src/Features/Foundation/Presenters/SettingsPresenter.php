<?php
/**
 * MH Dev Kit settings admin page.
 *
 * @package MH\DevKit\Features\Foundation\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\Foundation\Presenters;

use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Features\Foundation\Models\FoundationSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class SettingsPresenter {

	public function __construct(
		private FoundationSettingsModel $settings
	) {}

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'MH Dev Kit', 'mh-dev-kit' ),
			__( 'MH Dev Kit', 'mh-dev-kit' ),
			'manage_options',
			FoundationSettingsModel::MENU_SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-admin-tools',
			57
		);

		add_submenu_page(
			FoundationSettingsModel::MENU_SLUG,
			__( 'Foundation Settings', 'mh-dev-kit' ),
			__( 'Foundation', 'mh-dev-kit' ),
			'manage_options',
			FoundationSettingsModel::SETTINGS_SLUG,
			array( $this, 'render_settings' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_foundation',
			FoundationSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_foundation_main',
			__( 'Foundation Settings', 'mh-dev-kit' ),
			static function (): void {
				echo '<p>' . esc_html__( 'Defaults for MH Dev Kit blocks and content types.', 'mh-dev-kit' ) . '</p>';
			},
			FoundationSettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'default_callout_style',
			__( 'Default callout style', 'mh-dev-kit' ),
			array( $this, 'field_callout_style' ),
			FoundationSettingsModel::SETTINGS_SLUG,
			'mhdk_foundation_main'
		);

		add_settings_field(
			'web_links_new_tab',
			__( 'Web links open in new tab', 'mh-dev-kit' ),
			array( $this, 'field_web_links_new_tab' ),
			FoundationSettingsModel::SETTINGS_SLUG,
			'mhdk_foundation_main'
		);

		add_settings_field(
			'menu_currency_symbol',
			__( 'Hospitality menu currency symbol', 'mh-dev-kit' ),
			array( $this, 'field_currency' ),
			FoundationSettingsModel::SETTINGS_SLUG,
			'mhdk_foundation_main'
		);

		add_settings_field(
			'enable_frontend_css',
			__( 'Enable frontend CSS', 'mh-dev-kit' ),
			array( $this, 'field_frontend_css' ),
			FoundationSettingsModel::SETTINGS_SLUG,
			'mhdk_foundation_main'
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, FoundationSettingsModel::MENU_SLUG ) ) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-foundation-admin',
			'Foundation',
			'css/foundation-admin.css'
		);
	}

	public function render_dashboard(): void {
		ViewRenderer::display(
			'Foundation/Views/dashboard-page.php',
			array(
				'version' => MH_DEV_KIT_VERSION,
			)
		);
	}

	public function render_settings(): void {
		ViewRenderer::display(
			'Foundation/Views/settings-page.php',
			array(
				'option_key' => FoundationSettingsModel::OPTION_KEY,
				'settings'   => $this->settings->get_all(),
				'page_slug'  => FoundationSettingsModel::SETTINGS_SLUG,
				'version'    => MH_DEV_KIT_VERSION,
			)
		);
	}

	public function field_callout_style(): void {
		$value   = (string) $this->settings->get( 'default_callout_style', 'info' );
		$options = array(
			'info'    => __( 'Info', 'mh-dev-kit' ),
			'warning' => __( 'Warning', 'mh-dev-kit' ),
			'success' => __( 'Success', 'mh-dev-kit' ),
		);

		echo '<select name="' . esc_attr( FoundationSettingsModel::OPTION_KEY ) . '[default_callout_style]">';
		foreach ( $options as $key => $label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $key ),
				selected( $value, $key, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	public function field_web_links_new_tab(): void {
		$checked = (bool) $this->settings->get( 'web_links_new_tab', true );
		printf(
			'<label><input type="checkbox" name="%1$s[web_links_new_tab]" value="1" %2$s /> %3$s</label>',
			esc_attr( FoundationSettingsModel::OPTION_KEY ),
			checked( $checked, true, false ),
			esc_html__( 'Open web links in a new browser tab by default', 'mh-dev-kit' )
		);
	}

	public function field_currency(): void {
		$value = (string) $this->settings->get( 'menu_currency_symbol', '$' );
		printf(
			'<input type="text" class="regular-text" name="%1$s[menu_currency_symbol]" value="%2$s" />',
			esc_attr( FoundationSettingsModel::OPTION_KEY ),
			esc_attr( $value )
		);
	}

	public function field_frontend_css(): void {
		$checked = (bool) $this->settings->get( 'enable_frontend_css', true );
		printf(
			'<label><input type="checkbox" name="%1$s[enable_frontend_css]" value="1" %2$s /> %3$s</label>',
			esc_attr( FoundationSettingsModel::OPTION_KEY ),
			checked( $checked, true, false ),
			esc_html__( 'Load Foundation frontend styles on public pages', 'mh-dev-kit' )
		);
	}
}
