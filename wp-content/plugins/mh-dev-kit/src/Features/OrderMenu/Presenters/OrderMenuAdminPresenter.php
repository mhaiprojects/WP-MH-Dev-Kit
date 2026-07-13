<?php
/**
 * Order Menu admin settings, product meta, shortcode generator (#14–#16, #19).
 *
 * @package MH\DevKit\Features\OrderMenu\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\AdminMenuRegistrar;
use MH\DevKit\Core\Admin\FeatureAdminContributorInterface;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Features\OrderMenu\Models\OrderMenuSettingsModel;
use MH\DevKit\Features\OrderMenu\Models\SavedMenuModel;
use MH\DevKit\Shared\AssetRegistrar;

final class OrderMenuAdminPresenter implements FeatureAdminContributorInterface {

	public function __construct(
		private OrderMenuSettingsModel $settings = new OrderMenuSettingsModel(),
		private SavedMenuModel $saved_menus = new SavedMenuModel()
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_fields' ) );
	}

	public function register_admin( AdminMenuRegistrar $registrar ): void {
		$settings = $this->settings;

		$registrar->add_settings_submenu(
			OrderMenuSettingsModel::SETTINGS_SLUG,
			__( 'Order Menu', AdminMenu::TEXT_DOMAIN ),
			__( 'Order Menu Settings', AdminMenu::TEXT_DOMAIN ),
			static function () use ( $settings ): void {
				( new self( $settings ) )->render_settings_page();
			}
		);
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
	}

	public function enqueue_assets( string $hook ): void {
		if ( ! str_contains( $hook, OrderMenuSettingsModel::SETTINGS_SLUG ) ) {
			return;
		}

		AssetRegistrar::enqueue_style(
			'mhdk-order-menu-admin',
			'OrderMenu',
			'css/order-menu-admin.css'
		);
	}

	public function render_settings_page(): void {
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		$cat_options = array();

		if ( is_array( $categories ) ) {
			foreach ( $categories as $term ) {
				if ( $term instanceof \WP_Term ) {
					$cat_options[] = array(
						'slug' => $term->slug,
						'name' => $term->name,
					);
				}
			}
		}

		ViewRenderer::display(
			'OrderMenu/Views/admin/settings-page.php',
			array(
				'option_key'   => OrderMenuSettingsModel::OPTION_KEY,
				'settings'     => $this->settings->get_all(),
				'page_slug'    => OrderMenuSettingsModel::SETTINGS_SLUG,
				'categories'   => $cat_options,
				'saved_menus'  => $this->saved_menus->get_all_menus(),
				'version'      => MH_DEV_KIT_VERSION,
			)
		);
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
