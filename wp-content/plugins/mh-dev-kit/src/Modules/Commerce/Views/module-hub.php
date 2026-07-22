<?php
/**
 * Commerce module hub.
 *
 * @var string $version Plugin version.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Commerce', 'mh-dev-kit' ); ?></h1>
	<hr class="wp-header-end" />
	<p><?php esc_html_e( 'WooCommerce features registered by the Commerce module.', 'mh-dev-kit' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th scope="col" class="column-primary"><?php esc_html_e( 'Feature', 'mh-dev-kit' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Manage', 'mh-dev-kit' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Order Menu', 'mh-dev-kit' ); ?></strong></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-order-menu' ) ); ?>"><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></a></td>
			</tr>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Store Locator', 'mh-dev-kit' ); ?></strong></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-settings-store-locator' ) ); ?>"><?php esc_html_e( 'Settings', 'mh-dev-kit' ); ?></a></td>
			</tr>
			<tr>
				<td class="column-primary"><strong><?php esc_html_e( 'Commerce Content', 'mh-dev-kit' ); ?></strong></td>
				<td><a href="<?php echo esc_url( admin_url( 'admin.php?page=mhdk-commerce-content' ) ); ?>"><?php esc_html_e( 'View content types', 'mh-dev-kit' ); ?></a></td>
			</tr>
		</tbody>
	</table>

	<p class="description"><?php echo esc_html( sprintf( __( 'Version %s', 'mh-dev-kit' ), $version ) ); ?></p>
</div>
