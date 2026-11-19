<?php
/**
 * Inventory settings and stock management.
 *
 * @var \MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel $settings
 * @var array<int, array<string, mixed>>                                                       $stores
 * @var array<int, \WC_Product>                                                                $products
 * @var \MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventoryModel         $inventory
 * @var int                                                                                      $selected_product
 * @var string                                                                                   $locator_url
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MH\DevKit\Modules\Commerce\Features\InventoryManagement\Models\InventorySettingsModel;

$threshold = $settings->low_stock_threshold();
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<hr class="wp-header-end" />

	<?php settings_errors(); ?>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Inventory saved.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['matrix_saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Inventory matrix saved.', 'mh-dev-kit' ); ?></p></div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></h2>
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mhdk_inventory' );
		do_settings_sections( InventorySettingsModel::SETTINGS_SLUG );
		submit_button();
		?>
	</form>

	<h2><?php esc_html_e( 'Edit stock by store', 'mh-dev-kit' ); ?></h2>

	<?php if ( empty( $products ) ) : ?>
		<div class="notice notice-info inline">
			<p>
				<?php esc_html_e( 'No published WooCommerce products found. Create a product first, then return here to set per-store stock levels.', 'mh-dev-kit' ); ?>
			</p>
		</div>
	<?php else : ?>
		<form method="get" id="mhdk-inventory-product-form">
			<input type="hidden" name="page" value="<?php echo esc_attr( InventorySettingsModel::SETTINGS_SLUG ); ?>" />
			<label for="mhdk-inventory-product"><?php esc_html_e( 'Product', 'mh-dev-kit' ); ?></label>
			<select
				id="mhdk-inventory-product"
				name="product_id"
				class="wc-product-search"
				data-placeholder="<?php esc_attr_e( 'Search for a product…', 'mh-dev-kit' ); ?>"
				data-action="woocommerce_json_search_products"
				data-selected="<?php echo esc_attr( (string) $selected_product ); ?>"
			>
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
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Store', 'mh-dev-kit' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Stock', 'mh-dev-kit' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'mh-dev-kit' ); ?></th>
							<th scope="col"><?php esc_html_e( 'On locator', 'mh-dev-kit' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $stores as $store ) : ?>
							<?php
							$store_id = (string) $store['id'];
							$qty      = (int) ( $levels[ $store_id ] ?? 0 );
							if ( $qty <= 0 ) {
								$badge = __( 'Out of stock', 'mh-dev-kit' );
								$badge_class = 'notice notice-error inline';
							} elseif ( $qty <= $threshold ) {
								$badge = __( 'Low stock', 'mh-dev-kit' );
								$badge_class = 'notice notice-warning inline';
							} else {
								$badge = __( 'In stock', 'mh-dev-kit' );
								$badge_class = 'notice notice-success inline';
							}
							?>
							<tr>
								<td><strong><?php echo esc_html( (string) $store['name'] ); ?></strong></td>
								<td>
									<input
										type="number"
										min="0"
										class="small-text"
										name="store_stock[<?php echo esc_attr( $store_id ); ?>]"
										value="<?php echo esc_attr( (string) $qty ); ?>"
									/>
								</td>
								<td><span class="<?php echo esc_attr( $badge_class ); ?>"><p><?php echo esc_html( $badge ); ?></p></span></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-store-locator' ) ); ?>">
										<?php esc_html_e( 'Store Locator', 'mh-dev-kit' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button( __( 'Save inventory levels', 'mh-dev-kit' ) ); ?>
			</form>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Stock matrix', 'mh-dev-kit' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Edit stock for all listed products across stores in one save action.', 'mh-dev-kit' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'mhdk_save_inventory_matrix' ); ?>
			<input type="hidden" name="action" value="mhdk_save_inventory_matrix" />
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Product', 'mh-dev-kit' ); ?></th>
						<?php foreach ( $stores as $store ) : ?>
							<th scope="col"><?php echo esc_html( (string) $store['name'] ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $products as $product ) : ?>
						<?php $matrix_levels = $inventory->get_levels_for_product( (int) $product->get_id() ); ?>
						<tr>
							<td><strong><?php echo esc_html( $product->get_name() ); ?></strong></td>
							<?php foreach ( $stores as $store ) : ?>
								<?php
								$store_id = (string) $store['id'];
								$qty      = (int) ( $matrix_levels[ $store_id ] ?? 0 );
								?>
								<td>
									<input
										type="number"
										min="0"
										class="small-text"
										name="matrix[<?php echo esc_attr( (string) $product->get_id() ); ?>][<?php echo esc_attr( $store_id ); ?>]"
										value="<?php echo esc_attr( (string) $qty ); ?>"
									/>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( __( 'Save matrix', 'mh-dev-kit' ) ); ?>
		</form>
	<?php endif; ?>

	<hr />
	<p class="description">
		<a href="<?php echo esc_url( $locator_url ); ?>"><?php esc_html_e( 'Store Locator settings', 'mh-dev-kit' ); ?></a>
		—
		<a href="<?php echo esc_url( home_url( '/store-locator/' ) ); ?>"><?php esc_html_e( 'View demo locator page', 'mh-dev-kit' ); ?></a>
	</p>
</div>
