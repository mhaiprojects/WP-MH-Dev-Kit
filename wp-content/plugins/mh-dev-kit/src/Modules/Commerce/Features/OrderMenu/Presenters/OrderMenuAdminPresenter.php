<?php
/**
 * Order Menu admin settings, product meta, shortcode generator (#14–#16, #19).
 *
 * @package MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\OrderMenuSettingsModel;
use MH\DevKit\Modules\Commerce\Features\OrderMenu\Models\SavedMenuModel;
use MH\DevKit\Shared\AssetRegistrar;

final class OrderMenuAdminPresenter {

	/** @var array<int, array<string, string>> */
	private array $cat_options = array();

	/** @var array<int, array<string, mixed>> */
	private array $saved_menus = array();

	public function __construct(
		private OrderMenuSettingsModel $settings = new OrderMenuSettingsModel(),
		private SavedMenuModel $saved_menu_model = new SavedMenuModel()
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_fields' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_order_menu',
			OrderMenuSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_order_menu_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Defaults for the bulk order menu shortcode and block.', 'mh-dev-kit' ) . '</p>';
			},
			OrderMenuSettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'enable_ajax',
			__( 'Enable AJAX add to cart', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_enable_ajax' ),
			OrderMenuSettingsModel::SETTINGS_SLUG,
			'mhdk_order_menu_main'
		);

		add_settings_field(
			'default_category',
			__( 'Default category', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_default_category' ),
			OrderMenuSettingsModel::SETTINGS_SLUG,
			'mhdk_order_menu_main'
		);

		add_settings_field(
			'max_qty_per_line',
			__( 'Max quantity per line', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_max_qty' ),
			OrderMenuSettingsModel::SETTINGS_SLUG,
			'mhdk_order_menu_main'
		);

		add_settings_field(
			'columns_behaviour',
			__( 'Columns & behaviour', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_columns_behaviour' ),
			OrderMenuSettingsModel::SETTINGS_SLUG,
			'mhdk_order_menu_main'
		);

		add_settings_section(
			'mhdk_order_menu_shortcode',
			__( 'Shortcode generator', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'section_shortcode_generator' ),
			OrderMenuSettingsModel::SETTINGS_SLUG
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, OrderMenuSettingsModel::SETTINGS_SLUG ) ) {
			return;
		}

		AssetRegistrar::enqueue_script(
			'mhdk-order-menu-admin',
			'Modules/Commerce/Features/OrderMenu',
			'js/order-menu-admin.js'
		);
	}

	public function render_settings_page(): void {
		$this->prepare_generator_data();

		ViewRenderer::display(
			'Modules/Commerce/Features/OrderMenu/Views/admin/settings-page.php',
			array(
				'page_slug'    => OrderMenuSettingsModel::SETTINGS_SLUG,
				'option_group' => 'mhdk_order_menu',
				'footer_note'  => sprintf(
					/* translators: %s: plugin version */
					__( 'MH Dev Kit %s', 'mh-dev-kit' ),
					MH_DEV_KIT_VERSION
				),
			)
		);
	}

	private function prepare_generator_data(): void {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		$this->cat_options = array();

		if ( is_array( $categories ) ) {
			foreach ( $categories as $term ) {
				if ( $term instanceof \WP_Term ) {
					$this->cat_options[] = array(
						'slug' => $term->slug,
						'name' => $term->name,
					);
				}
			}
		}

		$this->saved_menus = $this->saved_menu_model->get_all_menus();
	}

	public function field_enable_ajax(): void {
		$key = OrderMenuSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::checkbox(
			$key . '[enable_ajax]',
			(bool) $this->settings->get( 'enable_ajax', true ),
			__( 'Update cart without full page reload', AdminMenu::TEXT_DOMAIN )
		);
	}

	public function field_default_category(): void {
		$key = OrderMenuSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::text(
			$key . '[default_category]',
			(string) $this->settings->get( 'default_category', '' ),
			'regular-text',
			__( 'Product category slug used when the shortcode does not specify a source.', 'mh-dev-kit' )
		);
	}

	public function field_max_qty(): void {
		$key = OrderMenuSettingsModel::OPTION_KEY;
		SettingsFieldRenderer::number(
			$key . '[max_qty_per_line]',
			(int) $this->settings->get( 'max_qty_per_line', 99 ),
			1,
			999,
			__( 'Maximum quantity allowed per product line in the order table.', 'mh-dev-kit' )
		);
	}

	public function field_columns_behaviour(): void {
		$key = OrderMenuSettingsModel::OPTION_KEY;

		SettingsFieldRenderer::checkbox(
			$key . '[show_unit_column]',
			(bool) $this->settings->get( 'show_unit_column', true ),
			__( 'Show unit column', AdminMenu::TEXT_DOMAIN )
		);
		echo '<br />';
		SettingsFieldRenderer::checkbox(
			$key . '[show_stock_badge]',
			(bool) $this->settings->get( 'show_stock_badge', true ),
			__( 'Show stock badges', AdminMenu::TEXT_DOMAIN )
		);
		echo '<br />';
		SettingsFieldRenderer::checkbox(
			$key . '[show_image]',
			(bool) $this->settings->get( 'show_image', false ),
			__( 'Show product thumbnails by default', AdminMenu::TEXT_DOMAIN )
		);
		echo '<br />';
		SettingsFieldRenderer::checkbox(
			$key . '[preserve_qty_after_add]',
			(bool) $this->settings->get( 'preserve_qty_after_add', true ),
			__( 'Preserve quantity inputs after add', AdminMenu::TEXT_DOMAIN )
		);
		echo '<br />';
		SettingsFieldRenderer::checkbox(
			$key . '[enable_reorder_button]',
			(bool) $this->settings->get( 'enable_reorder_button', true ),
			__( 'Show reorder last order button', AdminMenu::TEXT_DOMAIN )
		);
	}

	public function section_shortcode_generator(): void {
		echo '<p class="description">' . esc_html__( 'Build a shortcode snippet for any page or the Order Menu block.', 'mh-dev-kit' ) . '</p>';
		$this->render_shortcode_generator_table();
	}

	private function render_shortcode_generator_table(): void {
		$default_category = (string) $this->settings->get( 'default_category', '' );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="mhdk-gen-category"><?php esc_html_e( 'Category slug', 'mh-dev-kit' ); ?></label></th>
				<td>
					<select id="mhdk-gen-category">
						<option value=""><?php esc_html_e( '— None —', 'mh-dev-kit' ); ?></option>
						<?php foreach ( $this->cat_options as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat['slug'] ); ?>"><?php echo esc_html( $cat['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mhdk-gen-menu"><?php esc_html_e( 'Saved menu', 'mh-dev-kit' ); ?></label></th>
				<td>
					<select id="mhdk-gen-menu">
						<option value=""><?php esc_html_e( '— None —', 'mh-dev-kit' ); ?></option>
						<?php foreach ( $this->saved_menus as $menu ) : ?>
							<option value="<?php echo esc_attr( (string) $menu['slug'] ); ?>"><?php echo esc_html( (string) $menu['title'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="mhdk-gen-ids"><?php esc_html_e( 'Product IDs', 'mh-dev-kit' ); ?></label></th>
				<td><input type="text" class="regular-text" id="mhdk-gen-ids" placeholder="19,20,21" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="mhdk-gen-orderby"><?php esc_html_e( 'Order by', 'mh-dev-kit' ); ?></label></th>
				<td>
					<select id="mhdk-gen-orderby">
						<option value="title"><?php esc_html_e( 'Name', 'mh-dev-kit' ); ?></option>
						<option value="price"><?php esc_html_e( 'Price', 'mh-dev-kit' ); ?></option>
					</select>
					<select id="mhdk-gen-order">
						<option value="ASC">ASC</option>
						<option value="DESC">DESC</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Generated shortcode', 'mh-dev-kit' ); ?></th>
				<td>
					<input
						type="text"
						class="large-text code"
						id="mhdk-gen-output"
						readonly
						value="<?php echo esc_attr( '[mhdk_order_menu category="' . $default_category . '"]' ); ?>"
					/>
				</td>
			</tr>
		</table>
		<?php
	}

	public function product_fields(): void {
		global $post;

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		echo '<div class="options_group mhdk-order-menu-product-fields">';

		woocommerce_wp_textarea_input(
			array(
				'id'          => OrderMenuSettingsModel::META_MENU_DESCRIPTION,
				'label'       => __( 'Order menu description', 'mh-dev-kit' ),
				'description' => __( 'Short blurb shown in the bulk order menu table.', 'mh-dev-kit' ),
				'value'       => get_post_meta( $post->ID, OrderMenuSettingsModel::META_MENU_DESCRIPTION, true ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => OrderMenuSettingsModel::META_AVAILABLE_FROM,
				'label'       => __( 'Available from', 'mh-dev-kit' ),
				'description' => __( 'YYYY-MM-DD. Leave empty for no start limit.', 'mh-dev-kit' ),
				'type'        => 'date',
				'value'       => get_post_meta( $post->ID, OrderMenuSettingsModel::META_AVAILABLE_FROM, true ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => OrderMenuSettingsModel::META_AVAILABLE_UNTIL,
				'label'       => __( 'Available until', 'mh-dev-kit' ),
				'description' => __( 'YYYY-MM-DD. Leave empty for no end limit.', 'mh-dev-kit' ),
				'type'        => 'date',
				'value'       => get_post_meta( $post->ID, OrderMenuSettingsModel::META_AVAILABLE_UNTIL, true ),
			)
		);

		echo '</div>';
	}

	public function save_product_fields( int $post_id ): void {
		if ( isset( $_POST[ OrderMenuSettingsModel::META_MENU_DESCRIPTION ] ) ) {
			update_post_meta(
				$post_id,
				OrderMenuSettingsModel::META_MENU_DESCRIPTION,
				sanitize_textarea_field( wp_unslash( (string) $_POST[ OrderMenuSettingsModel::META_MENU_DESCRIPTION ] ) )
			);
		}

		foreach ( array( OrderMenuSettingsModel::META_AVAILABLE_FROM, OrderMenuSettingsModel::META_AVAILABLE_UNTIL ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$date = sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) );
				update_post_meta( $post_id, $key, preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '' );
			}
		}
	}
}
