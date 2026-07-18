<?php
/**
 * Family Tree admin JSON editor.
 *
 * @var string $tree_json Pretty-printed tree JSON.
 * @var \WP_Post $post Current post.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row"><label for="mhdk_family_tree_json"><?php esc_html_e( 'Tree JSON', 'mh-dev-kit' ); ?></label></th>
		<td>
			<textarea class="large-text code" rows="16" id="mhdk_family_tree_json" name="mhdk_family_tree_json"><?php echo esc_textarea( (string) $tree_json ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Structure: { "root": { "id", "name", "children": [...] } }. Use the frontend shortcode with editable="1" for interactive editing.', 'mh-dev-kit' ); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Shortcode', 'mh-dev-kit' ); ?></th>
		<td>
			<code>[mhdk_family_tree id="<?php echo esc_attr( (string) $post->ID ); ?>"]</code><br />
			<code>[mhdk_family_tree id="<?php echo esc_attr( (string) $post->ID ); ?>" editable="1"]</code>
		</td>
	</tr>
</table>
