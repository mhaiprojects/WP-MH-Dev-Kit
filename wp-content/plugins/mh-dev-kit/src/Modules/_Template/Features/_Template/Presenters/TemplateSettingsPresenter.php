<?php
/**
 * Example settings presenter — register Settings API fields and render the shared partial.
 *
 * Wire from YourModule::register_admin():
 *
 *   $presenter = new TemplateSettingsPresenter( new TemplateModel() );
 *   $presenter->register();
 *   $registrar->add_submenu( 'your-slug', ..., array( $presenter, 'render_page' ) );
 *
 * @package MH\DevKit\Modules\_Template\Features\_Template\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\_Template\Features\_Template\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\_Template\Features\_Template\Models\TemplateModel;

final class TemplateSettingsPresenter {

	public const PAGE_SLUG     = 'mhdk-settings-template';

	public const OPTION_GROUP  = 'mhdk_template';

	public function __construct(
		private TemplateModel $model
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			TemplateModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->model, 'sanitize' ),
				'default'           => $this->model->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_template_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Example Settings API section intro.', 'mh-dev-kit' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		add_settings_field(
			'example_text',
			__( 'Example text', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_example_text' ),
			self::PAGE_SLUG,
			'mhdk_template_main'
		);
	}

	public function render_page(): void {
		ViewRenderer::display(
			'Core/Views/admin/partials/settings-page.php',
			array(
				'page_slug'    => self::PAGE_SLUG,
				'option_group' => self::OPTION_GROUP,
			)
		);
	}

	public function field_example_text(): void {
		$key = TemplateModel::OPTION_KEY;
		SettingsFieldRenderer::text(
			$key . '[example_text]',
			(string) $this->model->get( 'example_text', '' ),
			'regular-text',
			__( 'Use SettingsFieldRenderer for core admin classes.', 'mh-dev-kit' )
		);
	}
}
