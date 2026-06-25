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
<p>
	<label for="mhdk_url"><strong><?php esc_html_e( 'URL', 'mh-dev-kit' ); ?></strong></label><br />
	<input type="url" class="widefat" id="mhdk_url" name="mhdk_url" value="<?php echo esc_attr( $url ); ?>" required />
</p>
<p>
	<label for="mhdk_description"><strong><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></strong></label><br />
	<textarea class="widefat" id="mhdk_description" name="mhdk_description" rows="3"><?php echo esc_textarea( $description ); ?></textarea>
</p>
<p>
	<label>
		<input type="checkbox" name="mhdk_open_new_tab" value="1" <?php checked( $new_tab ); ?> />
		<?php esc_html_e( 'Open in new tab', 'mh-dev-kit' ); ?>
	</label>
</p>
