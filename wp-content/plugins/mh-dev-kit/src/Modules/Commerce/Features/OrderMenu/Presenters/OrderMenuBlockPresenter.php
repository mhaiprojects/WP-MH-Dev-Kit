<?php
/**
 * Gutenberg block for Order Menu shortcode (#18).
 *
 * @package MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters
 */

declare(strict_types=1);

namespace MH\DevKit\Modules\Commerce\Features\OrderMenu\Presenters;

final class OrderMenuBlockPresenter {

	public function register(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
	}

	public function register_block(): void {
		$path = MH_DEV_KIT_PATH . 'src/Modules/Commerce/Features/OrderMenu/Assets/blocks/order-menu';

		if ( ! is_dir( $path ) ) {
			return;
		}

		register_block_type(
			$path,
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * @param array<string, mixed> $attributes Block attributes.
	 */
	public function render_block( array $attributes ): string {
		$atts = array(
			'category'   => (string) ( $attributes['category'] ?? '' ),
			'menu'       => (string) ( $attributes['menu'] ?? '' ),
			'ids'        => (string) ( $attributes['ids'] ?? '' ),
			'slugs'      => (string) ( $attributes['slugs'] ?? '' ),
			'orderby'    => (string) ( $attributes['orderby'] ?? 'title' ),
			'order'      => (string) ( $attributes['order'] ?? 'ASC' ),
			'show_image' => ! empty( $attributes['showImage'] ) ? 'yes' : '',
		);

		return (string) do_shortcode( $this->build_shortcode( $atts ) );
	}

	/**
	 * @param array<string, string> $atts Attributes.
	 */
	private function build_shortcode( array $atts ): string {
		$parts = array();

		foreach ( $atts as $key => $value ) {
			if ( '' === $value && 'show_image' !== $key ) {
				continue;
			}

			if ( 'show_image' === $key && 'yes' !== $value ) {
				continue;
			}

			$parts[] = $key . '="' . esc_attr( $value ) . '"';
		}

		return '[mhdk_order_menu ' . implode( ' ', $parts ) . ']';
	}

	/**
	 * @param array<int, array<string, mixed>> $categories Block categories.
	 * @return array<int, array<string, mixed>>
	 */
	public function register_block_category( array $categories, \WP_Block_Editor_Context $context ): array {
		unset( $context );

		foreach ( $categories as $category ) {
			if ( isset( $category['slug'] ) && 'mhdk' === $category['slug'] ) {
				return $categories;
			}
		}

		array_unshift(
			$categories,
			array(
				'slug'  => 'mhdk',
				'title' => __( 'MHDK', 'mh-dev-kit' ),
				'icon'  => null,
			)
		);

		return $categories;
	}
}
