<?php
/**
 * Shortlinks admin meta box.
 *
 * @var \WP_Post $post  Current post.
 * @var string   $slug  Public slug.
 * @var string   $bio   Bio text.
 * @var array    $links Link rows.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MH\DevKit\Modules\UserTools\Features\Shortlinks\Models\ShortlinksModel;
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="mhdk_link_slug"><?php esc_html_e( 'Public slug', 'mh-dev-kit' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" id="mhdk_link_slug" name="mhdk_link_slug" value="<?php echo esc_attr( $slug ); ?>" />
			<p class="description">
				<?php
				printf(
					/* translators: %s: example URL */
					esc_html__( 'Public URL: %s', 'mh-dev-kit' ),
					esc_html( ShortlinksModel::public_url( $slug ?: 'your-slug' ) )
				);
				?>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mhdk_link_bio"><?php esc_html_e( 'Bio', 'mh-dev-kit' ); ?></label></th>
		<td><textarea class="large-text" rows="3" id="mhdk_link_bio" name="mhdk_link_bio"><?php echo esc_textarea( $bio ); ?></textarea></td>
	</tr>
</table>

<h4><?php esc_html_e( 'Links', 'mh-dev-kit' ); ?></h4>
<table class="widefat striped mhdk-link-items-table" id="mhdk-link-items-table">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Label', 'mh-dev-kit' ); ?></th>
			<th><?php esc_html_e( 'URL', 'mh-dev-kit' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $links as $index => $link ) : ?>
			<tr>
				<td><input type="text" class="widefat" name="mhdk_link_items[<?php echo esc_attr( (string) $index ); ?>][label]" value="<?php echo esc_attr( $link['label'] ?? '' ); ?>" /></td>
				<td><input type="url" class="widefat" name="mhdk_link_items[<?php echo esc_attr( (string) $index ); ?>][url]" value="<?php echo esc_attr( $link['url'] ?? '' ); ?>" /></td>
				<td><button type="button" class="button mhdk-remove-link-row"><?php esc_html_e( 'Remove', 'mh-dev-kit' ); ?></button></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p><button type="button" class="button" id="mhdk-add-link-row"><?php esc_html_e( 'Add link', 'mh-dev-kit' ); ?></button></p>
