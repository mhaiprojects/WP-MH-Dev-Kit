<?php
/**
 * MH Dev Kit dashboard.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mhdk-admin">
	<h1><?php esc_html_e( 'MH Dev Kit', 'mh-dev-kit' ); ?></h1>
	<p><?php esc_html_e( 'Monolithic development plugin for custom WordPress features.', 'mh-dev-kit' ); ?></p>
	<ul>
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=mh-dev-kit-foundation' ) ); ?>"><?php esc_html_e( 'Foundation Settings', 'mh-dev-kit' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=mh-dev-kit-content' ) ); ?>"><?php esc_html_e( 'MH Dev Kit Content', 'mh-dev-kit' ); ?></a></li>
	</ul>
	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
