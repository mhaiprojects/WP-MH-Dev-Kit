<?php
/**
 * Local Ad Page settings (URL pattern).
 *
 * @package MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageModel;
use MH\DevKit\Modules\ContentTools\Features\LocalAdPage\Models\LocalAdPageSettingsModel;

final class LocalAdPageSettingsPresenter {

	public function __construct(
		private LocalAdPageSettingsModel $settings,
		private LocalAdPageModel $pages
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_local_ad',
			LocalAdPageSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_local_ad_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Configure public URL structure for local ad landing pages.', 'mh-dev-kit' ) . '</p>';
			},
			LocalAdPageSettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'url_pattern',
			__( 'URL pattern', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_url_pattern' ),
			LocalAdPageSettingsModel::SETTINGS_SLUG,
			'mhdk_local_ad_main'
		);
	}

	/**
	 * @param array<string, mixed> $input Raw settings.
	 * @return array<string, mixed>
	 */
	public function sanitize_settings( array $input ): array {
		$previous = $this->settings->url_pattern();
		$clean    = $this->settings->sanitize( $input );

		if ( $clean['url_pattern'] !== $previous ) {
			flush_rewrite_rules( false );
		}

		return $clean;
	}

	public function render_settings(): void {
		ViewRenderer::display(
			'Core/Views/admin/partials/settings-page.php',
			array(
				'page_slug'    => LocalAdPageSettingsModel::SETTINGS_SLUG,
				'option_group' => 'mhdk_local_ad',
				'footer_note'  => sprintf(
					/* translators: %s: example URL */
					__( 'Example: %s', 'mh-dev-kit' ),
					home_url(
						trailingslashit(
							$this->pages->build_path(
								array(
									'country'  => 'au',
									'state'    => 'vic',
									'suburb'   => 'shepparton',
									'postcode' => '3630',
								),
								$this->settings->url_pattern()
							)
						)
					)
				),
			)
		);
	}

	public function field_url_pattern(): void {
		$key     = LocalAdPageSettingsModel::OPTION_KEY;
		$value   = $this->settings->url_pattern();
		$options = array(
			LocalAdPageSettingsModel::URL_PATTERN_DASH  => __( 'Dash-separated (default): local-au-vic-shepparton-3630', 'mh-dev-kit' ),
			LocalAdPageSettingsModel::URL_PATTERN_SLASH => __( 'Slash-separated: local/au/vic/shepparton-3630', 'mh-dev-kit' ),
		);

		SettingsFieldRenderer::select( $key . '[url_pattern]', $options, $value );
		echo '<p class="description">' . esc_html__( 'Change the pattern, save settings, then visit Settings → Permalinks → Save to refresh rewrite rules if URLs 404.', 'mh-dev-kit' ) . '</p>';
	}
}
