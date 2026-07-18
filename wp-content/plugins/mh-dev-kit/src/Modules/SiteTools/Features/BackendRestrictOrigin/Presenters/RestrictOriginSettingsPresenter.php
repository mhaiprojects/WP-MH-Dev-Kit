<?php
/**
 * Backend Restrict Origin admin settings.
 *
 * @package MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\SiteTools\Features\BackendRestrictOrigin\Models\RestrictOriginSettingsModel;

final class RestrictOriginSettingsPresenter {

	public function __construct(
		private RestrictOriginSettingsModel $settings
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_restrict_origin',
			RestrictOriginSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_restrict_origin_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Block admin requests that do not originate from this site (Referer / Origin check). Helps reduce open-redirect and some XSS vectors when combined with other hardening.', 'mh-dev-kit' ) . '</p>';
			},
			RestrictOriginSettingsModel::SETTINGS_SLUG
		);

		$fields = array(
			array( 'enabled', __( 'Enable restrict origin', AdminMenu::TEXT_DOMAIN ), 'field_enabled' ),
			array( 'allow_same_host', __( 'Allow same site host', AdminMenu::TEXT_DOMAIN ), 'field_allow_same_host' ),
			array( 'allowed_hosts', __( 'Additional allowed hosts', AdminMenu::TEXT_DOMAIN ), 'field_allowed_hosts' ),
			array( 'bypass_ajax', __( 'Bypass for admin-ajax.php', AdminMenu::TEXT_DOMAIN ), 'field_bypass_ajax' ),
			array( 'bypass_cron', __( 'Bypass for wp-cron.php', AdminMenu::TEXT_DOMAIN ), 'field_bypass_cron' ),
			array( 'bypass_rest', __( 'Bypass for REST API', AdminMenu::TEXT_DOMAIN ), 'field_bypass_rest' ),
		);

		foreach ( $fields as $field ) {
			add_settings_field(
				$field[0],
				$field[1],
				array( $this, $field[2] ),
				RestrictOriginSettingsModel::SETTINGS_SLUG,
				'mhdk_restrict_origin_main'
			);
		}
	}

	public function render_settings(): void {
		ViewRenderer::display(
			'Core/Views/admin/partials/settings-page.php',
			array(
				'page_slug'    => RestrictOriginSettingsModel::SETTINGS_SLUG,
				'option_group' => 'mhdk_restrict_origin',
				'footer_note'  => __( 'Edge cases: bookmarked admin URLs, external auth flows, and some browser privacy modes may omit Referer. Test before enabling in production.', 'mh-dev-kit' ),
			)
		);
	}

	public function field_enabled(): void {
		$key = RestrictOriginSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[enabled]',
			(bool) $this->settings->get( 'enabled', false ),
			__( 'Reject admin requests without a matching Referer or Origin', 'mh-dev-kit' )
		);
	}

	public function field_allow_same_host(): void {
		$key = RestrictOriginSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[allow_same_host]',
			(bool) $this->settings->get( 'allow_same_host', true ),
			__( 'Allow requests whose Referer/Origin host matches this site', 'mh-dev-kit' )
		);
	}

	public function field_allowed_hosts(): void {
		$key = RestrictOriginSettingsModel::OPTION_KEY;
		printf(
			'<textarea class="large-text" rows="4" name="%1$s">%2$s</textarea>',
			esc_attr( $key . '[allowed_hosts]' ),
			esc_textarea( (string) $this->settings->get( 'allowed_hosts', '' ) )
		);
		echo '<p class="description">' . esc_html__( 'One host per line (no scheme). Example: staging.example.com', 'mh-dev-kit' ) . '</p>';
	}

	public function field_bypass_ajax(): void {
		$key = RestrictOriginSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[bypass_ajax]',
			(bool) $this->settings->get( 'bypass_ajax', true ),
			__( 'Do not check admin-ajax.php requests', 'mh-dev-kit' )
		);
	}

	public function field_bypass_cron(): void {
		$key = RestrictOriginSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[bypass_cron]',
			(bool) $this->settings->get( 'bypass_cron', true ),
			__( 'Do not check wp-cron.php requests', 'mh-dev-kit' )
		);
	}

	public function field_bypass_rest(): void {
		$key = RestrictOriginSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[bypass_rest]',
			(bool) $this->settings->get( 'bypass_rest', false ),
			__( 'Do not check REST API requests in admin context', 'mh-dev-kit' )
		);
	}
}
