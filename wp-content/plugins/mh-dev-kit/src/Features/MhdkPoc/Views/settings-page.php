<?php
/**
 * Foundation settings page.
 *
 * @var string               $option_key Settings option key.
 * @var array<string, mixed> $settings   Current settings.
 * @var string               $page_slug  Settings page slug.
 * @var string               $version    Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mhdk-admin">
	<h1><?php esc_html_e( 'MHDK-POC Settings', 'mh-dev-kit' ); ?></h1>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mhdk_foundation' );
		do_settings_sections( $page_slug );
		submit_button();
		?>
	</form>
	<p class="description"><?php echo esc_html( sprintf( __( 'MH Dev Kit %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
