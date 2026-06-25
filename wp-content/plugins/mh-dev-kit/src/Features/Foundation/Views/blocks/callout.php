<?php
/**
 * Callout block partial.
 *
 * @var string $title   Callout title.
 * @var string $content Callout body.
 * @var string $style   info|warning|success.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mhdk-block mhdk-callout mhdk-callout--<?php echo esc_attr( $style ); ?> wp-block-group">
	<?php if ( '' !== $title ) : ?>
		<h3 class="mhdk-callout__title"><?php echo esc_html( $title ); ?></h3>
	<?php endif; ?>
	<?php if ( '' !== $content ) : ?>
		<div class="mhdk-callout__content"><?php echo wp_kses_post( wpautop( $content ) ); ?></div>
	<?php endif; ?>
</div>
