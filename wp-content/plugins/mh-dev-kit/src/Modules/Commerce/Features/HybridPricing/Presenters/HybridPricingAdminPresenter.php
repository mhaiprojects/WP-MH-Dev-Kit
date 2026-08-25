<?php
/**
 * Hybrid pricing admin settings and price lists.
 *
 * @package MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\HybridPricing\Presenters;

use MH\DevKit\Core\Admin\AdminMenu;
use MH\DevKit\Core\Admin\SettingsFieldRenderer;
use MH\DevKit\Core\ViewRenderer;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\HybridPricingSettingsModel;
use MH\DevKit\Modules\Commerce\Features\HybridPricing\Models\PriceListModel;

final class HybridPricingAdminPresenter {

	public function __construct(
		private HybridPricingSettingsModel $settings,
		private PriceListModel $price_lists
	) {}

	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_post_mhdk_save_hybrid_pricing_lists', array( $this, 'handle_save_lists' ) );
	}

	public function register_settings(): void {
		register_setting(
			'mhdk_hybrid_pricing',
			HybridPricingSettingsModel::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this->settings, 'sanitize' ),
				'default'           => $this->settings->get_defaults(),
			)
		);

		add_settings_section(
			'mhdk_hybrid_pricing_main',
			'',
			static function (): void {
				echo '<p>' . esc_html__( 'Configure hybrid B2B/B2C catalog visibility and price display rules.', 'mh-dev-kit' ) . '</p>';
			},
			HybridPricingSettingsModel::SETTINGS_SLUG
		);

		add_settings_field(
			'hide_prices_guest',
			__( 'Hide prices from guests', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_hide_guest' ),
			HybridPricingSettingsModel::SETTINGS_SLUG,
			'mhdk_hybrid_pricing_main'
		);

		add_settings_field(
			'hide_prices_b2c',
			__( 'Hide prices from B2C customers', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_hide_b2c' ),
			HybridPricingSettingsModel::SETTINGS_SLUG,
			'mhdk_hybrid_pricing_main'
		);

		add_settings_field(
			'hide_b2b_only_from_b2c',
			__( 'Hide B2B-only products from B2C', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_hide_b2b_products' ),
			HybridPricingSettingsModel::SETTINGS_SLUG,
			'mhdk_hybrid_pricing_main'
		);

		add_settings_field(
			'login_for_prices_message',
			__( 'Hidden price message', AdminMenu::TEXT_DOMAIN ),
			array( $this, 'field_login_message' ),
			HybridPricingSettingsModel::SETTINGS_SLUG,
			'mhdk_hybrid_pricing_main'
		);
	}

	public function render_settings_page(): void {
		$this->price_lists->ensure_defaults();

		ViewRenderer::display(
			'Modules/Commerce/Features/HybridPricing/Views/settings-page.php',
			array(
				'settings'    => $this->settings->get_all(),
				'price_lists' => $this->price_lists->get_all_lists(),
				'products'    => $this->get_product_options(),
				'categories'  => $this->get_category_options(),
			)
		);
	}

	public function handle_save_lists(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'mh-dev-kit' ) );
		}

		check_admin_referer( 'mhdk_save_hybrid_pricing_lists' );

		$raw_lists = isset( $_POST['mhdk_price_lists'] ) && is_array( $_POST['mhdk_price_lists'] )
			? wp_unslash( $_POST['mhdk_price_lists'] )
			: array();

		$lists = array();
		foreach ( $raw_lists as $raw ) {
			if ( ! is_array( $raw ) ) {
				continue;
			}

			$id = sanitize_key( (string) ( $raw['id'] ?? '' ) );
			if ( '' === $id ) {
				continue;
			}

			$rules = array();
			if ( isset( $raw['rules'] ) && is_array( $raw['rules'] ) ) {
				foreach ( $raw['rules'] as $rule ) {
					if ( ! is_array( $rule ) ) {
						continue;
					}
					$type = sanitize_key( (string) ( $rule['type'] ?? '' ) );
					if ( 'product' === $type ) {
						$rules[] = array(
							'type'             => 'product',
							'product_id'       => max( 0, (int) ( $rule['product_id'] ?? 0 ) ),
							'fixed_price'      => wc_format_decimal( (string) ( $rule['fixed_price'] ?? '' ) ),
							'discount_percent' => (float) ( $rule['discount_percent'] ?? 0 ),
						);
					} elseif ( 'category' === $type ) {
						$rules[] = array(
							'type'             => 'category',
							'term_id'          => max( 0, (int) ( $rule['term_id'] ?? 0 ) ),
							'discount_percent' => (float) ( $rule['discount_percent'] ?? 0 ),
						);
					}
				}
			}

			$lists[] = array(
				'id'      => $id,
				'name'    => sanitize_text_field( (string) ( $raw['name'] ?? $id ) ),
				'enabled' => ! empty( $raw['enabled'] ),
				'rules'   => $rules,
			);
		}

		if ( array() === $lists ) {
			$lists = $this->price_lists->get_default_seed_lists();
		}

		$this->price_lists->save_all_lists( $lists );

		wp_safe_redirect(
			add_query_arg(
				array( 'page' => HybridPricingSettingsModel::SETTINGS_SLUG, 'mhdk_saved' => '1' ),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public function field_hide_guest(): void {
		SettingsFieldRenderer::checkbox(
			HybridPricingSettingsModel::OPTION_KEY . '[hide_prices_guest]',
			(bool) $this->settings->get( 'hide_prices_guest' ),
			__( 'Hide from guests', 'mh-dev-kit' ),
			__( 'Guests cannot see product prices.', 'mh-dev-kit' )
		);
	}

	public function field_hide_b2c(): void {
		SettingsFieldRenderer::checkbox(
			HybridPricingSettingsModel::OPTION_KEY . '[hide_prices_b2c]',
			(bool) $this->settings->get( 'hide_prices_b2c' ),
			__( 'Hide from B2C customers', 'mh-dev-kit' ),
			__( 'Logged-in retail customers cannot see prices until assigned B2B access.', 'mh-dev-kit' )
		);
	}

	public function field_hide_b2b_products(): void {
		SettingsFieldRenderer::checkbox(
			HybridPricingSettingsModel::OPTION_KEY . '[hide_b2b_only_from_b2c]',
			(bool) $this->settings->get( 'hide_b2b_only_from_b2c' ),
			__( 'Hide B2B-only products', 'mh-dev-kit' ),
			__( 'Products marked B2B-only are hidden from shop and search for non-B2B users.', 'mh-dev-kit' )
		);
	}

	public function field_login_message(): void {
		SettingsFieldRenderer::text(
			HybridPricingSettingsModel::OPTION_KEY . '[login_for_prices_message]',
			(string) $this->settings->get( 'login_for_prices_message', '' ),
			__( 'Shown when prices are hidden.', 'mh-dev-kit' )
		);
	}

	/**
	 * @return list<array{id: int, label: string}>
	 */
	private function get_product_options(): array {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$options = array();
		foreach ( wc_get_products( array( 'limit' => 100, 'status' => 'publish' ) ) as $product ) {
			$options[] = array(
				'id'    => (int) $product->get_id(),
				'label' => $product->get_name(),
			);
		}

		return $options;
	}

	/**
	 * @return list<array{id: int, label: string}>
	 */
	private function get_category_options(): array {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return array();
		}

		$options = array();
		foreach ( $terms as $term ) {
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}
			$options[] = array(
				'id'    => (int) $term->term_id,
				'label' => $term->name,
			);
		}

		return $options;
	}
}
