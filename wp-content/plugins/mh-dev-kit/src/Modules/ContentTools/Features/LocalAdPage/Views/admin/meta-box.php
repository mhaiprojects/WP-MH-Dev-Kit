<?php
/**
 * Local Ad Page admin meta box.
 *
 * @var \WP_Post             $post     Current post.
 * @var array<string,string> $location Location fields.
 * @var string               $headline Ad headline.
 * @var string               $body     Ad body HTML.
 * @var string               $url      Public URL preview.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><?php esc_html_e( 'Public URL', 'mh-dev-kit' ); ?></th>
		<td>
			<?php if ( '' !== $url ) : ?>
				<code><?php echo esc_html( $url ); ?></code>
			<?php else : ?>
				<span class="description"><?php esc_html_e( 'Fill location fields to generate URL.', 'mh-dev-kit' ); ?></span>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_local_country"><?php esc_html_e( 'Country', 'mh-dev-kit' ); ?></label></th>
		<td><input type="text" class="regular-text" id="mhdk_local_country" name="mhdk_local_country" value="<?php echo esc_attr( $location['country'] ); ?>" placeholder="au" /></td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_local_state"><?php esc_html_e( 'State', 'mh-dev-kit' ); ?></label></th>
		<td><input type="text" class="regular-text" id="mhdk_local_state" name="mhdk_local_state" value="<?php echo esc_attr( $location['state'] ); ?>" placeholder="vic" /></td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_local_suburb"><?php esc_html_e( 'Suburb', 'mh-dev-kit' ); ?></label></th>
		<td><input type="text" class="regular-text" id="mhdk_local_suburb" name="mhdk_local_suburb" value="<?php echo esc_attr( $location['suburb'] ); ?>" placeholder="shepparton" /></td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_local_postcode"><?php esc_html_e( 'Postcode', 'mh-dev-kit' ); ?></label></th>
		<td><input type="text" class="small-text" id="mhdk_local_postcode" name="mhdk_local_postcode" value="<?php echo esc_attr( $location['postcode'] ); ?>" placeholder="3630" /></td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_local_headline"><?php esc_html_e( 'Headline', 'mh-dev-kit' ); ?></label></th>
		<td><input type="text" class="large-text" id="mhdk_local_headline" name="mhdk_local_headline" value="<?php echo esc_attr( $headline ); ?>" /></td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_local_body"><?php esc_html_e( 'Ad body', 'mh-dev-kit' ); ?></label></th>
		<td><textarea class="large-text" rows="6" id="mhdk_local_body" name="mhdk_local_body"><?php echo esc_textarea( $body ); ?></textarea></td>
	</tr>
</table>
