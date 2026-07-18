<?php
/**
 * QR Code meta box.
 *
 * @var \WP_Post $post   Current post.
 * @var string   $target Target URL.
 * @var int      $size   QR size in pixels.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="mhdk_qr_target"><?php esc_html_e( 'Target URL or text', 'mh-dev-kit' ); ?></label></th>
		<td>
			<input type="url" class="large-text" id="mhdk_qr_target" name="mhdk_qr_target" value="<?php echo esc_attr( $target ); ?>" />
			<p class="description"><?php esc_html_e( 'URL or text encoded in the QR code.', 'mh-dev-kit' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_qr_size"><?php esc_html_e( 'Size (px)', 'mh-dev-kit' ); ?></label></th>
		<td>
			<input type="number" class="small-text" id="mhdk_qr_size" name="mhdk_qr_size" min="128" max="512" value="<?php echo esc_attr( (string) $size ); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Shortcode', 'mh-dev-kit' ); ?></th>
		<td>
			<code>[mhdk_qr_code id="<?php echo esc_attr( (string) $post->ID ); ?>"]</code>
		</td>
	</tr>
</table>
