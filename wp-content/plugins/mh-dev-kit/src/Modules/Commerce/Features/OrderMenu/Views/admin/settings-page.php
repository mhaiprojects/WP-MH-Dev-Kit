<?php
/**
 * Order Menu settings and shortcode generator.
 *
 * @var string                             $option_key
 * @var array<string, mixed>               $settings
 * @var string                             $page_slug
 * @var array<int, array<string, string>>  $categories
 * @var array<int, array<string, mixed>>   $saved_menus
 * @var string                             $version
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap mhdk-admin mhdk-order-menu-settings">
	<h1><?php esc_html_e( 'Order Menu Settings', 'mh-dev-kit' ); ?></h1>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'mhdk_order_menu' );
		?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable AJAX add to cart', 'mh-dev-kit' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[enable_ajax]" value="1" <?php checked( ! empty( $settings['enable_ajax'] ) ); ?> />
						<?php esc_html_e( 'Update cart without full page reload', 'mh-dev-kit' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Default category', 'mh-dev-kit' ); ?></th>
				<td>
					<input type="text" class="regular-text" name="<?php echo esc_attr( $option_key ); ?>[default_category]" value="<?php echo esc_attr( (string) $settings['default_category'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Max quantity per line', 'mh-dev-kit' ); ?></th>
				<td>
					<input type="number" min="1" max="999" name="<?php echo esc_attr( $option_key ); ?>[max_qty_per_line]" value="<?php echo esc_attr( (string) $settings['max_qty_per_line'] ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Columns & behaviour', 'mh-dev-kit' ); ?></th>
				<td>
					<label><input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[show_unit_column]" value="1" <?php checked( ! empty( $settings['show_unit_column'] ) ); ?> /> <?php esc_html_e( 'Show unit column', 'mh-dev-kit' ); ?></label><br />
					<label><input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[show_stock_badge]" value="1" <?php checked( ! empty( $settings['show_stock_badge'] ) ); ?> /> <?php esc_html_e( 'Show stock badges', 'mh-dev-kit' ); ?></label><br />
					<label><input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[show_image]" value="1" <?php checked( ! empty( $settings['show_image'] ) ); ?> /> <?php esc_html_e( 'Show product thumbnails by default', 'mh-dev-kit' ); ?></label><br />
					<label><input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[preserve_qty_after_add]" value="1" <?php checked( ! empty( $settings['preserve_qty_after_add'] ) ); ?> /> <?php esc_html_e( 'Preserve quantity inputs after add', 'mh-dev-kit' ); ?></label><br />
					<label><input type="checkbox" name="<?php echo esc_attr( $option_key ); ?>[enable_reorder_button]" value="1" <?php checked( ! empty( $settings['enable_reorder_button'] ) ); ?> /> <?php esc_html_e( 'Show reorder last order button', 'mh-dev-kit' ); ?></label>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>

	<hr />

	<h2><?php esc_html_e( 'Shortcode generator', 'mh-dev-kit' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Build a shortcode snippet for any page or the Order Menu block.', 'mh-dev-kit' ); ?></p>

	<table class="form-table mhdk-shortcode-generator" role="presentation">
		<tr>
			<th scope="row"><label for="mhdk-gen-category"><?php esc_html_e( 'Category slug', 'mh-dev-kit' ); ?></label></th>
			<td>
				<select id="mhdk-gen-category">
					<option value=""><?php esc_html_e( '— None —', 'mh-dev-kit' ); ?></option>
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat['slug'] ); ?>"><?php echo esc_html( $cat['name'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mhdk-gen-menu"><?php esc_html_e( 'Saved menu', 'mh-dev-kit' ); ?></label></th>
			<td>
				<select id="mhdk-gen-menu">
					<option value=""><?php esc_html_e( '— None —', 'mh-dev-kit' ); ?></option>
					<?php foreach ( $saved_menus as $menu ) : ?>
						<option value="<?php echo esc_attr( (string) $menu['slug'] ); ?>"><?php echo esc_html( (string) $menu['title'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mhdk-gen-ids"><?php esc_html_e( 'Product IDs', 'mh-dev-kit' ); ?></label></th>
			<td><input type="text" class="regular-text" id="mhdk-gen-ids" placeholder="19,20,21" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="mhdk-gen-orderby"><?php esc_html_e( 'Order by', 'mh-dev-kit' ); ?></label></th>
			<td>
				<select id="mhdk-gen-orderby">
					<option value="title"><?php esc_html_e( 'Name', 'mh-dev-kit' ); ?></option>
					<option value="price"><?php esc_html_e( 'Price', 'mh-dev-kit' ); ?></option>
				</select>
				<select id="mhdk-gen-order">
					<option value="ASC">ASC</option>
					<option value="DESC">DESC</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Generated shortcode', 'mh-dev-kit' ); ?></th>
			<td>
				<input type="text" class="large-text code" id="mhdk-gen-output" readonly value='[mhdk_order_menu category="<?php echo esc_attr( (string) $settings['default_category'] ); ?>"]' />
			</td>
		</tr>
	</table>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
<script>
(function () {
	function buildShortcode() {
		var parts = [];
		var menu = document.getElementById('mhdk-gen-menu').value;
		var category = document.getElementById('mhdk-gen-category').value;
		var ids = document.getElementById('mhdk-gen-ids').value.trim();
		var orderby = document.getElementById('mhdk-gen-orderby').value;
		var order = document.getElementById('mhdk-gen-order').value;
		if (menu) parts.push('menu="' + menu + '"');
		else if (ids) parts.push('ids="' + ids + '"');
		else if (category) parts.push('category="' + category + '"');
		if (orderby && orderby !== 'title') parts.push('orderby="' + orderby + '"');
		if (order && order !== 'ASC') parts.push('order="' + order + '"');
		document.getElementById('mhdk-gen-output').value = '[mhdk_order_menu ' + parts.join(' ') + ']';
	}
	['mhdk-gen-category','mhdk-gen-menu','mhdk-gen-ids','mhdk-gen-orderby','mhdk-gen-order'].forEach(function (id) {
		document.getElementById(id).addEventListener('change', buildShortcode);
		document.getElementById(id).addEventListener('input', buildShortcode);
	});
	buildShortcode();
})();
</script>
