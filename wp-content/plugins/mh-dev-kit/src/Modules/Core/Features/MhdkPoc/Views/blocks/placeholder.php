<?php
/**
 * Block placeholder when content is missing.
 *
 * @var string $message Placeholder message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="mhdk-block mhdk-block-placeholder"><?php echo esc_html( $message ); ?></p>
