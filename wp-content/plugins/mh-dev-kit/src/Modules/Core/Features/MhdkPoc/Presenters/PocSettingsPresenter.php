<?php
/**
 * MHDK-POC settings (Settings API only; menu registered via Core module).
 *
 * @package MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Core\Features\MhdkPoc\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Core\Features\MhdkPoc\Models\MhdkPocSettingsModel;

final class PocSettingsPresenter {

	public function __construct(
		private MhdkPocSettingsModel $settings
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
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
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Defaults for MHDK-POC blocks and content types.', 'mh-dev-kit' ) . '</p>';
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

	public function render_settings(): void {
		ViewRenderer::display(
			'Modules/Core/Features/MhdkPoc/Views/settings-page.php',
			array(
				'page_slug'    => MhdkPocSettingsModel::SETTINGS_SLUG,
				'option_group' => 'mhdk_poc',
				'footer_note'  => sprintf(
					/* translators: %s: plugin version */
					__( 'MH Dev Kit %s', 'mh-dev-kit' ),
					MH_DEV_KIT_VERSION
				),
			)
		);
	}

	public function field_callout_style(): void {
		$key     = MhdkPocSettingsModel::OPTION_KEY;
		$value   = (string) $this->settings->get( 'default_callout_style', 'info' );
		$options = array(
			'info'    => __( 'Info', AdminMenu::TEXT_DOMAIN ),
			'warning' => __( 'Warning', AdminMenu::TEXT_DOMAIN ),
			'success' => __( 'Success', AdminMenu::TEXT_DOMAIN ),
		);

		SettingsFieldRenderer::select( $key . '[default_callout_style]', $options, $value );
		echo '<p class="description">' . esc_html__( 'Used when a callout block does not specify a style.', 'mh-dev-kit' ) . '</p>';
	}

	public function field_web_links_new_tab(): void {
		$key = MhdkPocSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[web_links_new_tab]',
			(bool) $this->settings->get( 'web_links_new_tab', true ),
			__( 'Open web links in a new browser tab by default', AdminMenu::TEXT_DOMAIN )
		);
	}

	public function field_currency(): void {
		$key = MhdkPocSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::text(
			$key . '[menu_currency_symbol]',
			(string) $this->settings->get( 'menu_currency_symbol', '$' ),
			'small-text',
			__( 'Shown before prices in hospitality menu blocks.', 'mh-dev-kit' )
		);
	}

	public function field_frontend_css(): void {
		$key = MhdkPocSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[enable_frontend_css]',
			(bool) $this->settings->get( 'enable_frontend_css', true ),
			__( 'Load MHDK-POC frontend styles on public pages', AdminMenu::TEXT_DOMAIN )
		);
	}
}
