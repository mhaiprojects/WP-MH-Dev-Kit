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
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="mhdk_subtitle"><?php esc_html_e( 'Subtitle', 'mh-dev-kit' ); ?></label></th>
		<td>
			<input type="text" class="regular-text widefat" id="mhdk_subtitle" name="mhdk_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_menu_note"><?php esc_html_e( 'Menu note', 'mh-dev-kit' ); ?></label></th>
		<td>
			<textarea class="large-text widefat" id="mhdk_menu_note" name="mhdk_menu_note" rows="3"><?php echo esc_textarea( $menu_note ); ?></textarea>
		</td>
	</tr>
</table>
