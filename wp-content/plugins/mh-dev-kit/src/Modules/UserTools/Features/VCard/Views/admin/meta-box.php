<?php
/**
 * vCard admin meta box fields.
 *
 * @var \WP_Post             $post   Current post.
 * @var array<string,string> $fields Field values.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$labels = array(
	'first_name' => __( 'First name', 'mh-dev-kit' ),
	'last_name'  => __( 'Last name', 'mh-dev-kit' ),
	'org'        => __( 'Organisation', 'mh-dev-kit' ),
	'title'      => __( 'Job title', 'mh-dev-kit' ),
	'phone'      => __( 'Phone', 'mh-dev-kit' ),
	'email'      => __( 'Email', 'mh-dev-kit' ),
	'url'        => __( 'Website', 'mh-dev-kit' ),
	'address'    => __( 'Address', 'mh-dev-kit' ),
);
?>
<table class="form-table" role="presentation">
	<?php foreach ( $labels as $key => $label ) : ?>
		<tr>
			<th scope="row"><label for="mhdk_vcard_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input
					type="text"
					class="regular-text"
					id="mhdk_vcard_<?php echo esc_attr( $key ); ?>"
					name="mhdk_vcard_<?php echo esc_attr( $key ); ?>"
					value="<?php echo esc_attr( $fields[ $key ] ?? '' ); ?>"
				/>
			</td>
		</tr>
	<?php endforeach; ?>
	<tr>
		<th scope="row"><?php esc_html_e( 'Download URL', 'mh-dev-kit' ); ?></th>
		<td><code><?php echo esc_html( \MH\DevKit\Modules\UserTools\Features\VCard\Presenters\VCardDownloadPresenter::download_url( (int) $post->ID ) ); ?></code></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Shortcode', 'mh-dev-kit' ); ?></th>
		<td><code>[mhdk_vcard id="<?php echo esc_attr( (string) $post->ID ); ?>"]</code></td>
	</tr>
</table>
