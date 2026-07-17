<?php
/**
 * Standard Settings API page shell (General Settings pattern).
 *
 * @var string      $page_slug     Settings page slug passed to do_settings_sections().
 * @var string      $option_group  Option group passed to settings_fields().
 * @var string|null $footer_note   Optional footer description.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php settings_errors(); ?>
	<form method="post" action="options.php">
		<?php
		settings_fields( $option_group );
		do_settings_sections( $page_slug );
		submit_button();
		?>
	</form>
	<?php if ( ! empty( $footer_note ) ) : ?>
		<p class="description"><?php echo esc_html( (string) $footer_note ); ?></p>
	<?php endif; ?>
</div>
