<?php
/**
 * Core module content hub.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mhdk-admin">
	<h1><?php esc_html_e( 'MHDK Content', 'mh-dev-kit' ); ?></h1>
	<p><?php esc_html_e( 'Custom content types registered by the Core module.', 'mh-dev-kit' ); ?></p>
	<ul class="mhdk-content-links">
		<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mhdk_hosp_menu' ) ); ?>"><?php esc_html_e( 'Hospitality Menus', 'mh-dev-kit' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mhdk_web_link' ) ); ?>"><?php esc_html_e( 'Web Links', 'mh-dev-kit' ); ?></a></li>
	</ul>
	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
