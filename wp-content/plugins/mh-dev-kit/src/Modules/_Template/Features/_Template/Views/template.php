<?php
/**
 * Template view partial.
 *
 * @var string $message Display message.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php echo esc_html( $message ); ?></p>
