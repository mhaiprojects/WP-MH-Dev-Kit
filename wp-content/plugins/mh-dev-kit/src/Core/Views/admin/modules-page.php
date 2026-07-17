<?php
/**
 * MHDK Settings > Modules page.
 *
 * @var string                              $option_key Option name.
 * @var string                              $page_slug  Page slug.
 * @var \MH\DevKit\Core\Admin\ModulesListTable $table   Modules list table.
 * @var string                              $version    Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Modules', 'mh-dev-kit' ); ?></h1>
	<hr class="wp-header-end" />
	<?php settings_errors(); ?>
	<p><?php esc_html_e( 'Enable or disable MH Dev Kit modules. Disabled modules do not register features, admin pages, or blocks.', 'mh-dev-kit' ); ?></p>

	<form method="post" action="options.php">
		<?php settings_fields( 'mhdk_modules' ); ?>
		<?php $table->display(); ?>
		<?php submit_button(); ?>
	</form>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
