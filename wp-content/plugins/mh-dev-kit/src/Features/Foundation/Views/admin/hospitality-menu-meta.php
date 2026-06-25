<?php
/**
 * Hospitality menu meta box fields.
 *
 * @var string $subtitle  Menu subtitle.
 * @var string $menu_note Menu footnote.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p>
	<label for="mhdk_subtitle"><strong><?php esc_html_e( 'Subtitle', 'mh-dev-kit' ); ?></strong></label><br />
	<input type="text" class="widefat" id="mhdk_subtitle" name="mhdk_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" />
</p>
<p>
	<label for="mhdk_menu_note"><strong><?php esc_html_e( 'Menu note', 'mh-dev-kit' ); ?></strong></label><br />
	<textarea class="widefat" id="mhdk_menu_note" name="mhdk_menu_note" rows="3"><?php echo esc_textarea( $menu_note ); ?></textarea>
</p>
