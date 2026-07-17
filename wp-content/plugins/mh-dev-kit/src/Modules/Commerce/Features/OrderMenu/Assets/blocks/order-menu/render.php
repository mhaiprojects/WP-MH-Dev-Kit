<?php
/**
 * Order Menu block server render.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$atts = array(
	'category'   => (string) ( $attributes['category'] ?? '' ),
	'menu'       => (string) ( $attributes['menu'] ?? '' ),
	'ids'        => (string) ( $attributes['ids'] ?? '' ),
	'slugs'      => (string) ( $attributes['slugs'] ?? '' ),
	'orderby'    => (string) ( $attributes['orderby'] ?? 'title' ),
	'order'      => (string) ( $attributes['order'] ?? 'ASC' ),
	'show_image' => ! empty( $attributes['showImage'] ) ? 'yes' : '',
);

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

echo do_shortcode( '[mhdk_order_menu ' . implode( ' ', $parts ) . ']' );
