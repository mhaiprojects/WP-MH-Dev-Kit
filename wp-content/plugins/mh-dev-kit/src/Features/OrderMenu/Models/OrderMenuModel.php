<?php
/**
 * Product queries and cart validation for Order Menu.
 *
 * @package MH\DevKit\Features\OrderMenu\Models
 */

declare(strict_types=1);

namespace MH\DevKit\Features\OrderMenu\Models;

final class OrderMenuModel {

	public const MAX_QTY_PER_LINE = 99;

	public const MAX_PRODUCTS     = 100;

	/**
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return array<int, \WC_Product>
	 */
	public function get_products( array $atts ): array {
		$args = array(
			'status'  => 'publish',
			'limit'   => self::MAX_PRODUCTS,
			'orderby' => 'title',
			'order'   => 'ASC',
			'return'  => 'objects',
		);

		if ( ! empty( $atts['ids'] ) ) {
			$ids = $this->parse_ids( $atts['ids'] );
			if ( array() === $ids ) {
				return array();
			}
			$args['include'] = $ids;
		} elseif ( ! empty( $atts['slugs'] ) ) {
			$slugs = $this->parse_slugs( $atts['slugs'] );
			if ( array() === $slugs ) {
				return array();
			}
			$args['slug'] = $slugs;
		} elseif ( ! empty( $atts['category'] ) ) {
			$args['category'] = array( sanitize_title( $atts['category'] ) );
		}

		$products = wc_get_products( $args );

		return array_values(
			array_filter(
				$products,
				static fn( $product ): bool => $product instanceof \WC_Product && $product->is_purchasable()
			)
		);
	}

	/**
	 * @param array<string, string> $atts     Filter attributes from form.
	 * @param array<int, int>       $quantities Product ID => quantity.
	 * @return array{added: int, skipped: int, messages: array<int, string>}
	 */
	public function add_to_cart( array $atts, array $quantities ): array {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array(
				'added'    => 0,
				'skipped'  => 0,
				'messages' => array( __( 'Cart is unavailable.', 'mh-dev-kit' ) ),
			);
		}

		$allowed = $this->get_products( $atts );
		$allowed_ids = array_flip(
			array_map(
				static fn( \WC_Product $product ): int => $product->get_id(),
				$allowed
			)
		);

		$added   = 0;
		$skipped = 0;
		$messages = array();

		foreach ( $quantities as $product_id => $qty ) {
			$product_id = (int) $product_id;
			$qty        = (int) $qty;

			if ( $qty <= 0 ) {
				continue;
			}

			if ( $qty > self::MAX_QTY_PER_LINE ) {
				$qty = self::MAX_QTY_PER_LINE;
			}

			if ( ! isset( $allowed_ids[ $product_id ] ) ) {
				++$skipped;
				continue;
			}

			$result = WC()->cart->add_to_cart( $product_id, $qty );

			if ( $result ) {
				++$added;
			} else {
				++$skipped;
				$messages[] = sprintf(
					/* translators: %d: product ID */
					__( 'Could not add product #%d to cart.', 'mh-dev-kit' ),
					$product_id
				);
			}
		}

		return array(
			'added'    => $added,
			'skipped'  => $skipped,
			'messages' => $messages,
		);
	}

	/**
	 * @return array<int, int>
	 */
	public function sanitize_quantities( array $raw ): array {
		$clean = array();

		foreach ( $raw as $key => $value ) {
			if ( ! is_numeric( $key ) ) {
				continue;
			}

			$product_id = (int) $key;
			$qty        = (int) $value;

			if ( $product_id <= 0 || $qty <= 0 ) {
				continue;
			}

			$clean[ $product_id ] = min( $qty, self::MAX_QTY_PER_LINE );
		}

		return $clean;
	}

	public function menu_description( \WC_Product $product ): string {
		$short = $product->get_short_description();

		if ( '' !== $short ) {
			return wp_strip_all_tags( $short );
		}

		return wp_trim_words( wp_strip_all_tags( $product->get_description() ), 24, '…' );
	}

	/**
	 * @param array<string, string> $atts Shortcode attributes.
	 * @return array<string, string>
	 */
	public function normalize_atts( array $atts ): array {
		return array(
			'category' => sanitize_title( (string) ( $atts['category'] ?? '' ) ),
			'ids'      => sanitize_text_field( (string) ( $atts['ids'] ?? '' ) ),
			'slugs'    => sanitize_text_field( (string) ( $atts['slugs'] ?? '' ) ),
		);
	}

	/**
	 * @return array<int, int>
	 */
	private function parse_ids( string $raw ): array {
		$parts = preg_split( '/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY );

		if ( ! is_array( $parts ) ) {
			return array();
		}

		$ids = array_map( 'absint', $parts );

		return array_values( array_filter( $ids, static fn( int $id ): bool => $id > 0 ) );
	}

	/**
	 * @return array<int, string>
	 */
	private function parse_slugs( string $raw ): array {
		$parts = preg_split( '/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY );

		if ( ! is_array( $parts ) ) {
			return array();
		}

		return array_values(
			array_filter(
				array_map( 'sanitize_title', $parts ),
				static fn( string $slug ): bool => '' !== $slug
			)
		);
	}
}
