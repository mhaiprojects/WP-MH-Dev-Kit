<?php
/**
 * Inventory settings and quick-edit table.
 *
 * @var \MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel $settings
 * @var array<int, array<string, mixed>>                                                       $stores
 * @var array<int, \WC_Product>                                                                $products
 * @var \MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel         $inventory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel;

$selected_product = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0;

if ( $selected_product <= 0 && ! empty( $products ) ) {
	$selected_product = (int) $products[0]->get_id();
}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php settings_errors(); ?>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Inventory saved.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'mhdk_inventory' );
		do_settings_sections( InventorySettingsModel::SETTINGS_SLUG );
		submit_button();
		?>
	</form>

	<h2><?php esc_html_e( 'Edit stock by store', 'mh-dev-kit' ); ?></h2>

	<form method="get">
		<input type="hidden" name="page" value="<?php echo esc_attr( InventorySettingsModel::SETTINGS_SLUG ); ?>" />
		<label for="mhdk-inventory-product"><?php esc_html_e( 'Product', 'mh-dev-kit' ); ?></label>
		<select id="mhdk-inventory-product" name="product_id" onchange="this.form.submit()">
			<?php foreach ( $products as $product ) : ?>
				<option value="<?php echo esc_attr( (string) $product->get_id() ); ?>" <?php selected( $selected_product, $product->get_id() ); ?>>
					<?php echo esc_html( $product->get_name() ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</form>

	<?php if ( $selected_product > 0 ) : ?>
		<?php $levels = $inventory->get_levels_for_product( $selected_product ); ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'mhdk_save_inventory' ); ?>
			<input type="hidden" name="action" value="mhdk_save_inventory" />
			<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $selected_product ); ?>" />
			<table class="form-table" role="presentation">
				<tbody>
					<?php foreach ( $stores as $store ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( (string) $store['name'] ); ?></th>
							<td>
								<input
									type="number"
									min="0"
									class="small-text"
									name="store_stock[<?php echo esc_attr( (string) $store['id'] ); ?>]"
									value="<?php echo esc_attr( (string) ( $levels[ (string) $store['id'] ] ?? 0 ) ); ?>"
								/>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Save inventory levels', 'mh-dev-kit' ) ); ?>
		</form>
	<?php endif; ?>
</div>
