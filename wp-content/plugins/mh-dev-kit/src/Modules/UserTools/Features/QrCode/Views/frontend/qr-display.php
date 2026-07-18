<?php
/**
 * QR Code frontend display.
 *
 * @var string $target Encoded content.
 * @var int    $size   Pixel size.
 * @var string $title  Post title.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="mhdk-qr-code" data-target="<?php echo esc_attr( $target ); ?>" data-size="<?php echo esc_attr( (string) $size ); ?>">
	<?php if ( '' !== $title ) : ?>
		<p class="mhdk-qr-code-title"><strong><?php echo esc_html( $title ); ?></strong></p>
	<?php endif; ?>
	<div class="mhdk-qr-code-canvas" aria-label="<?php esc_attr_e( 'QR Code', 'mh-dev-kit' ); ?>"></div>
</div>
