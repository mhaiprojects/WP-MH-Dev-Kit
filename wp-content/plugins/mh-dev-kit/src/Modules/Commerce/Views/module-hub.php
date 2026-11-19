<?php
/**
 * Commerce module hub.
 *
 * @var string $version Plugin version.
 * @var bool   $checkout_enabled
 * @var int    $active_stores
 * @var int    $inventory_products
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Commerce', 'mh-dev-kit' ); ?></h1>
	<hr class="wp-header-end" />
	<p><?php esc_html_e( 'WooCommerce features: order menus, store locator, inventory, and checkout pickup.', 'mh-dev-kit' ); ?></p>

	<h2><?php esc_html_e( 'Commerce operations', 'mh-dev-kit' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Area', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Manage', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><strong><?php esc_html_e( 'Store Locator', 'mh-dev-kit' ); ?></strong></td>
				<td><?php echo esc_html( sprintf( _n( '%d active store', '%d active stores', $active_stores, 'mh-dev-kit' ), $active_stores ) ); ?></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-store-locator' ) ); ?>"><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></a></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Inventory', 'mh-dev-kit' ); ?></strong></td>
				<td>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: number of products with stock data */
							_n( '%d product tracked', '%d products tracked', $inventory_products, 'mh-dev-kit' ),
							$inventory_products
						)
					);
					?>
				</td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-inventory' ) ); ?>"><?php esc_html_e( 'Manage', 'mh-dev-kit' ); ?></a></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Checkout pickup', 'mh-dev-kit' ); ?></strong></td>
				<td>
					<?php
					echo $checkout_enabled
						? esc_html__( 'Enabled', 'mh-dev-kit' )
						: esc_html__( 'Disabled', 'mh-dev-kit' );
					?>
				</td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-store-locator' ) ); ?>"><?php esc_html_e( 'Configure', 'mh-dev-kit' ); ?></a></td>
			</tr>
		</tbody>
	</table>

	<h2><?php esc_html_e( 'Features', 'mh-dev-kit' ); ?></h2>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-primary"><?php esc_html_e( 'Feature', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Description', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Manage', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Order Menu', 'mh-dev-kit' ); ?></strong></td>
				<td><?php esc_html_e( 'Bulk order menu shortcode and product ordering defaults.', 'mh-dev-kit' ); ?></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-order-menu' ) ); ?>"><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></a></td>
			</tr>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Store Locator', 'mh-dev-kit' ); ?></strong></td>
				<td><?php esc_html_e( 'AU store list, distance search, and checkout pickup selection.', 'mh-dev-kit' ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-store-locator' ) ); ?>"><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></a>
					|
					<a href="<?php echo esc_url( home_url( '/store-locator/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Demo', 'mh-dev-kit' ); ?></a>
				</td>
			</tr>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Inventory Management', 'mh-dev-kit' ); ?></strong></td>
				<td><?php esc_html_e( 'Per-store stock levels linked to the Store Locator and product pages.', 'mh-dev-kit' ); ?></td>
				<td>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-inventory' ) ); ?>"><?php esc_html_e( 'Manage', 'mh-dev-kit' ); ?></a>
					|
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-store-locator' ) ); ?>"><?php esc_html_e( 'Stores', 'mh-dev-kit' ); ?></a>
				</td>
			</tr>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Commerce Content', 'mh-dev-kit' ); ?></strong></td>
				<td><?php esc_html_e( 'Custom post types registered by the Commerce module.', 'mh-dev-kit' ); ?></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-commerce-content' ) ); ?>"><?php esc_html_e( 'View content types', 'mh-dev-kit' ); ?></a></td>
			</tr>
		</tbody>
	</table>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
