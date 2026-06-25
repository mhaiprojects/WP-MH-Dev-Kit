<?php
/**
 * MHDK-POC settings (Settings API only; menu registered via Core).
 *
 * @package MH\DevKit\Features\MhdkPoc\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\MhdkPoc\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Features\MhdkPoc\Models\MhdkPocSettingsModel;
use MH\DevKit\Shared\AssetRegistrar;

final class PocSettingsPresenter {

	public function __construct(
		private MhdkPocSettingsModel $settings
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_poc',
			MhdkPocSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_poc_main',
			__( 'MHDK-POC Settings', AdminMenu::TEXT_DOMAIN ),
			static function (): void {
				echo '<p>' . esc_html__( 'Defaults for MHDK-POC blocks and content types.', AdminMenu::TEXT_DOMAIN ) . '</p>';
			},
			MhdkPocSettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'default_callout_style',
			__( 'Default callout style', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_callout_style' ),
			MhdkPocSettingsModel::SETTINGS_SLUG,
			'mhdk_poc_main'
		);

		add_settings_field(
			'web_links_new_tab',
			__( 'Web links open in new tab', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_web_links_new_tab' ),
			MhdkPocSettingsModel::SETTINGS_SLUG,
			'mhdk_poc_main'
		);

		add_settings_field(
			'menu_currency_symbol',
			__( 'Hospitality menu currency symbol', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_currency' ),
			MhdkPocSettingsModel::SETTINGS_SLUG,
			'mhdk_poc_main'
		);

		add_settings_field(
			'enable_frontend_css',
			__( 'Enable frontend CSS', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_frontend_css' ),
			MhdkPocSettingsModel::SETTINGS_SLUG,
			'mhdk_poc_main'
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, MhdkPocSettingsModel::SETTINGS_SLUG ) ) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-poc-admin',
			'MhdkPoc',
			'css/foundation-admin.css'
		);
	}

	public function render_settings(): void {
		ViewRenderer::display(
			'MhdkPoc/Views/settings-page.php',
			array(
				'option_key' => MhdkPocSettingsModel::OPTION_KEY,
				'settings'   => $this->settings->get_all(),
				'page_slug'  => MhdkPocSettingsModel::SETTINGS_SLUG,
				'version'    => MH_DEV_KIT_VERSION,
			)
		);
	}

	public function field_callout_style(): void {
		$value   = (string) $this->settings->get( 'default_callout_style', 'info' );
		$options = array(
			'info'    => __( 'Info', AdminMenu::TEXT_DOMAIN ),
			'warning' => __( 'Warning', AdminMenu::TEXT_DOMAIN ),
			'success' => __( 'Success', AdminMenu::TEXT_DOMAIN ),
		);

		echo '<select name="' . esc_attr( MhdkPocSettingsModel::OPTION_KEY ) . '[default_callout_style]">';
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
			esc_attr( MhdkPocSettingsModel::OPTION_KEY ),
			checked( $checked, true, false ),
			esc_html__( 'Open web links in a new browser tab by default', AdminMenu::TEXT_DOMAIN )
		);
	}

	public function field_currency(): void {
		$value = (string) $this->settings->get( 'menu_currency_symbol', '$' );
		printf(
			'<input type="text" class="regular-text" name="%1$s[menu_currency_symbol]" value="%2$s" />',
			esc_attr( MhdkPocSettingsModel::OPTION_KEY ),
			esc_attr( $value )
		);
	}

	public function field_frontend_css(): void {
		$checked = (bool) $this->settings->get( 'enable_frontend_css', true );
		printf(
			'<label><input type="checkbox" name="%1$s[enable_frontend_css]" value="1" %2$s /> %3$s</label>',
			esc_attr( MhdkPocSettingsModel::OPTION_KEY ),
			checked( $checked, true, false ),
			esc_html__( 'Load MHDK-POC frontend styles on public pages', AdminMenu::TEXT_DOMAIN )
		);
	}
}
