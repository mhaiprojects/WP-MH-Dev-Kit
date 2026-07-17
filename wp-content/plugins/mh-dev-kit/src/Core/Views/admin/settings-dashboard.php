<?php
/**
 * MHDK Settings dashboard.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mhdk-admin">
	<h1><?php esc_html_e( 'MHDK Settings', 'mh-dev-kit' ); ?></h1>
	<p><?php esc_html_e( 'Configure MH Dev Kit from the submenus below. Enable or disable modules under Modules.', 'mh-dev-kit' ); ?></p>
	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
