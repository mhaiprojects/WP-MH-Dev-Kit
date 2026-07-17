<?php
/**
 * Web link meta box fields.
 *
 * @var string $url         Link URL.
 * @var string $description Link description.
 * @var bool   $new_tab     Open in new tab.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="mhdk_url"><?php esc_html_e( 'URL', 'mh-dev-kit' ); ?></label></th>
		<td>
			<input type="url" class="regular-text widefat" id="mhdk_url" name="mhdk_url" value="<?php echo esc_attr( $url ); ?>" required />
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_description"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></label></th>
		<td>
			<textarea class="large-text widefat" id="mhdk_description" name="mhdk_description" rows="3"><?php echo esc_textarea( $description ); ?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'New tab', 'mh-dev-kit' ); ?></th>
		<td>
			<label>
				<input type="checkbox" name="mhdk_open_new_tab" value="1" <?php checked( $new_tab ); ?> />
				<?php esc_html_e( 'Open in new tab', 'mh-dev-kit' ); ?>
			</label>
		</td>
	</tr>
</table>
